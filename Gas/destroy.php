<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/GasOrder.php';

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

// Get order ID from URL
$orderId = isset($_GET['id']) ? intval($_GET['id']) : null;
$confirmed = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if (!$orderId) {
    header('Location: orderlist.php?error=' . urlencode('Invalid order ID'));
    exit();
}

if (!$confirmed) {
    header('Location: orderlist.php?error=' . urlencode('Deletion not confirmed'));
    exit();
}

try {
    // Delete order (this will also restore inventory)
    $deleted = GasOrder::deleteOrder($orderId);
    
    if ($deleted) {
        header('Location: orderlist.php?success=' . urlencode('Order deleted successfully and inventory restored!'));
        exit();
    } else {
        throw new Exception('Failed to delete order');
    }
    
} catch (Exception $e) {
    header('Location: orderlist.php?error=' . urlencode($e->getMessage()));
    exit();
}
?>