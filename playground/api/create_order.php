<?php
require_once __DIR__ . '/../includes/config.php';
startSession();
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    echo json_encode(['success' => false, 'error' => 'Invalid request body']);
    exit;
}

$paypalOrderId = $body['paypal_order_id'] ?? '';
$paypalPayerId = $body['paypal_payer_id'] ?? '';
$total         = floatval($body['total'] ?? 0);
$cart          = $body['cart'] ?? [];
$userId        = $_SESSION['user_id'];

if (!$paypalOrderId || empty($cart) || $total <= 0) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    $db = getDB();
    $db->beginTransaction();

    // Create order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, status, paypal_order_id, paypal_payer_id) VALUES (?, ?, 'paid', ?, ?)");
    $stmt->execute([$userId, $total, $paypalOrderId, $paypalPayerId]);
    $orderId = $db->lastInsertId();

    // Insert order items
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $productId = intval($item['id']);
        $qty       = intval($item['qty']);
        $price     = floatval($item['price']);

        $itemStmt->execute([$orderId, $productId, $qty, $price]);

        // Reduce stock
        $db->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?")->execute([$qty, $productId]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
