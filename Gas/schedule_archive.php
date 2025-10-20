<?php
/**
 * Schedule Archive Script
 * This script is called to archive a specific order after a 1-minute delay
 * It's executed as a background process by updateStatus.php
 */

// Check if order_id is provided as command line argument
if ($argc < 2) {
    error_log("Schedule Archive: No order ID provided");
    exit(1);
}

$order_id = intval($argv[1]);

if ($order_id <= 0) {
    error_log("Schedule Archive: Invalid order ID: " . $argv[1]);
    exit(1);
}

try {
    // Log start of archive process
    error_log("Schedule Archive: Starting archive process for order ID: $order_id");
    
    // Include required files
    require_once __DIR__ . '/../database/Database.php';
    require_once __DIR__ . '/../Models/Models.php';
    require_once __DIR__ . '/../Models/GasArchivedOrder.php';

    // Set up database connection
    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);
    
    error_log("Schedule Archive: Database connection established for order ID: $order_id");

    // Check if order is still eligible for archiving
    $stmt = $conn->prepare("SELECT status, paid_at FROM orders WHERE order_id = ? AND business_type = 'Gas System'");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        error_log("Schedule Archive: Order ID $order_id not found");
        exit(1);
    }

    if ($order['status'] !== 'Paid') {
        error_log("Schedule Archive: Order ID $order_id is no longer in 'Paid' status (current: {$order['status']})");
        exit(1);
    }

    if (empty($order['paid_at'])) {
        error_log("Schedule Archive: Order ID $order_id has no paid_at timestamp");
        exit(1);
    }

    // Check if order is already archived
    if (GasArchivedOrder::isOrderArchived($order_id)) {
        error_log("Schedule Archive: Order ID $order_id is already archived");
        exit(0);
    }

    // Archive the specific order
    if (GasArchivedOrder::archiveOrderWithCustomDeliveryDate($order_id)) {
        error_log("Schedule Archive: Successfully archived order ID $order_id");
        exit(0);
    } else {
        error_log("Schedule Archive: Failed to archive order ID $order_id");
        exit(1);
    }

} catch (Exception $e) {
    error_log("Schedule Archive Error for order ID $order_id: " . $e->getMessage());
    exit(1);
}
?>