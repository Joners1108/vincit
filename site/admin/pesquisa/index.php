<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Pesquisa global no painel de admin
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

$pageTitle = 'Pesquisa';
$q = trim($_GET['q'] ?? '');
$minLen = 2;
$qLen = function_exists('mb_strlen') ? mb_strlen($q) : strlen($q);

$products = [];
$orders = [];
$users = [];
$categories = [];

if ($qLen >= $minLen) {
    $like = '%' . $q . '%';
    $products = dbFetchAll(
        'SELECT id, name, slug, status FROM products WHERE name LIKE ? OR slug LIKE ? ORDER BY name ASC LIMIT 25',
        [$like, $like]
    );
    $orders = dbFetchAll(
        'SELECT id, order_number, shipping_name, shipping_email, status FROM orders WHERE order_number LIKE ? OR shipping_name LIKE ? OR shipping_email LIKE ? ORDER BY created_at DESC LIMIT 25',
        [$like, $like, $like]
    );
    $users = dbFetchAll(
        'SELECT id, name, email, role, status FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY created_at DESC LIMIT 25',
        [$like, $like]
    );
    $categories = dbFetchAll(
        'SELECT id, name, slug, status FROM categories WHERE name LIKE ? OR slug LIKE ? ORDER BY name ASC LIMIT 25',
        [$like, $like]
    );
}

include_once __DIR__ . '/../includes/admin_header.php';
?>

<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Pesquisa no admin</h1>
            <p>Produtos, encomendas, utilizadores e categorias</p>
        </div>
    </div>

    <div class="filters-card" style="margin-bottom: 1.5rem;">
        <form action="<?php echo SITE_URL; ?>/admin/pesquisa" method="GET" class="filters-form">
            <div class="filter-group" style="flex: 1; min-width: 200px;">
                <label for="q">Termo</label>
                <input type="search" id="q" name="q" value="<?php echo htmlspecialchars($q); ?>" placeholder="Nome, email, número de encomenda…" autofocus>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-secondary"><i class="fas fa-search"></i> Pesquisar</button>
            </div>
        </form>
    </div>

    <?php if ($q !== '' && $qLen < $minLen): ?>
    <p class="no-results" style="padding:1rem;">Introduz pelo menos <?php echo $minLen; ?> caracteres.</p>
    <?php elseif ($q !== ''): ?>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header"><h2>Produtos (<?php echo count($products); ?>)</h2></div>
            <div class="card-content">
                <?php if (count($products) > 0): ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($products as $p): ?>
                    <li style="padding:0.5rem 0;border-bottom:1px solid var(--a-border);">
                        <a href="<?php echo SITE_URL; ?>/admin/produtos/editar/<?php echo (int) $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a>
                        <span style="font-size:0.85rem;color:var(--a-text-muted);"> · <?php echo htmlspecialchars($p['status']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?><p style="color:var(--a-text-muted);">Sem resultados.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header"><h2>Encomendas (<?php echo count($orders); ?>)</h2></div>
            <div class="card-content">
                <?php if (count($orders) > 0): ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($orders as $o): ?>
                    <li style="padding:0.5rem 0;border-bottom:1px solid var(--a-border);">
                        <a href="<?php echo SITE_URL; ?>/admin/encomendas/editar/<?php echo (int) $o['id']; ?>"><?php echo htmlspecialchars($o['order_number']); ?></a>
                        <span style="font-size:0.85rem;color:var(--a-text-muted);"> · <?php echo htmlspecialchars($o['shipping_name']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?><p style="color:var(--a-text-muted);">Sem resultados.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header"><h2>Utilizadores (<?php echo count($users); ?>)</h2></div>
            <div class="card-content">
                <?php if (count($users) > 0): ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($users as $u): ?>
                    <li style="padding:0.5rem 0;border-bottom:1px solid var(--a-border);">
                        <a href="<?php echo SITE_URL; ?>/admin/utilizadores/<?php echo (int) $u['id']; ?>"><?php echo htmlspecialchars($u['name']); ?></a>
                        <span style="font-size:0.85rem;color:var(--a-text-muted);"> · <?php echo htmlspecialchars($u['email']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?><p style="color:var(--a-text-muted);">Sem resultados.</p><?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header"><h2>Categorias (<?php echo count($categories); ?>)</h2></div>
            <div class="card-content">
                <?php if (count($categories) > 0): ?>
                <ul style="list-style:none;padding:0;margin:0;">
                    <?php foreach ($categories as $c): ?>
                    <li style="padding:0.5rem 0;border-bottom:1px solid var(--a-border);">
                        <a href="<?php echo SITE_URL; ?>/admin/categorias/editar/<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?><p style="color:var(--a-text-muted);">Sem resultados.</p><?php endif; ?>
            </div>
        </div>
    </div>

    <?php else: ?>
    <p style="color:var(--a-text-muted);padding:1rem 0;">Escreve um termo na caixa acima e pesquisa.</p>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../includes/admin_footer.php'; ?>
