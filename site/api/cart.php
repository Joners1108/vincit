
<?php
/**
 * VINCIT E-COMMERCE DE ROUPA
 * API do Carrinho
 */

// Incluir ficheiros de configuração e base de dados
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../includes/product_sizes_lib.php';

// Definir cabeçalhos JSON
header('Content-Type: application/json');

// Verificar método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Permitir GET apenas para a ação 'get'
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    if ($action === 'get') {
        // Obter carrinho
        $cartItems = getCart();
        $cartCount = getCartCount();
        $cartTotal = getCartTotal();
        
        echo json_encode([
            'success' => true,
            'cart' => $cartItems,
            'cart_count' => $cartCount,
            'cart_total' => $cartTotal
        ]);
        exit;
    }
}

if ($method !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido'
    ]);
    exit;
}

// Obter dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Dados inválidos'
    ]);
    exit;
}

// Obter ação
$action = $input['action'] ?? '';

// Processar ação
switch ($action) {
    case 'add':
        // Adicionar produto ao carrinho (tamanho explícito ou 1.º em stock — p.ex. listagens)
        $productId = (int) ($input['product_id'] ?? 0);
        $productSizeId = (int) ($input['product_size_id'] ?? 0);
        $quantity = max(1, (int) ($input['quantity'] ?? 1));

        if ($productId < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Produto inválido.',
            ]);
            exit;
        }

        $product = getProductById($productId);
        if (!$product) {
            echo json_encode([
                'success' => false,
                'message' => 'Produto não encontrado',
            ]);
            exit;
        }

        if ($productSizeId < 1) {
            $sizes = vincit_get_product_sizes($productId);
            if (count($sizes) === 1) {
                $productSizeId = (int) $sizes[0]['id'];
            } else {
                $row = dbFetch(
                    'SELECT id FROM product_sizes WHERE product_id = ? AND stock > 0 ORDER BY sort_order ASC, id ASC LIMIT 1',
                    [$productId]
                );
                if ($row) {
                    $productSizeId = (int) $row['id'];
                }
            }
        }

        if ($productSizeId < 1) {
            echo json_encode([
                'success' => false,
                'message' => 'Sem stock ou escolhe um tamanho na página do produto.',
            ]);
            exit;
        }

        $result = addToCart($productId, $quantity, $productSizeId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Produto adicionado ao carrinho',
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal(),
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Stock insuficiente para este tamanho ou dados inválidos.',
            ]);
        }
        break;
        
    case 'update':
        // Atualizar quantidade no carrinho
        $cartId = $input['cart_id'] ?? null;
        $quantity = $input['quantity'] ?? 1;
        
        if (!$cartId) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do carrinho é obrigatório'
            ]);
            exit;
        }
        
        // Atualizar quantidade
        $result = updateCartQuantity($cartId, $quantity);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Quantidade atualizada',
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao atualizar quantidade'
            ]);
        }
        break;
        
    case 'remove':
        // Remover produto do carrinho
        $cartId = $input['cart_id'] ?? null;
        
        if (!$cartId) {
            echo json_encode([
                'success' => false,
                'message' => 'ID do carrinho é obrigatório'
            ]);
            exit;
        }
        
        // Remover do carrinho
        $result = removeFromCart($cartId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Produto removido do carrinho',
                'cart_count' => getCartCount(),
                'cart_total' => getCartTotal()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao remover produto do carrinho'
            ]);
        }
        break;
        
    case 'clear':
        // Limpar carrinho
        $result = clearCart();
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Carrinho limpo',
                'cart_count' => 0,
                'cart_total' => 0
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar carrinho'
            ]);
        }
        break;
        
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Ação inválida'
        ]);
        break;
}
?>