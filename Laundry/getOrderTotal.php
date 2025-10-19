<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/Database.php';

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$order_id = intval($data['order_id']);

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Fetch total_price from orders table
    $stmt = $conn->prepare("SELECT total_price FROM orders WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'total_price' => floatval($order['total_price'] ?? 0)
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>