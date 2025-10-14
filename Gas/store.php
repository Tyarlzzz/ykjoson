<?php
    require_once '../database/Database.php';
    require_once '../Models/GasCustomer.php';
    include '../layout/header.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        try {

            $database = new Database();
            $db = $database->getConnection();
            Gas::setConnection($db);

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

            $orderSql = "INSERT INTO orders (business_type, customer_id, user_id, order_date, status, is_rushed, note, created_at) 
                         VALUES (:business_type, :customer_id, :user_id, :order_date, :status, :is_rushed, :note, NOW())";
            
            $orderStmt = $db->prepare($orderSql);
            $orderStmt->execute([
                ':business_type' => 'Gas System',
                ':customer_id' => $customer_id,
                ':user_id' => $user_id,
                ':order_date' => date('Y-m-d H:i:s'),
                ':status' => 'Pending',
                ':is_rushed' => 0,
                ':note' => $_POST['note'] ?? ''
            ]);

            $order_id = $db->lastInsertId();

            $brands = [
                'petron' => [
                    'qty' => intval($_POST['petronQty'] ?? 0),
                    'name' => 'Petron'
                ],
                'econo' => [
                    'qty' => intval($_POST['econoQty'] ?? 0),
                    'name' => 'Econo'
                ],
                'seagas' => [
                    'qty' => intval($_POST['seagasQty'] ?? 0),
                    'name' => 'SeaGas'
                ]
            ];

            foreach($brands as $brand => $data){
                if($data['qty'] > 0){
                    
                    $productSql = "SELECT code_id FROM `product codes` 
                                   WHERE business_type = 'Gas System' 
                                   AND brand = :brand LIMIT 1";
                    $productStmt = $db->prepare($productSql);
                    $productStmt->execute([':brand' => $data['name']]);
                    $product = $productStmt->fetch(PDO::FETCH_ASSOC);

                    if(!$product){
                        throw new Exception("Product code not found for " . $data['name']);
                    }

                    $product_code_id = $product['code_id'];

                    $itemSql = "SELECT item_id, stocks, cost FROM `item inventory` 
                                WHERE business_type = 'Gas System' 
                                AND item_name = :item_name LIMIT 1";
                    $itemStmt = $db->prepare($itemSql);
                    $itemStmt->execute([':item_name' => $data['name']]);
                    $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

                    if(!$item){
                        throw new Exception("Item not found in inventory for " . $data['name']);
                    }

                    if($item['stocks'] < $data['qty']){
                        throw new Exception("Insufficient stock for " . $data['name'] . ". Available: " . $item['stocks']);
                    }

                    $item_id = $item['item_id'];
                    $unit_price = $item['cost'];
                    $total = $unit_price * $data['qty'];

                    $allotmentSql = "INSERT INTO `item allotment` (item_id, quantity, total_cost, created_at) 
                                     VALUES (:item_id, :quantity, :total_cost, NOW())";
                    $allotmentStmt = $db->prepare($allotmentSql);
                    $allotmentStmt->execute([
                        ':item_id' => $item_id,
                        ':quantity' => $data['qty'],
                        ':total_cost' => $total
                    ]);

                    $allotment_id = $db->lastInsertId();

                    $updateStockSql = "UPDATE `item inventory` 
                                       SET stocks = stocks - :quantity, 
                                           updated_at = NOW() 
                                       WHERE item_id = :item_id";
                    $updateStockStmt = $db->prepare($updateStockSql);
                    $updateStockStmt->execute([
                        ':quantity' => $data['qty'],
                        ':item_id' => $item_id
                    ]);

                    $orderedItemSql = "INSERT INTO gas_ordered_items 
                                       (order_id, product_code_id, allotment_id, quantity, unit_price, total, created_at) 
                                       VALUES (:order_id, :product_code_id, :allotment_id, :quantity, :unit_price, :total, NOW())";
                    $orderedItemStmt = $db->prepare($orderedItemSql);
                    $orderedItemStmt->execute([
                        ':order_id' => $order_id,
                        ':product_code_id' => $product_code_id,
                        ':allotment_id' => $allotment_id,
                        ':quantity' => $data['qty'],
                        ':unit_price' => $unit_price,
                        ':total' => $total
                    ]);
                }
            }

            $db->commit();

            echo '<script>
                    Swal.fire({
                        title: "Good job!",
                        text: "You Successfully Created an Order!",
                        icon: "success"
                    }).then(function() {
                        window.location = "index.php";
                    });
                </script>';

        } catch (Exception $e) {
            
            $db->rollBack();
            
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