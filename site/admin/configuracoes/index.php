<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * Configurações - Admin
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../database.php';

// Verificar se está logado e é admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . '/login');
}

// Definir título da página
$pageTitle = 'Configurações';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Token de segurança inválido'
        ];
        redirect(rtrim(SITE_URL, '/') . '/admin/configuracoes/');
    }
    
    // Obter dados do formulário
    $settings = [
        'site_email' => clean($_POST['site_email'] ?? ''),
        'site_phone' => clean($_POST['site_phone'] ?? ''),
        'site_address' => clean($_POST['site_address'] ?? ''),
        'currency' => clean($_POST['currency'] ?? 'EUR'),
        'currency_symbol' => clean($_POST['currency_symbol'] ?? '€'),
        'shipping_cost' => (string) (float) ($_POST['shipping_cost'] ?? 5.00),
        'free_shipping_threshold' => (string) (float) ($_POST['free_shipping_threshold'] ?? 50.00),
    ];

    // Atualizar configurações (insert se a chave ainda não existir na BD)
    try {
        foreach ($settings as $key => $value) {
            $exists = dbFetch('SELECT id FROM settings WHERE setting_key = ?', [$key]);
            if ($exists) {
                dbUpdate('settings', [
                    'setting_value' => $value,
                ], 'setting_key = ?', [$key]);
            } else {
                dbInsert('settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                ]);
            }
        }
        
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => 'Configurações atualizadas com sucesso!'
        ];
        
        redirect(rtrim(SITE_URL, '/') . '/admin/configuracoes/');
        
    } catch (Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Erro ao atualizar configurações: ' . $e->getMessage()
        ];
        
        redirect(rtrim(SITE_URL, '/') . '/admin/configuracoes/');
    }
}

// Obter configurações atuais
$settings = [];
$results = dbFetchAll("SELECT * FROM settings");
foreach ($results as $result) {
    $settings[$result['setting_key']] = $result['setting_value'];
}

// Incluir header do admin
include_once __DIR__ . '/../includes/admin_header.php';
?>

<!-- Settings Content -->
<div class="admin-content">
    <div class="admin-header">
        <div class="header-title">
            <h1>Configurações</h1>
            <p>Configure as definições do site. Chaves <strong>Stripe</strong> e webhook: apenas no ficheiro <code>site/.env</code> (<code>STRIPE_PUBLIC_KEY</code>, <code>STRIPE_SECRET_KEY</code>, <code>STRIPE_WEBHOOK_SECRET</code>).</p>
        </div>
    </div>
    
    <form action="<?php echo rtrim(SITE_URL, '/'); ?>/admin/configuracoes/" method="POST" class="settings-form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        
        <div class="form-grid">
            <!-- Site Settings -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Contacto na loja</h2>
                </div>
                <div class="card-content">
                    <p style="font-size:0.9rem;color:var(--a-text-muted);margin-bottom:1rem;">Estes dados aparecem no rodapé e no topo da loja. O nome da marca define-se no ficheiro <code>.env</code> (<code>SITE_NAME</code>).</p>
                    <div class="form-group">
                        <label for="site_email">Email de contacto</label>
                        <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_phone">Telefone</label>
                        <input type="text" id="site_phone" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_address">Morada</label>
                        <textarea id="site_address" name="site_address" rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                    </div>
                </div>
            </div>
            
            <!-- Currency Settings -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Configurações de Moeda</h2>
                </div>
                <div class="card-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="currency">Moeda</label>
                            <select id="currency" name="currency">
                                <option value="EUR" <?php echo ($settings['currency'] ?? 'EUR') === 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
                                <option value="USD" <?php echo ($settings['currency'] ?? '') === 'USD' ? 'selected' : ''; ?>>Dólar (USD)</option>
                                <option value="GBP" <?php echo ($settings['currency'] ?? '') === 'GBP' ? 'selected' : ''; ?>>Libra (GBP)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="currency_symbol">Símbolo da Moeda</label>
                            <input type="text" id="currency_symbol" name="currency_symbol" value="<?php echo $settings['currency_symbol'] ?? '€'; ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping Settings -->
            <div class="form-card">
                <div class="card-header">
                    <h2>Configurações de Envio</h2>
                </div>
                <div class="card-content">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="shipping_cost">Custo de Envio</label>
                            <input type="number" id="shipping_cost" name="shipping_cost" step="0.01" min="0" value="<?php echo $settings['shipping_cost'] ?? '5.00'; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="free_shipping_threshold">Envio Grátis a partir de</label>
                            <input type="number" id="free_shipping_threshold" name="free_shipping_threshold" step="0.01" min="0" value="<?php echo $settings['free_shipping_threshold'] ?? '50.00'; ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Guardar Configurações
            </button>
        </div>
    </form>
</div>

<?php
// Incluir footer do admin
include_once __DIR__ . '/../includes/admin_footer.php';
?>