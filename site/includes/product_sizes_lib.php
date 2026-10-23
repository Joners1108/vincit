<?php
/**
 * Tamanhos e stock por tamanho (product_sizes)
 */

/**
 * Garante que a tabela product_sizes existe (se ainda não correu update_database.php).
 * Só memoriza sucesso (evita bloquear o worker PHP após uma falha pontual).
 *
 * @return bool true se a tabela existe ou foi criada
 */
function vincit_ensure_product_sizes_schema(): bool {
    static $verified = false;
    if ($verified) {
        return true;
    }

    global $pdo;
    if (!$pdo instanceof PDO) {
        return false;
    }

    try {
        $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $chk = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
        );
        $chk->execute([$db, 'product_sizes']);
        if ((int) $chk->fetchColumn() > 0) {
            $verified = true;

            return true;
        }

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
        error_log('Vincit: tabela product_sizes criada automaticamente. Recomenda-se correr update_database.php para o resto do esquema (carrinho, etc.).');
        $verified = true;

        return true;
    } catch (Throwable $e) {
        error_log('Vincit vincit_ensure_product_sizes_schema: ' . $e->getMessage());

        return false;
    }
}

function vincit_get_product_sizes(int $productId): array {
    if (!vincit_ensure_product_sizes_schema()) {
        return [];
    }

    return dbFetchAll(
        'SELECT id, product_id, size_label, stock, sort_order FROM product_sizes WHERE product_id = ? ORDER BY sort_order ASC, id ASC',
        [$productId]
    );
}

function vincit_product_size_by_id(int $id): ?array {
    if (!vincit_ensure_product_sizes_schema()) {
        return null;
    }

    $r = dbFetch('SELECT * FROM product_sizes WHERE id = ?', [$id]);
    return $r ?: null;
}

function vincit_sync_product_size_string_and_total(int $productId): void {
    if (!vincit_ensure_product_sizes_schema()) {
        return;
    }

    global $pdo;
    $rows = vincit_get_product_sizes($productId);
    $labels = [];
    foreach ($rows as $r) {
        $labels[] = $r['size_label'];
    }
    $total = 0;
    foreach ($rows as $r) {
        $total += (int) $r['stock'];
    }
    $sizeStr = implode(', ', $labels);
    if (strlen($sizeStr) > 500) {
        $sizeStr = substr($sizeStr, 0, 500);
    }
    $st = $pdo->prepare('UPDATE products SET size = ?, stock = ? WHERE id = ?');
    $st->execute([$sizeStr, $total, $productId]);
}

/**
 * Guarda linhas a partir de POST size_label[] e size_stock[]
 * @return string|null mensagem de erro ou null se OK
 */
function vincit_save_product_sizes_from_post(int $productId, array $post): ?string {
    if (!vincit_ensure_product_sizes_schema()) {
        return 'Não foi possível criar a tabela de tamanhos na base de dados. Verifica permissões MySQL ou corre o script update_database.php.';
    }

    global $pdo;

    $labels = $post['size_label'] ?? [];
    $stocks = $post['size_stock'] ?? [];
    if (!is_array($labels) || !is_array($stocks)) {
        return 'Dados de tamanhos inválidos.';
    }

    $pairs = [];
    $n = max(count($labels), count($stocks));
    for ($i = 0; $i < $n; $i++) {
        $label = isset($labels[$i]) ? trim((string) $labels[$i]) : '';
        $stock = isset($stocks[$i]) ? max(0, (int) $stocks[$i]) : 0;
        if ($label === '') {
            continue;
        }
        $pairs[] = ['label' => $label, 'stock' => $stock];
    }

    if (count($pairs) === 0) {
        return 'Indica pelo menos um tamanho com stock (ex.: S, M, L ou Único).';
    }

    // Evitar duplicados com espaços/capitalização que quebram o UNIQUE (product_id, size_label)
    $seen = [];
    foreach ($pairs as $p) {
        $key = function_exists('mb_strtolower')
            ? mb_strtolower($p['label'], 'UTF-8')
            : strtolower($p['label']);
        if (isset($seen[$key])) {
            return 'Tamanhos duplicados: "' . $p['label'] . '" aparece mais do que uma vez.';
        }
        $seen[$key] = true;
    }

    try {
        // Libertar referências em encomendas antes de apagar variantes (evita erro 1451 com FK)
        try {
            $pdo->prepare('UPDATE order_items SET product_size_id = NULL WHERE product_id = ?')->execute([$productId]);
        } catch (Throwable $e) {
            // Coluna pode não existir em bases muito antigas
        }

        $pdo->prepare('DELETE FROM product_sizes WHERE product_id = ?')->execute([$productId]);
        $sort = 0;
        foreach ($pairs as $p) {
            $ins = $pdo->prepare(
                'INSERT INTO product_sizes (product_id, size_label, stock, sort_order) VALUES (?, ?, ?, ?)'
            );
            $ins->execute([$productId, $p['label'], $p['stock'], $sort++]);
        }

        vincit_sync_product_size_string_and_total($productId);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || (int) $e->getCode() === 23000) {
            return 'Tamanhos duplicados no mesmo produto não são permitidos.';
        }
        if (strpos($e->getMessage(), '1451') !== false || strpos($e->getMessage(), 'foreign key') !== false) {
            error_log('product_sizes save FK: ' . $e->getMessage());
            return 'Não foi possível atualizar tamanhos (referências noutras tabelas). Contacta o suporte técnico.';
        }
        error_log('product_sizes save: ' . $e->getMessage());
        return 'Erro ao guardar tamanhos: ' . $e->getMessage();
    }

    return null;
}

