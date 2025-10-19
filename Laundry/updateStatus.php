<?php
/**
 * 🎯 ARCHIVE TIMING CONFIGURATION:
 * 
 * To change the automatic archive delay, modify this variable on line ~89:
 * $archiveDelaySeconds = 60; // Change this number (in seconds)
 * 
 * Examples:
 * - 30 = 30 seconds
 * - 60 = 1 minute  
 * - 120 = 2 minutes
 * - 300 = 5 minutes
 * - 600 = 10 minutes
 */

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
    require_once '../Models/LaundryArchivedOrder.php';

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

    // Prepare SQL based on whether status is being changed to "Paid"
    if ($new_status === 'Paid') {
        $sql = "UPDATE orders 
                SET status = :status, 
                    updated_at = NOW(),
                    paid_at = NOW()
                WHERE order_id = :order_id 
                AND business_type = 'Laundry System'";
    } else {
        $sql = "UPDATE orders 
                SET status = :status, 
                    updated_at = NOW() 
                WHERE order_id = :order_id 
                AND business_type = 'Laundry System'";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':status', $new_status, PDO::PARAM_STR);
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update order status');
    }

    // Check if the order exists and get its current status
    $checkSql = "SELECT status FROM orders WHERE order_id = :order_id AND business_type = 'Laundry System'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $checkStmt->execute();
    $currentOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentOrder) {
        throw new Exception('Order not found');
    }

    // If no rows were affected, check if it's because the status is already the same
    if ($stmt->rowCount() === 0 && $currentOrder['status'] !== $new_status) {
        throw new Exception('Failed to update order status - unexpected error');
    }

    // If status is changed to 'Paid', schedule archive process for specified delay
    if ($new_status === 'Paid') {
        try {
            // Check if order is not already archived
            if (!LaundryArchivedOrder::isOrderArchived($order_id)) {
                // 🎯 CUSTOMIZE ARCHIVE DELAY HERE (in seconds):
                $archiveDelaySeconds = 60; // 60 = 1 minute, 120 = 2 minutes, 30 = 30 seconds
                
                // Set up a background process to archive this order after the specified delay
                $phpPath = '/Applications/XAMPP/xamppfiles/bin/php';
                $archiveScript = __DIR__ . '/schedule_archive.php';
                $command = "sleep $archiveDelaySeconds && $phpPath $archiveScript $order_id > /dev/null 2>&1 &";
                exec($command);
            }
        } catch (Exception $e) {
            // Log error but don't fail the status update
            error_log("Failed to schedule archive process for order ID: " . $order_id . " - " . $e->getMessage());
        }
    }

    ob_clean();

    // Determine the appropriate success message
    $wasUpdated = $stmt->rowCount() > 0;
    $message = $wasUpdated 
        ? 'Order status updated successfully' 
        : 'Order status confirmed (already set to ' . $new_status . ')';

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => $message,
        'new_status' => $new_status,
        'order_id' => $order_id,
        'was_updated' => $wasUpdated,
        'archived' => ($new_status === 'Delivered')
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