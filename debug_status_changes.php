<?php
/**
 * Debug script to track status changes in real-time
 * This will help identify what's causing automatic status changes
 */

require_once '../database/Database.php';

$database = new Database();
$conn = $database->getConnection();

// Function to get current orders with 'Delivered' status
function getDeliveredOrders($conn) {
    $sql = "SELECT order_id, status, updated_at, paid_at 
            FROM orders 
            WHERE business_type = 'Laundry System' 
            AND status = 'Delivered'
            ORDER BY order_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to log status changes
function logStatusChange($orderId, $oldStatus, $newStatus, $source) {
    $logFile = __DIR__ . '/status_change_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] Order $orderId: $oldStatus -> $newStatus (Source: $source)\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    echo $logEntry;
}

echo "=== STATUS CHANGE MONITOR ===\n";
echo "Monitoring orders with 'Delivered' status...\n";
echo "Press Ctrl+C to stop monitoring\n\n";

// Store initial state
$initialOrders = getDeliveredOrders($conn);
$lastKnownStatus = [];

foreach ($initialOrders as $order) {
    $lastKnownStatus[$order['order_id']] = $order['status'];
    echo "Initial: Order {$order['order_id']} = {$order['status']}\n";
}

echo "\nStarting monitoring...\n\n";

// Monitor for changes every 5 seconds
while (true) {
    sleep(5);
    
    // Check all orders that were previously 'Delivered'
    foreach ($lastKnownStatus as $orderId => $lastStatus) {
        $sql = "SELECT status FROM orders WHERE order_id = ? AND business_type = 'Laundry System'";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$orderId]);
        $currentOrder = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($currentOrder && $currentOrder['status'] !== $lastStatus) {
            logStatusChange($orderId, $lastStatus, $currentOrder['status'], 'Unknown - Automatic');
            $lastKnownStatus[$orderId] = $currentOrder['status'];
        }
    }
    
    // Check for new 'Delivered' orders
    $currentOrders = getDeliveredOrders($conn);
    foreach ($currentOrders as $order) {
        if (!isset($lastKnownStatus[$order['order_id']])) {
            $lastKnownStatus[$order['order_id']] = $order['status'];
            echo "New Delivered order detected: {$order['order_id']}\n";
        }
    }
}
?>