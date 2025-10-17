<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/LaundryCustomer.php';
require_once '../Models/Order.php';
require_once '../Models/Laundry.php';
include '../layout/header.php';

// Get order ID from URL
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$order_id) {
    echo '<script>
            Swal.fire({
                title: "Error!",
                text: "Invalid order ID",
                icon: "error"
            }).then(function() {
                window.location = "orderlist.php";
            });
        </script>';
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    Model::setConnection($db);

    $db->beginTransaction();

    // First, check if the order exists and get customer info
    $checkSql = "SELECT o.order_id, o.customer_id, c.fullname 
                 FROM orders o
                 JOIN customer c ON o.customer_id = c.customer_id
                 WHERE o.order_id = :order_id";
    $checkStmt = $db->prepare($checkSql);
    $checkStmt->execute([':order_id' => $order_id]);
    $order = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

    $customer_id = $order['customer_id'];
    $customer_name = $order['fullname'];

    // Delete order items first (due to foreign key constraints)
    $deleteItemsSql = "DELETE FROM laundry_ordered_items WHERE order_id = :order_id";
    $deleteItemsStmt = $db->prepare($deleteItemsSql);
    $deleteItemsStmt->execute([':order_id' => $order_id]);

    // Delete the order
    $deleteOrderSql = "DELETE FROM orders WHERE order_id = :order_id";
    $deleteOrderStmt = $db->prepare($deleteOrderSql);
    $deleteOrderStmt->execute([':order_id' => $order_id]);

    // Check if customer has any other orders
    $checkCustomerOrdersSql = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = :customer_id";
    $checkCustomerOrdersStmt = $db->prepare($checkCustomerOrdersSql);
    $checkCustomerOrdersStmt->execute([':customer_id' => $customer_id]);
    $customerOrderCount = $checkCustomerOrdersStmt->fetch(PDO::FETCH_ASSOC);

    // If customer has no more orders, optionally delete the customer record
    // Comment out the following block if you want to keep customer records
    if ($customerOrderCount['order_count'] == 0) {
        $deleteCustomerSql = "DELETE FROM customer WHERE customer_id = :customer_id";
        $deleteCustomerStmt = $db->prepare($deleteCustomerSql);
        $deleteCustomerStmt->execute([':customer_id' => $customer_id]);
    }

    $db->commit();

    echo '<script>
            Swal.fire({
                title: "Deleted!",
                text: "Order for ' . addslashes($customer_name) . ' has been deleted successfully.",
                icon: "success"
            }).then(function() {
                window.location = "orderlist.php";
            });
        </script>';

} catch (Exception $e) {
    
    if($db->inTransaction()) {
        $db->rollBack();
    }
    
    echo '<script>
            Swal.fire({
                title: "Error!",
                text: "' . addslashes($e->getMessage()) . '",
                icon: "error"
            }).then(function() {
                window.location = "orderlist.php";
            });
        </script>';
}

include '../layout/footer.php';
?>