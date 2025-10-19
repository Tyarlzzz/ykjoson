<?php
    require_once 'Order.php';
    require_once 'LaundryArchivedOrder.php';

    class Laundry extends Order {
        public static function all() {
            // Get all orders and filter by business_type = 'Laundry System'
            $allOrders = parent::all();
            if (!$allOrders) return null;

            $laundryOrders = array_filter($allOrders, function($order) {
                return isset($order->business_type) && $order->business_type === 'Laundry System';
            });

            // Exclude archived orders
            return array_filter($laundryOrders, function($order) {
                return !LaundryArchivedOrder::isOrderArchived($order->order_id);
            });
        }

        public static function where($column, $operation, $value) {
            // First get filtered results from parent where method
            $results = parent::where($column, $operation, $value);

            if (!$results) return null;

            // Filter by business_type = 'Laundry System'
            $laundryResults = array_filter($results, function($order) {
                return isset($order->business_type) && $order->business_type === 'Laundry System';
            });

            // Exclude archived orders
            return array_filter($laundryResults, function($order) {
                return !LaundryArchivedOrder::isOrderArchived($order->order_id);
            });
        }

        public static function find($id) {
            $result = parent::find($id);
            if ($result && isset($result->business_type) && $result->business_type === 'Laundry System') {
                return $result;
            }
            return null;
        }

        public static function create(array $data) {
            $data['business_type'] = 'Laundry System';
            return parent::create($data);
        }

        public static function countOnHold() {
            $onHoldOrders = self::where('status', '=', 'On Hold');
            return $onHoldOrders ? count($onHoldOrders) : 0;
        }

        public static function countOnWash() {
            $onWashOrders = self::where('status', '=', 'On Wash');
            return $onWashOrders ? count($onWashOrders) : 0;
        }

        public static function countOnDry() {
            $onDryOrders = self::where('status', '=', 'On Dry');
            return $onDryOrders ? count($onDryOrders) : 0;
        }

        public static function countOnFold() {
            $onFoldOrders = self::where('status', '=', 'On Fold');
            return $onFoldOrders ? count($onFoldOrders) : 0;
        }

        public static function countForDelivery() {
            $forDeliveryOrders = self::where('status', '=', 'For Delivery');
            return $forDeliveryOrders ? count($forDeliveryOrders) : 0;
        }

        public static function countDelivered() {
            $deliveredOrders = self::where('status', '=', 'Delivered');
            return $deliveredOrders ? count($deliveredOrders) : 0;
        }
        public static function countRushedOrders() {
            try {
                $sql = "SELECT COUNT(*) as count
                        FROM orders
                        WHERE business_type = 'Laundry System'
                        AND is_rushed = 1
                        AND status != 'Delivered'";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] ?? 0;
                
            } catch (PDOException $e) {
                die("Error counting rushed orders: " . $e->getMessage());
            }
        }

        public static function getTodaysOrders() {
            try {
                $today = date('Y-m-d');
                
                $sql = "SELECT 
                            o.order_id,
                            o.status,
                            o.is_rushed,
                            o.created_at,
                            c.fullname,
                            c.address,
                            c.phone_number,
                            COALESCE((SELECT SUM(loi.quantity) 
                            FROM laundry_ordered_items loi
                            WHERE loi.order_id = o.order_id), 0) as total_items
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN laundry_archived_orders lao ON o.order_id = lao.order_id
                        WHERE o.business_type = 'Laundry System'
                        AND DATE(o.created_at) = :today
                        AND lao.order_id IS NULL
                        ORDER BY o.created_at DESC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':today', $today);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 ? $results : null;
                
            } catch (PDOException $e) {
                die("Error fetching today's orders: " . $e->getMessage());
            }
        }

        public static function getAllLaundryWithDetails() {
    try {
        $sql = "SELECT 
                    o.order_id,
                    o.status,
                    o.is_rushed,
                    o.archive_at,  -- ✅ include this line
                    c.fullname,
                    o.created_at,
                    c.address,
                    c.phone_number,
                    COALESCE(SUM(loi.quantity), 0) AS total_quantity
                FROM orders o
                INNER JOIN customer c ON o.customer_id = c.customer_id
                LEFT JOIN laundry_ordered_items loi ON o.order_id = loi.order_id
                LEFT JOIN laundry_archived_orders lao ON o.order_id = lao.order_id
                WHERE o.business_type = 'Laundry System'
                AND lao.order_id IS NULL
                GROUP BY 
                    o.order_id, 
                    o.status, 
                    o.is_rushed, 
                    o.archive_at,  -- ✅ add here too
                    o.created_at, 
                    c.fullname, 
                    c.address, 
                    c.phone_number
                ORDER BY o.created_at DESC";

        $stmt = self::$conn->prepare($sql);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return count($results) > 0 ? $results : null;

    } catch (PDOException $e) {
        die("Error fetching laundry orders: " . $e->getMessage());
    }
}

    }
?>