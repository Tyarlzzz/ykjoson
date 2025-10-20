<?php
/**
 * Utility script to manually archive existing paid Gas orders
 * This archives orders that were paid 2 or more days ago and haven't been archived yet
 */

require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/GasArchivedOrder.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

try {
    // Get all paid Gas orders from 2+ days ago that haven't been archived yet
    $twoDaysAgo = date('Y-m-d H:i:s', strtotime('-2 days'));
    
    $sql = "SELECT o.order_id, o.paid_at 
            FROM orders o 
            WHERE o.business_type = 'Gas System' 
            AND o.status = 'Paid' 
            AND o.paid_at IS NOT NULL
            AND o.paid_at <= :two_days_ago
            AND o.order_id NOT IN (SELECT order_id FROM gas_archived_orders)
            ORDER BY o.paid_at ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':two_days_ago', $twoDaysAgo);
    $stmt->execute();
    
    $paidOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($paidOrders)) {
        echo "No paid orders found to archive (no orders paid 2+ days ago).\n";
        exit;
    }
    
    $archived_count = 0;
    $failed_count = 0;
    
    foreach ($paidOrders as $order) {
        $order_id = $order['order_id'];
        $paid_date = $order['paid_at'];
        
        try {
            if (GasArchivedOrder::archiveOrderWithCustomDeliveryDate($order_id)) {
                $archived_count++;
                echo "Successfully archived order ID: $order_id (paid on: $paid_date)\n";
            } else {
                $failed_count++;
                echo "Failed to archive order ID: $order_id\n";
            }
        } catch (Exception $e) {
            $failed_count++;
            echo "Error archiving order ID $order_id: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n=== Archive Summary ===\n";
    echo "Total orders processed: " . count($paidOrders) . "\n";
    echo "Successfully archived: $archived_count\n";
    echo "Failed to archive: $failed_count\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>