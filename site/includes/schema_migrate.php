<?php
declare(strict_types=1);

/**
 * Migrações idempotentes do esquema MySQL.
 * Em alterações aqui, aumenta VINCIT_SCHEMA_REVISION em config.php.
 */

function vincit_schema_table_exists(PDO $pdo, string $table): bool {
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!$db) {
        return false;
    }
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
    );
    $st->execute([$db, $table]);
    return (int) $st->fetchColumn() > 0;
}

function vincit_schema_column_exists(PDO $pdo, string $table, string $column): bool {
    $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
    if (!$db) {
        return false;
    }
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $st->execute([$db, $table, $column]);
    return (int) $st->fetchColumn() > 0;
}

function vincit_schema_ensure_settings_table(PDO $pdo): void {
    if (vincit_schema_table_exists($pdo, 'settings')) {
        return;
    }
    $pdo->exec("
        CREATE TABLE settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_key (setting_key)
        ) ENGINE=InnoDB
    ");
}

function vincit_schema_revision_current(PDO $pdo): int {
    try {
        if (!vincit_schema_table_exists($pdo, 'settings')) {
            return 0;
        }
        $st = $pdo->prepare('SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1');
        $st->execute(['vincit_schema_revision']);
        $v = $st->fetchColumn();
        return $v !== false ? (int) $v : 0;
    } catch (Throwable $e) {
        return 0;
    }
}

