<?php
/**
 * AUTO-ARCHIVE PROCESSOR
 * 
 * This script checks for orders that are ready to be archived based on their archive_at timestamp.
 * Call this script periodically (via AJAX from frontend or cron job) to process pending archives.
 */

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../Models/GasArchivedOrder.php';

ob_clean();
header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Make sure the model has the connection
    require_once __DIR__ . '/../Models/Models.php';
    Model::setConnection($pdo);
    
    // Find all orders that:
    // 1. Have an archive_at time set
    // 2. The archive_at time has passed (is in the past)
    // 3. Status is still "Paid"
    // 4. Not already archived
    $sql = "SELECT order_id FROM orders 
            WHERE archive_at IS NOT NULL 
            AND archive_at <= NOW() 
            AND status = 'Paid'
            AND order_id NOT IN (SELECT order_id FROM gas_archived_orders)
            ORDER BY archive_at ASC
            LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $ordersToArchive = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $archived = [];
    $errors = [];
    
    foreach ($ordersToArchive as $orderId) {
        try {
            error_log("Auto-archiving order ID: $orderId");
            
            // Step 1: Change status to "Delivered" if it's "Paid"
            $updateStatusStmt = $pdo->prepare("UPDATE orders SET status = 'Delivered' WHERE order_id = ? AND status = 'Paid'");
            $updateStatusStmt->execute([$orderId]);
            
            // Step 2: Archive the order
            $result = GasArchivedOrder::archiveOrder($orderId);
            
            if ($result) {
                // Clear the archive_at timestamp after successful archiving
                $clearStmt = $pdo->prepare("UPDATE orders SET archive_at = NULL WHERE order_id = ?");
                $clearStmt->execute([$orderId]);
                
                $archived[] = $orderId;
                error_log("Successfully auto-archived order ID: $orderId");
            } else {
                $errors[] = "Failed to archive order $orderId";
                error_log("Failed to auto-archive order ID: $orderId");
            }
        } catch (Exception $e) {
            $errors[] = "Error archiving order $orderId: " . $e->getMessage();
            error_log("Error auto-archiving order ID: $orderId - " . $e->getMessage());
        }
    }
    
    ob_clean();
    echo json_encode([
        'success' => true,
        'archived_count' => count($archived),
        'archived_orders' => $archived,
        'errors' => $errors
    ]);
    ob_end_flush();
    exit;
    
} catch (Exception $e) {
    error_log("Auto-archive processor error: " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}
?>
