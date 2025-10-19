<?php
/**
 * Process Pending Archives
 * This script checks for orders that are ready to be archived
 * Run this via cron job every minute: * * * * * /Applications/XAMPP/xamppfiles/bin/php /path/to/process_pending_archives.php
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../Models/Models.php';
require_once __DIR__ . '/../Models/LaundryArchivedOrder.php';

$pendingArchivesFile = __DIR__ . '/pending_archives.json';

if (!file_exists($pendingArchivesFile)) {
    // No pending archives
    exit(0);
}

$pendingArchives = json_decode(file_get_contents($pendingArchivesFile), true);
if (!$pendingArchives) {
    exit(0);
}

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

$currentTime = time();
$remainingArchives = [];
$processedCount = 0;

foreach ($pendingArchives as $archive) {
    if ($currentTime >= $archive['archive_at']) {
        // Time to archive this order
        try {
            $orderId = $archive['order_id'];
            
            // Check if order is still in "Paid" status and not already archived
            $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND business_type = 'Laundry System'");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($order && $order['status'] === 'Paid' && !LaundryArchivedOrder::isOrderArchived($orderId)) {
                if (LaundryArchivedOrder::archiveOrder($orderId)) {
                    error_log("Successfully archived order ID: $orderId");
                    $processedCount++;
                } else {
                    error_log("Failed to archive order ID: $orderId");
                    $remainingArchives[] = $archive; // Keep for retry
                }
            } else {
                error_log("Order ID: $orderId is no longer eligible for archiving (status: " . ($order['status'] ?? 'not found') . ")");
            }
        } catch (Exception $e) {
            error_log("Error processing archive for order ID: " . $archive['order_id'] . " - " . $e->getMessage());
            $remainingArchives[] = $archive; // Keep for retry
        }
    } else {
        // Not time yet, keep in pending
        $remainingArchives[] = $archive;
    }
}

// Update the pending archives file
if (empty($remainingArchives)) {
    unlink($pendingArchivesFile);
} else {
    file_put_contents($pendingArchivesFile, json_encode($remainingArchives, JSON_PRETTY_PRINT));
}

if ($processedCount > 0) {
    error_log("Processed $processedCount pending archives");
}

exit(0);
?>