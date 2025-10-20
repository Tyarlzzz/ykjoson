<?php
/**
 * Debug why orders aren't auto-archiving when set to Paid
 */

require_once '../database/Database.php';
require_once '../Models/Models.php';

echo "=== Debug: Auto-Archive on Paid Status ===\n";

try {
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    // Check the most recent orders and their paid status
    $sql = "SELECT o.order_id, o.status, o.paid_at, o.created_at, o.updated_at,
                   c.fullname,
                   CASE 
                       WHEN o.paid_at IS NOT NULL THEN 'YES' 
                       ELSE 'NO' 
                   END as has_paid_timestamp
            FROM orders o
            INNER JOIN customer c ON o.customer_id = c.customer_id
            WHERE o.business_type = 'Gas System'
            ORDER BY o.updated_at DESC 
            LIMIT 5";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Recent orders and their paid timestamps:\n";
    foreach ($recentOrders as $order) {
        echo "  Order {$order['order_id']}: {$order['fullname']}\n";
        echo "    Status: {$order['status']}\n";
        echo "    Has paid_at timestamp: {$order['has_paid_timestamp']}\n";
        echo "    paid_at: " . ($order['paid_at'] ?: 'NULL') . "\n";
        echo "    Last updated: {$order['updated_at']}\n\n";
    }
    
    // Check what orders would be archived right now
    echo "=== Testing Archive Function ===\n";
    
    // Simulate what the archive function sees
    $today = date('Y-m-d');
    $startTime = $today . ' 00:00:00';
    $endTime = $today . ' 23:59:59';
    
    $sql2 = "SELECT o.order_id, o.status, o.paid_at, c.fullname,
                    CASE WHEN lao.order_id IS NOT NULL THEN 'YES' ELSE 'NO' END as already_archived
             FROM orders o
             INNER JOIN customer c ON o.customer_id = c.customer_id
             LEFT JOIN gas_archived_orders lao ON o.order_id = lao.order_id
             WHERE o.business_type = 'Gas System' 
             AND o.status = 'Paid'
             AND o.paid_at BETWEEN :start_date AND :end_date";
    
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bindParam(':start_date', $startTime);
    $stmt2->bindParam(':end_date', $endTime);
    $stmt2->execute();
    
    $archivableOrders = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Orders that SHOULD be archived (Paid today):\n";
    if (empty($archivableOrders)) {
        echo "  None found\n";
        echo "  This means: No orders have status='Paid' AND paid_at timestamp set to today\n";
    } else {
        foreach ($archivableOrders as $order) {
            echo "  Order {$order['order_id']}: {$order['fullname']}\n";
            echo "    Status: {$order['status']}\n";
            echo "    Paid at: {$order['paid_at']}\n";
            echo "    Already archived: {$order['already_archived']}\n\n";
        }
    }
    
    // Check the updateStatus.php logic
    echo "=== Checking updateStatus.php Logic ===\n";
    echo "The issue might be:\n";
    echo "1. updateStatus.php isn't setting paid_at timestamp when status changes to 'Paid'\n";
    echo "2. The archive function isn't being called automatically\n";
    echo "3. There's an error in the archive process\n\n";
    
    echo "SOLUTION: The archive function needs to be called manually or via cron job.\n";
    echo "It's NOT automatically triggered when status changes to 'Paid'.\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
?>