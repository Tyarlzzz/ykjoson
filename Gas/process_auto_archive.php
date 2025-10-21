<?php
/**
 * AUTO-ARCHIVE PROCESSOR (Gas System Only)
 * ----------------------------------------
 * This script automatically archives Gas orders that:
 * - Have status = 'Paid'
 * - Have archive_at <= NOW()
 * - Belong to the 'Gas System'
 * - Are not yet in gas_archived_orders
 * 
 * It is triggered either:
 * - Automatically via JS (after ~70s)
 * - Or manually via a cron job
 */

ob_start();
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../Models/Models.php';
require_once __DIR__ . '/../Models/GasArchivedOrder.php';

ob_clean();
header('Content-Type: application/json');

try {
    $db = new Database();
    $pdo = $db->getConnection();
    Model::setConnection($pdo);

    // 🧠 1. Find Gas orders ready for archiving
    $sql = "SELECT order_id 
            FROM orders 
            WHERE business_type = 'Gas System'
              AND archive_at IS NOT NULL 
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

    // 🧠 2. Process each order found
    foreach ($ordersToArchive as $orderId) {
        try {
            error_log("🔁 Auto-archiving Gas order ID: $orderId");

            // ✅ Step 1: Archive order while still in 'Paid' status
            $result = GasArchivedOrder::archiveOrder($orderId);

            // ✅ Step 2: Change status to Delivered only after successful archive
            if ($result) {
                $updateStatusStmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = 'Delivered', updated_at = NOW()
                    WHERE order_id = ? AND status = 'Paid'
                ");
                $updateStatusStmt->execute([$orderId]);

                // Step 3: Clear archive_at after successful archive
                $clearStmt = $pdo->prepare("
                    UPDATE orders 
                    SET archive_at = NULL 
                    WHERE order_id = ?
                ");
                $clearStmt->execute([$orderId]);

                $archived[] = $orderId;
                error_log("✅ Successfully auto-archived Gas order ID: $orderId");
            } else {
                $errors[] = "❌ Failed to archive order $orderId";
                error_log("❌ Failed to archive Gas order ID: $orderId");
            }

        } catch (Exception $e) {
            $errors[] = "⚠️ Error archiving order $orderId: " . $e->getMessage();
            error_log("⚠️ Error auto-archiving Gas order ID $orderId: " . $e->getMessage());
        }
    }

    // 🧠 3. Return JSON summary
    ob_clean();
    echo json_encode([
        'success' => true,
        'system' => 'Gas System',
        'archived_count' => count($archived),
        'archived_orders' => $archived,
        'errors' => $errors
    ]);
    ob_end_flush();
    exit;

} catch (Exception $e) {
    error_log("🚨 Auto-archive processor error (Gas): " . $e->getMessage());
    ob_clean();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}
?>
