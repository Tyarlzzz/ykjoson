<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/GasOrder.php';

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

// pang kuha ng id sa url
$orderId = isset($_GET['id']) ? intval($_GET['id']) : null;

try {
    // Delete order and ma r restore ung nasa inventory
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