<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/Laundry.php';
require_once '../Models/LaundryArchivedOrder.php';

ob_clean();
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No data received');
    }

    $input = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON');
    }

    if (!isset($input['order_id']) || !isset($input['status'])) {
        throw new Exception('Missing required parameters');
    }

    $order_id = intval($input['order_id']);
    $new_status = trim($input['status']);

    // Connect to database
    $db = new Database();
    $pdo = $db->getConnection();
    Model::setConnection($pdo);

    // Update the status and handle Paid status
    if ($new_status === 'Paid') {
        // Set status, paid_at, and archive_at (60 seconds from now)
        $sql = "UPDATE orders SET 
                status = ?, 
                updated_at = NOW(), 
                paid_at = NOW(), 
                archive_at = DATE_ADD(NOW(), INTERVAL 60 SECOND) 
                WHERE order_id = ? AND business_type = 'Laundry System'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $order_id]);
        
        error_log("Order $order_id marked as Paid and scheduled for archiving in 60 seconds");
        
        // Check if there are any OTHER orders ready to archive (not this one we just updated)
        $archiveResult = LaundryArchivedOrder::archiveOrdersPaidTwoDaysAgo();
        error_log("Archive check result: " . json_encode($archiveResult));
        
    } else {
        // Regular status update
        $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ? AND business_type = 'Laundry System'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$new_status, $order_id]);
    }    ob_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully',
        'new_status' => $new_status,
        'order_id' => $order_id
    ]);
    ob_end_flush();
    exit;

} catch (Exception $e) {
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}
?>