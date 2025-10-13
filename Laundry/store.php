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

        $customerData = [
            'fullname' => $_POST['fullname'],
            'address' => $_POST['address'],
            'phone_number' => $_POST['phone_number']
        ];

        $custSql = "INSERT INTO customer (fullname, address, phone_number, created_at) 
                    VALUES (:fullname, :address, :phone_number, NOW())";
        $custStmt = $db->prepare($custSql);
        $custStmt->execute($customerData);
        
        $customer_id = $db->lastInsertId();
        
        if(!$customer_id){
            throw new Exception("Failed to save customer");
        }

        $user_id = 1; 
        $isRushed = isset($_POST['rushOrder']) && $_POST['rushOrder'] === 'on' ? 1 : 0;

        $orderSql = "INSERT INTO orders (business_type, customer_id, user_id, order_date, status, is_rushed, note, created_at) 
                     VALUES (:business_type, :customer_id, :user_id, :order_date, :status, :is_rushed, :note, NOW())";
        
        $orderStmt = $db->prepare($orderSql);
        $orderStmt->execute([
            ':business_type' => 'Laundry System',
            ':customer_id' => $customer_id,
            ':user_id' => $user_id,
            ':order_date' => date('Y-m-d H:i:s'),
            ':status' => 'Pending',
            ':is_rushed' => $isRushed,
            ':note' => $_POST['note'] ?? ''
        ]);

        $order_id = $db->lastInsertId();

        if(!$order_id){
            throw new Exception("Failed to create order");
        }

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
                    text: "Order created successfully for ' . addslashes($_POST['fullname']) . '!",
                    icon: "success"
                }).then(function() {
                    window.location = "index.php";
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
                    window.location = "create.php";
                });
            </script>';
    }
}

include '../layout/footer.php';
?>