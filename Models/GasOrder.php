<?php
    require_once 'Order.php';

    class GasOrder extends Order {
        public static function all() {
            $allOrders = parent::all();
            if (!$allOrders) return null;

            return array_filter($allOrders, function($order) {
                return isset($order->business_type) && $order->business_type === 'Gas System';
            });
        }

        public static function where($column, $operation, $value) {
            $results = parent::where($column, $operation, $value);

            if (!$results) return null;

            return array_filter($results, function($order) {
                return isset($order->business_type) && $order->business_type === 'Gas System';
            });
        }

        public static function find($id) {
            $result = parent::find($id);
            if ($result && isset($result->business_type) && $result->business_type === 'Gas System') {
                return $result;
            }
            return null;
        }

        public static function create(array $data) {
            $data['business_type'] = 'Gas System';
            return parent::create($data);
        }

        public static function countPending() {
            $pendingOrders = self::where('status', '=', 'Pending');
            return $pendingOrders ? count($pendingOrders) : 0;
        }

        public static function countDelivered() {
            $deliveredOrders = self::where('status', '=', 'Delivered');
            return $deliveredOrders ? count($deliveredOrders) : 0;
        }

        public static function countReturned() {
            $returnedOrders = self::where('status', '=', 'Returned');
            return $returnedOrders ? count($returnedOrders) : 0;
        }

        public static function countBorrowed() {
            $borrowedOrders = self::where('status', '=', 'Borrowed');
            return $borrowedOrders ? count($borrowedOrders) : 0;
        }

        public static function getAllOrdersWithDetails() {
            try {
                $sql = "SELECT 
                            o.order_id,
                            o.status,
                            o.is_rushed,
                            o.created_at,
                            c.fullname,
                            c.address,
                            c.phone_number,
                            GROUP_CONCAT(DISTINCT ii.item_name ORDER BY ii.item_name SEPARATOR ', ') as brands,
                            COALESCE(SUM(goi.quantity), 0) as total_quantity
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN gas_ordered_items goi ON o.order_id = goi.order_id
                        LEFT JOIN `item allotment` ia ON goi.allotment_id = ia.allotment_id
                        LEFT JOIN `item inventory` ii ON ia.item_id = ii.item_id
                        WHERE o.business_type = 'Gas System'
                        GROUP BY o.order_id, o.status, o.is_rushed, o.created_at, c.fullname, c.address, c.phone_number
                        ORDER BY o.created_at DESC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 ? $results : null;
                
            } catch (PDOException $e) {
                die("Error fetching gas orders: " . $e->getMessage());
            }
        }

        public static function getOrderWithDetails($order_id) {
            try {
                $sql = "SELECT 
                            o.order_id,
                            o.customer_id,
                            o.status,
                            o.is_rushed,
                            o.note,
                            o.created_at,
                            c.fullname,
                            c.address,
                            c.phone_number
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        WHERE o.order_id = :order_id 
                        AND o.business_type = 'Gas System'
                        LIMIT 1";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return $result ? $result : null;
                
            } catch (PDOException $e) {
                die("Error fetching order details: " . $e->getMessage());
            }
        }

        public static function getOrderItems($order_id) {
            try {
                $sql = "SELECT 
                            goi.item_order_id,
                            goi.order_id,
                            goi.product_code_id,
                            goi.allotment_id,
                            goi.quantity,
                            goi.unit_price,
                            goi.total,
                            pc.product_name,
                            pc.brand,
                            ii.item_id,
                            ii.item_name,
                            ii.stocks as current_stock
                        FROM gas_ordered_items goi
                        INNER JOIN `product codes` pc ON goi.product_code_id = pc.code_id
                        INNER JOIN `item allotment` ia ON goi.allotment_id = ia.allotment_id
                        INNER JOIN `item inventory` ii ON ia.item_id = ii.item_id
                        WHERE goi.order_id = :order_id
                        ORDER BY goi.item_order_id ASC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 ? $results : null;
                
            } catch (PDOException $e) {
                die("Error fetching order items: " . $e->getMessage());
            }
        }

        public static function updateOrderItem($item_order_id, $quantity, $unit_price) {
            try {
                $total = $quantity * $unit_price;
                
                $sql = "UPDATE gas_ordered_items 
                        SET quantity = :quantity, 
                            total = :total,
                            updated_at = NOW()
                        WHERE item_order_id = :item_order_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':quantity' => $quantity,
                    ':total' => $total,
                    ':item_order_id' => $item_order_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating order item: " . $e->getMessage());
            }
        }

        public static function updateAllotment($allotment_id, $quantity, $total_cost) {
            try {
                $sql = "UPDATE `item allotment` 
                        SET quantity = :quantity, 
                            total_cost = :total_cost,
                            updated_at = NOW()
                        WHERE allotment_id = :allotment_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':quantity' => $quantity,
                    ':total_cost' => $total_cost,
                    ':allotment_id' => $allotment_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating allotment: " . $e->getMessage());
            }
        }

        public static function updateCustomer($customer_id, $fullname, $phone_number, $address) {
            try {
                $sql = "UPDATE customer 
                        SET fullname = :fullname, 
                            phone_number = :phone_number, 
                            address = :address,
                            updated_at = NOW()
                        WHERE customer_id = :customer_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':fullname' => $fullname,
                    ':phone_number' => $phone_number,
                    ':address' => $address,
                    ':customer_id' => $customer_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating customer: " . $e->getMessage());
            }
        }

        public static function updateOrderNote($order_id, $note) {
            try {
                $sql = "UPDATE orders 
                        SET note = :note,
                            updated_at = NOW()
                        WHERE order_id = :order_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':note' => $note,
                    ':order_id' => $order_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating order note: " . $e->getMessage());
            }
        }

        public static function deleteOrder($order_id) {
            try {
                self::$conn->beginTransaction();

                $items = self::getOrderItems($order_id);
                
                if ($items) {
                    foreach ($items as $item) {
                        require_once 'Item_inventory.php';
                        Item_inventory::adjustStock($item['item_id'], $item['quantity']);
                    }
                }

                $sql = "DELETE FROM orders WHERE order_id = :order_id";
                $stmt = self::$conn->prepare($sql);
                $stmt->execute([':order_id' => $order_id]);

                self::$conn->commit();
                
                return true;
                
            } catch (PDOException $e) {
                self::$conn->rollBack();
                die("Error deleting order: " . $e->getMessage());
            }
        }

        public static function createAllotment($item_id, $quantity, $total_cost) {
            try {
                $sql = "INSERT INTO `item allotment` (item_id, quantity, total_cost, created_at) 
                        VALUES (:item_id, :quantity, :total_cost, NOW())";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute([
                    ':item_id' => $item_id,
                    ':quantity' => $quantity,
                    ':total_cost' => $total_cost
                ]);
                
                return self::$conn->lastInsertId();
                
            } catch (PDOException $e) {
                die("Error creating allotment: " . $e->getMessage());
            }
        }

        public static function createOrderItem($order_id, $product_code_id, $allotment_id, $quantity, $unit_price, $total) {
            try {
                $sql = "INSERT INTO gas_ordered_items 
                        (order_id, product_code_id, allotment_id, quantity, unit_price, total, created_at) 
                        VALUES (:order_id, :product_code_id, :allotment_id, :quantity, :unit_price, :total, NOW())";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_code_id' => $product_code_id,
                    ':allotment_id' => $allotment_id,
                    ':quantity' => $quantity,
                    ':unit_price' => $unit_price,
                    ':total' => $total
                ]);
                
            } catch (PDOException $e) {
                die("Error creating order item: " . $e->getMessage());
            }
        }

        public static function deleteOrderItem($item_order_id) {
            try {
                $sql = "DELETE FROM gas_ordered_items WHERE item_order_id = :item_order_id";
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([':item_order_id' => $item_order_id]);
                
            } catch (PDOException $e) {
                die("Error deleting order item: " . $e->getMessage());
            }
        }

        public static function getMonthlyBrandSales() {
            try {
                $sql = "SELECT 
                            ii.item_name as brand,
                            COALESCE(SUM(goi.quantity), 0) as total_quantity
                        FROM `item inventory` ii
                        LEFT JOIN `item allotment` ia ON ii.item_id = ia.item_id
                        LEFT JOIN gas_ordered_items goi ON ia.allotment_id = goi.allotment_id
                        LEFT JOIN orders o ON goi.order_id = o.order_id
                        WHERE ii.business_type = 'Gas System'
                        AND (o.created_at IS NULL OR (MONTH(o.created_at) = MONTH(CURRENT_DATE()) 
                        AND YEAR(o.created_at) = YEAR(CURRENT_DATE())))
                        GROUP BY ii.item_name
                        ORDER BY ii.item_name ASC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 ? $results : null;
                
            } catch (PDOException $e) {
                die("Error fetching monthly brand sales: " . $e->getMessage());
            }
        }
    }
?>