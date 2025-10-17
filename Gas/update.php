<?php
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/GasOrder.php';
    require_once '../Models/Item_inventory.php';

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $conn->beginTransaction();

            // Get form data
            $orderId = intval($_POST['orderId'] ?? 0);
            $customerId = intval($_POST['customerId'] ?? 0);
            $fullName = trim($_POST['fullName'] ?? '');
            $phoneNumber = trim($_POST['phoneNumber'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $note = trim($_POST['note'] ?? '');
            
            $newPetronQty = intval($_POST['petronQty'] ?? 0);
            $newEconoQty = intval($_POST['econoQty'] ?? 0);
            $newSeagasQty = intval($_POST['seagasQty'] ?? 0);
            
            $originalPetronQty = intval($_POST['originalPetronQty'] ?? 0);
            $originalEconoQty = intval($_POST['originalEconoQty'] ?? 0);
            $originalSeagasQty = intval($_POST['originalSeagasQty'] ?? 0);

            if (empty($orderId) || empty($customerId) || empty($fullName) || empty($phoneNumber) || empty($address)) {
                throw new Exception('All required fields must be filled');
            }

            if (!preg_match('/^[0-9]{10,11}$/', $phoneNumber)) {
                throw new Exception('Invalid phone number format. Use 10-11 digits.');
            }

            if ($newPetronQty == 0 && $newEconoQty == 0 && $newSeagasQty == 0) {
                throw new Exception('Order must have at least one item');
            }

            $customerUpdated = GasOrder::updateCustomer($customerId, $fullName, $phoneNumber, $address);
            if (!$customerUpdated) {
                throw new Exception('Failed to update customer information');
            }

            $noteUpdated = GasOrder::updateOrderNote($orderId, $note);
            if (!$noteUpdated) {
                throw new Exception('Failed to update order note');
            }

            $existingItems = GasOrder::getOrderItems($orderId);
            
            $existingBrands = [];
            if ($existingItems) {
                foreach ($existingItems as $item) {
                    $brandLower = strtolower($item['item_name']);
                    $existingBrands[$brandLower] = [
                        'item_order_id' => $item['item_order_id'],
                        'allotment_id' => $item['allotment_id'],
                        'item_id' => $item['item_id'],
                        'product_code_id' => $item['product_code_id'],
                        'unit_price' => $item['unit_price']
                    ];
                }
            }

            $brandConfig = [
                'petron' => [
                    'new_qty' => $newPetronQty,
                    'original_qty' => $originalPetronQty,
                    'product_code_id' => 1, // Petron code_id
                    'item_name' => 'Petron'
                ],
                'econo' => [
                    'new_qty' => $newEconoQty,
                    'original_qty' => $originalEconoQty,
                    'product_code_id' => 2, // Econo code_id
                    'item_name' => 'Econo'
                ],
                'seagas' => [
                    'new_qty' => $newSeagasQty,
                    'original_qty' => $originalSeagasQty,
                    'product_code_id' => 3, // SeaGas code_id
                    'item_name' => 'SeaGas'
                ]
            ];

            foreach ($brandConfig as $brandKey => $config) {
                $newQty = $config['new_qty'];
                $originalQty = $config['original_qty'];
                
                $brandExists = isset($existingBrands[$brandKey]);
                
                if ($newQty > 0) {
                    if ($brandExists) {
                        $itemData = $existingBrands[$brandKey];
                        $qtyDifference = $newQty - $originalQty;
                        
                        $itemUpdated = GasOrder::updateOrderItem(
                            $itemData['item_order_id'], 
                            $newQty, 
                            $itemData['unit_price']
                        );
                        
                        if (!$itemUpdated) {
                            throw new Exception("Failed to update {$config['item_name']} order item");
                        }

                        $totalCost = $newQty * $itemData['unit_price'];
                        $allotmentUpdated = GasOrder::updateAllotment(
                            $itemData['allotment_id'], 
                            $newQty, 
                            $totalCost
                        );
                        
                        if (!$allotmentUpdated) {
                            throw new Exception("Failed to update {$config['item_name']} allotment");
                        }

                        if ($qtyDifference != 0) {
                            $inventoryAdjustment = -$qtyDifference;
                            $inventoryUpdated = Item_inventory::adjustStock(
                                $itemData['item_id'], 
                                $inventoryAdjustment
                            );
                            
                            if (!$inventoryUpdated) {
                                throw new Exception("Failed to adjust inventory for {$config['item_name']}");
                            }
                        }
                    } else {
                        $item = Item_inventory::getByItemName($config['item_name']);
                        
                        if (!$item) {
                            throw new Exception("{$config['item_name']} not found in inventory");
                        }
                        
                        if ($item->stocks < $newQty) {
                            throw new Exception("Insufficient stock for {$config['item_name']}. Available: {$item->stocks}");
                        }
                        
                        $allotmentId = GasOrder::createAllotment($item->item_id, $newQty, $item->cost * $newQty);
                        
                        if (!$allotmentId) {
                            throw new Exception("Failed to create allotment for {$config['item_name']}");
                        }
                        
                        $itemCreated = GasOrder::createOrderItem(
                            $orderId,
                            $config['product_code_id'],
                            $allotmentId,
                            $newQty,
                            $item->cost,
                            $item->cost * $newQty
                        );
                        
                        if (!$itemCreated) {
                            throw new Exception("Failed to create order item for {$config['item_name']}");
                        }
                        
                        $inventoryUpdated = Item_inventory::adjustStock($item->item_id, -$newQty);
                        
                        if (!$inventoryUpdated) {
                            throw new Exception("Failed to reduce inventory for {$config['item_name']}");
                        }
                    }
                } else if ($originalQty > 0) {
                    if ($brandExists) {
                        $itemData = $existingBrands[$brandKey];
                        
                        $itemDeleted = GasOrder::deleteOrderItem($itemData['item_order_id']);
                        
                        if (!$itemDeleted) {
                            throw new Exception("Failed to delete {$config['item_name']} from order");
                        }
                        
                        $inventoryUpdated = Item_inventory::adjustStock($itemData['item_id'], $originalQty);
                        
                        if (!$inventoryUpdated) {
                            throw new Exception("Failed to restore inventory for {$config['item_name']}");
                        }
                    }
                }
            }

            $conn->commit();

            header('Location: orderlist.php?success=' . urlencode('Order updated successfully!'));
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            
            header('Location: edit.php?id=' . ($orderId ?? 0) . '&error=' . urlencode($e->getMessage()));
            exit();
        }
    } else {
        header('Location: orderlist.php');
        exit();
    }
?>