function vincit_product_total_stock(int $productId): int {
    if (!vincit_ensure_product_sizes_schema()) {
        return 0;
    }

    $row = dbFetch('SELECT COALESCE(SUM(stock), 0) AS t FROM product_sizes WHERE product_id = ?', [$productId]);
    return $row ? (int) $row['t'] : 0;
}

/**
 * Debita stock por linha de carrinho (product_sizes + products.stock agregado)
 * @return string|null Erro ou null
 */
/**
 * Popular product_sizes a partir do texto de tamanhos (CSV) e stock total — seeds / migrações.
 */
function vincit_seed_product_sizes_from_product_row(int $productId, string $sizeCsv, int $totalStock): void {
    if (!vincit_ensure_product_sizes_schema()) {
        return;
    }

    global $pdo;

    $sizes = array_values(array_filter(array_map('trim', explode(',', $sizeCsv)), static function ($s) {
        return $s !== '';
    }));
    $stock = max(0, $totalStock);
    if (count($sizes) === 0) {
        $pdo->prepare(
            'INSERT INTO product_sizes (product_id, size_label, stock, sort_order) VALUES (?, ?, ?, 0)'
        )->execute([$productId, 'Único', $stock]);
    } else {
        $n = count($sizes);
        $base = intdiv($stock, $n);
        $rem = $stock % $n;
        $so = 0;
        foreach ($sizes as $idx => $sl) {
            $q = $base + ($idx < $rem ? 1 : 0);
            $pdo->prepare(
                'INSERT INTO product_sizes (product_id, size_label, stock, sort_order) VALUES (?, ?, ?, ?)'
            )->execute([$productId, $sl, $q, $so++]);
        }
    }
    vincit_sync_product_size_string_and_total($productId);
}

function vincit_apply_cart_stock_decrements(array $cartItems): ?string {
    if (!vincit_ensure_product_sizes_schema()) {
        return 'Esquema de tamanhos em falta na base de dados.';
    }

    global $pdo;

    $productIds = [];
    foreach ($cartItems as $item) {
        $psId = (int) ($item['product_size_id'] ?? 0);
        $qty = (int) ($item['quantity'] ?? 0);
        if ($psId < 1 || $qty < 1) {
            return 'Linha de carrinho inválida.';
        }
        $st = $pdo->prepare('UPDATE product_sizes SET stock = stock - ? WHERE id = ? AND stock >= ?');
        $st->execute([$qty, $psId, $qty]);
        if ($st->rowCount() === 0) {
            return 'Stock insuficiente para uma das linhas.';
        }
        $productIds[(int) $item['product_id']] = true;
    }
    foreach (array_keys($productIds) as $pid) {
        vincit_sync_product_size_string_and_total((int) $pid);
    }

    return null;
}

/**
 * Garante coluna cart.product_size_id e dados mínimos (equivalente a update_database.php).
 * Evita erro quando a BD antiga ainda não tem a migração do carrinho por tamanho.
 */
function vincit_ensure_cart_product_size_column(): void {
    static $done = false;
    if ($done) {
        return;
    }

    if (!vincit_ensure_product_sizes_schema()) {
        return;
    }

    global $pdo;
    try {
        $db = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $chkTable = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
        );
        $chkTable->execute([$db, 'cart']);
        if ((int) $chkTable->fetchColumn() === 0) {
            $done = true;

            return;
        }

        $chkCol = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $chkCol->execute([$db, 'cart', 'product_size_id']);
        if ((int) $chkCol->fetchColumn() > 0) {
            $done = true;

            return;
        }

        $pdo->exec('ALTER TABLE cart ADD COLUMN product_size_id INT NULL AFTER product_id');

        $pdo->exec("
            UPDATE cart c
            INNER JOIN (
                SELECT product_id, MIN(id) AS mid FROM product_sizes GROUP BY product_id
            ) x ON x.product_id = c.product_id
            SET c.product_size_id = x.mid
            WHERE c.product_size_id IS NULL
        ");
        $pdo->exec('DELETE FROM cart WHERE product_size_id IS NULL');

        try {
            $pdo->exec('ALTER TABLE cart MODIFY COLUMN product_size_id INT NOT NULL');
        } catch (Throwable $e) {
            // pode falhar se ainda houver NULLs ou já for NOT NULL
        }
        try {
            $pdo->exec('
                ALTER TABLE cart
                ADD CONSTRAINT fk_cart_product_size
                FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE CASCADE
            ');
        } catch (Throwable $e) {
            // FK duplicada ou incompatível
        }
    } catch (Throwable $e) {
        error_log('Vincit vincit_ensure_cart_product_size_column: ' . $e->getMessage());
    } finally {
        $done = true;
    }
}
