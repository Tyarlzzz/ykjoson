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

    // Update the status
    $sql = "UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ? AND business_type = 'Laundry System'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_status, $order_id]);

    // If status is "Paid", schedule archiving for 60 seconds from now
    if ($new_status === 'Paid') {
        $archive_at = date('Y-m-d H:i:s', time() + 60); // Archive in 60 seconds
        $archiveStmt = $pdo->prepare("UPDATE orders SET archive_at = ? WHERE order_id = ?");
        $archiveStmt->execute([$archive_at, $order_id]);
        
        error_log("Order $order_id scheduled for archiving at $archive_at (60 seconds from now)");
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