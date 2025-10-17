<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/LaundryCustomer.php';
require_once '../Models/Order.php';
require_once '../Models/Laundry.php';
include '../layout/header.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    try {
        $database = new Database();
        $db = $database->getConnection();
        Model::setConnection($db);

        $db->beginTransaction();

        // Get order and customer IDs
        $order_id = intval($_POST['order_id']);
        $customer_id = intval($_POST['customer_id']);

        if(!$order_id || !$customer_id){
            throw new Exception("Invalid order or customer ID");
        }

        // Update customer information
        $customerData = [
            'customer_id' => $customer_id,
            'fullname' => $_POST['fullname'],
            'address' => $_POST['address'],
            'phone_number' => $_POST['phone_number']
        ];

        $custSql = "UPDATE customer 
                    SET fullname = :fullname, 
                        address = :address, 
                        phone_number = :phone_number,
                        updated_at = NOW()
                    WHERE customer_id = :customer_id";
        $custStmt = $db->prepare($custSql);
        $custStmt->execute($customerData);

        // Update order information
        $isRushed = isset($_POST['rushOrder']) && $_POST['rushOrder'] === 'on' ? 1 : 0;

        $orderSql = "UPDATE orders 
                     SET is_rushed = :is_rushed, 
                         note = :note,
                         updated_at = NOW()
                     WHERE order_id = :order_id";
        
        $orderStmt = $db->prepare($orderSql);
        $orderStmt->execute([
            ':order_id' => $order_id,
            ':is_rushed' => $isRushed,
            ':note' => $_POST['note'] ?? ''
        ]);

        // Delete existing order items
        $deleteSql = "DELETE FROM laundry_ordered_items WHERE order_id = :order_id";
        $deleteStmt = $db->prepare($deleteSql);
        $deleteStmt->execute([':order_id' => $order_id]);

        // Prepare items array
        $items = [
            'topsQty' => [
                'qty' => intval($_POST['topsQty'] ?? 0),
                'code_id' => 4,
                'name' => 'Tops'
            ],
            'bottomsQty' => [
                'qty' => intval($_POST['bottomsQty'] ?? 0),
                'code_id' => 5,
                'name' => 'Bottoms'
            ],
            'undiesQty' => [
                'qty' => intval($_POST['undiesQty'] ?? 0),
                'code_id' => 6,
                'name' => 'Underwears'
            ],
            'socksQty' => [
                'qty' => intval($_POST['socksQty'] ?? 0),
                'code_id' => 7,
                'name' => 'Socks'
            ],
            'towelsQty' => [
                'qty' => intval($_POST['towelsQty'] ?? 0),
                'code_id' => 8,
                'name' => 'Towels'
            ],
            'bedsQty' => [
                'qty' => intval($_POST['bedsQty'] ?? 0),
                'code_id' => 9,
                'name' => 'Bedsheets'
            ],
            'gownsQty' => [
                'qty' => intval($_POST['gownsQty'] ?? 0),
                'code_id' => 10,
                'name' => 'Gowns'
            ],
            'barongQty' => [
                'qty' => intval($_POST['barongQty'] ?? 0),
                'code_id' => 11,
                'name' => 'Barong'
            ],
            'curtainsQty' => [
                'qty' => intval($_POST['curtainsQty'] ?? 0),
                'code_id' => 12,
                'name' => 'Curtains'
            ],
            'comforterQty' => [
                'qty' => intval($_POST['comforterQty'] ?? 0),
                'code_id' => 13,
                'name' => 'Comforter'
            ]
        ];

        $itemsInserted = false;

        // Insert new order items
        foreach($items as $itemKey => $data){
            if($data['qty'] > 0){
                
                $product_code_id = $data['code_id'];

                // Insert into laundry_ordered_items with quantity
                $orderedItemSql = "INSERT INTO laundry_ordered_items 
                                (order_id, product_code_id, quantity, weight_kg, price_per_kg, total, created_at) 
                                VALUES (:order_id, :product_code_id, :quantity, :weight_kg, :price_per_kg, :total, NOW())";
                $orderedItemStmt = $db->prepare($orderedItemSql);
                $orderedItemStmt->execute([
                    ':order_id' => $order_id,
                    ':product_code_id' => $product_code_id,
                    ':quantity' => $data['qty'],
                    ':weight_kg' => 0, 
                    ':price_per_kg' => 0, 
                    ':total' => 0   
                ]);

                $itemsInserted = true;
            }
        }

        if(!$itemsInserted){
            throw new Exception("No items were added to the order. Please select at least one item.");
        }

        $db->commit();

        echo '<script>
                Swal.fire({
                    title: "Success!",
                    text: "Order updated successfully for ' . addslashes($_POST['fullname']) . '!",
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
                    window.location = "edit.php?order_id=' . intval($_POST['order_id']) . '";
                });
            </script>';
    }
}

include '../layout/footer.php';
?>