function vincit_schema_revision_persist(PDO $pdo): void {
    if (!vincit_schema_table_exists($pdo, 'settings')) {
        return;
    }
    $rev = defined('VINCIT_SCHEMA_REVISION') ? (int) VINCIT_SCHEMA_REVISION : 1;
    $st = $pdo->prepare(
        'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $st->execute(['vincit_schema_revision', (string) $rev]);
}

/**
 * Correr migrações em falta (uma consulta rápida a settings quando já está atualizado).
 */
function vincit_maybe_auto_migrate(PDO $pdo): void {
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    try {
        if (!vincit_schema_table_exists($pdo, 'products')) {
            return;
        }
        $need = defined('VINCIT_SCHEMA_REVISION') ? (int) VINCIT_SCHEMA_REVISION : 1;
        if (vincit_schema_revision_current($pdo) >= $need) {
            return;
        }
        vincit_schema_ensure_settings_table($pdo);
        vincit_apply_schema_migrations($pdo, null);
        vincit_schema_revision_persist($pdo);
    } catch (Throwable $e) {
        error_log('vincit_maybe_auto_migrate: ' . $e->getMessage());
    }
}

/**
 * @param callable(string): void|null $log null = silencioso (pedidos HTTP)
 */
function vincit_apply_schema_migrations(PDO $pdo, ?callable $log): void {
    $m = static function (string $line) use ($log): void {
        if ($log !== null) {
            $log($line);
        }
    };

    $run = static function (PDO $pdo, string $sql, string $okMsg) use ($m): void {
        $pdo->exec($sql);
        $m("  ✓ {$okMsg}");
    };

    if (!vincit_schema_table_exists($pdo, 'products')) {
        throw new RuntimeException(
            'A tabela "products" não existe. Instala primeiro o site (install.php) ou importa database.sql.'
        );
    }

    vincit_schema_ensure_settings_table($pdo);

    // --- Remover SKU (já não usado na loja) ---
    if (vincit_schema_column_exists($pdo, 'products', 'sku')) {
        $run($pdo, 'ALTER TABLE products DROP COLUMN sku', 'Removida coluna products.sku');
    } else {
        $m('  · products.sku — já ausente (OK)');
    }

    // --- users.remember_token ---
    if (!vincit_schema_column_exists($pdo, 'users', 'remember_token')) {
        $after = vincit_schema_column_exists($pdo, 'users', 'password') ? 'AFTER password' : '';
        $run(
            $pdo,
            'ALTER TABLE users ADD COLUMN remember_token VARCHAR(128) NULL ' . $after,
            'Adicionada users.remember_token'
        );
    } else {
        $m('  · users.remember_token — já existe (OK)');
    }

    // --- products.size ---
    if (vincit_schema_column_exists($pdo, 'products', 'size')) {
        try {
            $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
            $colStmt = $pdo->prepare(
                'SELECT CHARACTER_MAXIMUM_LENGTH AS len FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
            );
            $colStmt->execute([$db, 'products', 'size']);
            $colInfo = $colStmt->fetch(PDO::FETCH_ASSOC);
            $len = isset($colInfo['len']) ? (int) $colInfo['len'] : 0;
            if ($len > 0 && $len < 500) {
                $run(
                    $pdo,
                    'ALTER TABLE products MODIFY COLUMN size VARCHAR(500) NULL',
                    'Alargada products.size para VARCHAR(500) (lista de tamanhos)'
                );
            } else {
                $m('  · products.size — já VARCHAR(500) ou superior (OK)');
            }
        } catch (Throwable $e) {
            $m('  · products.size alter: ' . $e->getMessage());
        }
    }

    // --- products.image / gallery ---
    if (!vincit_schema_column_exists($pdo, 'products', 'image')) {
        $after = vincit_schema_column_exists($pdo, 'products', 'sale_price') ? 'AFTER sale_price' : '';
        $run(
            $pdo,
            'ALTER TABLE products ADD COLUMN image VARCHAR(255) NULL ' . $after,
            'Adicionada products.image'
        );
    } else {
        $m('  · products.image — já existe (OK)');
    }

    if (!vincit_schema_column_exists($pdo, 'products', 'gallery')) {
        $after = vincit_schema_column_exists($pdo, 'products', 'image') ? 'AFTER image' : '';
        $run(
            $pdo,
            'ALTER TABLE products ADD COLUMN gallery TEXT NULL ' . $after,
            'Adicionada products.gallery'
        );
    } else {
        $m('  · products.gallery — já existe (OK)');
    }

    // --- orders ---
    if (vincit_schema_table_exists($pdo, 'orders')) {
        if (!vincit_schema_column_exists($pdo, 'orders', 'cart_session_id')) {
            $after = vincit_schema_column_exists($pdo, 'orders', 'user_id') ? 'AFTER user_id' : '';
            $run(
                $pdo,
                'ALTER TABLE orders ADD COLUMN cart_session_id VARCHAR(128) NULL ' . $after,
                'Adicionada orders.cart_session_id'
            );
        } else {
            $m('  · orders.cart_session_id — já existe (OK)');
        }

        if (!vincit_schema_column_exists($pdo, 'orders', 'stripe_checkout_session_id')) {
            $after = vincit_schema_column_exists($pdo, 'orders', 'stripe_payment_id') ? 'AFTER stripe_payment_id' : '';
            $run(
                $pdo,
                'ALTER TABLE orders ADD COLUMN stripe_checkout_session_id VARCHAR(255) NULL ' . $after,
                'Adicionada orders.stripe_checkout_session_id'
            );
        } else {
            $m('  · orders.stripe_checkout_session_id — já existe (OK)');
        }

        if (!vincit_schema_column_exists($pdo, 'orders', 'tracking_code')) {
            $after = vincit_schema_column_exists($pdo, 'orders', 'notes') ? 'AFTER notes' : '';
            $run(
                $pdo,
                'ALTER TABLE orders ADD COLUMN tracking_code VARCHAR(160) NULL ' . $after,
                'Adicionada orders.tracking_code'
            );
        } else {
            $m('  · orders.tracking_code — já existe (OK)');
        }
    }

    // --- admin_notifications ---
    if (!vincit_schema_table_exists($pdo, 'admin_notifications')) {
        $pdo->exec("
            CREATE TABLE admin_notifications (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(200) NOT NULL,
                body TEXT,
                link VARCHAR(500) NULL,
                is_read TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_admin_notifications_read (is_read),
                INDEX idx_admin_notifications_created (created_at)
            ) ENGINE=InnoDB
        ");
        $m('  ✓ Criada tabela admin_notifications');
    } else {
        $m('  · tabela admin_notifications — já existe (OK)');
    }

    // --- cart ---
    if (!vincit_schema_table_exists($pdo, 'cart')) {
        $pdo->exec("
            CREATE TABLE cart (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT,
                session_id VARCHAR(100),
                product_id INT NOT NULL,
                quantity INT NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                INDEX idx_user (user_id),
                INDEX idx_session (session_id),
                INDEX idx_product (product_id)
            ) ENGINE=InnoDB
        ");
        $m('  ✓ Criada tabela cart');
    } else {
        $m('  · tabela cart — já existe (OK)');
    }

    // --- password_resets ---
    if (!vincit_schema_table_exists($pdo, 'password_resets')) {
        $pdo->exec("
            CREATE TABLE password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_password_resets_token (token),
                INDEX idx_password_resets_user (user_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
        $m('  ✓ Criada tabela password_resets');
    } else {
        $m('  · tabela password_resets — já existe (OK)');
    }

    // --- newsletter removida ---
    if (vincit_schema_table_exists($pdo, 'newsletter_subscribers')) {
        $pdo->exec('DROP TABLE newsletter_subscribers');
        $m('  ✓ Removida tabela newsletter_subscribers');
    }

    // --- favorites ---
    if (!vincit_schema_table_exists($pdo, 'favorites')) {
        $pdo->exec("
            CREATE TABLE favorites (
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (user_id, product_id),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                INDEX idx_favorites_product (product_id)
            ) ENGINE=InnoDB
        ");
        $m('  ✓ Criada tabela favorites');
    } else {
        $m('  · tabela favorites — já existe (OK)');
    }

    // --- product_sizes ---
    if (!vincit_schema_table_exists($pdo, 'product_sizes')) {
        $pdo->exec("
            CREATE TABLE product_sizes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                size_label VARCHAR(64) NOT NULL,
                stock INT NOT NULL DEFAULT 0,
                sort_order INT NOT NULL DEFAULT 0,
                UNIQUE KEY uk_product_size (product_id, size_label),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                INDEX idx_product_sizes_product (product_id)
            ) ENGINE=InnoDB
        ");
        $m('  ✓ Criada tabela product_sizes');
    } else {
        $m('  · tabela product_sizes — já existe (OK)');
    }

    // --- order_items ---
    if (vincit_schema_table_exists($pdo, 'order_items')) {
        if (!vincit_schema_column_exists($pdo, 'order_items', 'size_label')) {
            $run(
                $pdo,
                'ALTER TABLE order_items ADD COLUMN size_label VARCHAR(64) NULL AFTER product_name',
                'Adicionada order_items.size_label'
            );
        } else {
            $m('  · order_items.size_label — já existe (OK)');
        }
        if (!vincit_schema_column_exists($pdo, 'order_items', 'product_size_id')) {
            $run(
                $pdo,
                'ALTER TABLE order_items ADD COLUMN product_size_id INT NULL AFTER product_id',
                'Adicionada order_items.product_size_id'
            );
        } else {
            $m('  · order_items.product_size_id — já existe (OK)');
        }
    }

    // --- cart.product_size_id ---
    if (vincit_schema_table_exists($pdo, 'cart') && vincit_schema_table_exists($pdo, 'product_sizes')) {
        if (!vincit_schema_column_exists($pdo, 'cart', 'product_size_id')) {
            $run(
                $pdo,
                'ALTER TABLE cart ADD COLUMN product_size_id INT NULL AFTER product_id',
                'Adicionada cart.product_size_id'
            );
        } else {
            $m('  · cart.product_size_id — já existe (OK)');
        }
    }

    // Migrar produtos antigos para product_sizes
    $psRows = (int) $pdo->query('SELECT COUNT(*) FROM product_sizes')->fetchColumn();
    if ($psRows === 0 && vincit_schema_table_exists($pdo, 'products')) {
        $products = $pdo->query('SELECT id, stock, size FROM products')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($products as $pr) {
            $pid = (int) $pr['id'];
            $stock = max(0, (int) $pr['stock']);
            $raw = trim((string) ($pr['size'] ?? ''));
            $sizes = array_values(array_filter(array_map('trim', explode(',', $raw)), static function ($s) {
                return $s !== '';
            }));
            if (count($sizes) === 0) {
                $pdo->prepare(
                    'INSERT INTO product_sizes (product_id, size_label, stock, sort_order) VALUES (?, ?, ?, 0)'
                )->execute([$pid, 'Único', $stock]);
            } else {
                $n = count($sizes);
                $base = $n > 0 ? intdiv($stock, $n) : 0;
                $rem = $n > 0 ? ($stock % $n) : 0;
                $so = 0;
                foreach ($sizes as $idx => $sl) {
                    $q = $base + ($idx < $rem ? 1 : 0);
                    $pdo->prepare(
                        'INSERT INTO product_sizes (product_id, size_label, stock, sort_order) VALUES (?, ?, ?, ?)'
                    )->execute([$pid, $sl, $q, $so++]);
                }
            }
        }
        $m('  ✓ Migrados tamanhos/stock a partir de products.size + stock');
    }

    // Carrinhos antigos + FK
    if (vincit_schema_column_exists($pdo, 'cart', 'product_size_id')) {
        $pdo->exec("
            UPDATE cart c
            INNER JOIN (
                SELECT product_id, MIN(id) AS mid FROM product_sizes GROUP BY product_id
            ) x ON x.product_id = c.product_id
            SET c.product_size_id = x.mid
            WHERE c.product_size_id IS NULL
        ");
        $orph = $pdo->exec('DELETE FROM cart WHERE product_size_id IS NULL');
        if ($orph > 0) {
            $m('  ✓ Removidas ' . (int) $orph . ' linhas de carrinho sem tamanho válido');
        }
        try {
            $pdo->exec('ALTER TABLE cart MODIFY COLUMN product_size_id INT NOT NULL');
            $m('  ✓ cart.product_size_id definido como NOT NULL');
        } catch (Throwable $e) {
            $m('  · cart.product_size_id NOT NULL: ' . $e->getMessage());
        }
        try {
            $pdo->exec('
                ALTER TABLE cart
                ADD CONSTRAINT fk_cart_product_size
                FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE CASCADE
            ');
            $m('  ✓ Adicionada FK cart → product_sizes');
        } catch (Throwable $e) {
            if (strpos($e->getMessage(), 'Duplicate') === false && strpos($e->getMessage(), 'duplicate') === false) {
                $m('  · FK cart/product_sizes: ' . $e->getMessage());
            } else {
                $m('  · FK cart → product_sizes — já existe (OK)');
            }
        }
    }

    $m('');
    $m('Concluído sem erros. A loja pode usar o esquema atual.');
    $m('');
}
