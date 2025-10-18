<?php
ob_start();

header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

ob_clean();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/Order.php';
    require_once '../Models/Laundry.php';

    $rawInput = file_get_contents('php://input');

    if (empty($rawInput)) {
        throw new Exception('No data received');
    }

    $input = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    if (!isset($input['order_id']) || !isset($input['status'])) {
        throw new Exception('Missing required parameters');
    }

    $order_id = intval($input['order_id']);
    $new_status = trim($input['status']);

    if ($order_id <= 0) {
        throw new Exception('Invalid order ID');
    }

    $valid_statuses = ['On Hold', 'On Wash', 'On Dry', 'On Fold', 'For Delivery', 'Delivered', 'Paid'];
    
    if (!in_array($new_status, $valid_statuses)) {
        throw new Exception('Invalid status value');
    }

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    $sql = "UPDATE orders 
            SET status = :status, 
                updated_at = NOW() 
            WHERE order_id = :order_id 
            AND business_type = 'Laundry System'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update order status');
    }

    if ($stmt->rowCount() === 0) {
        throw new Exception('Order not found or already has this status');
    }

    ob_clean();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $new_status,
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    ob_clean();
    
    // Send error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush();
exit;
?>