<?php
require_once 'Order.php';

class Laundry extends Order {
    public static function all() {
        // Get all orders and filter by business_type = 'Laundry System'
        $allOrders = parent::all();
        if (!$allOrders) return null;

        return array_filter($allOrders, function($order) {
            return isset($order->business_type) && $order->business_type === 'Laundry System';
        });
    }

    public static function where($column, $operation, $value) {
        // First get filtered results from parent where method
        $results = parent::where($column, $operation, $value);

        if (!$results) return null;

        // Then filter by business_type = 'Laundry System'
        return array_filter($results, function($order) {
            return isset($order->business_type) && $order->business_type === 'Laundry System';
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
        // Ensure business_type is set to 'Laundry System' for gas orders
        $data['business_type'] = 'Laundry System';
        return parent::create($data);
    }

    public static function countPending() {
        $pendingOrders = self::where('status', '=', 'Pending');
        return $pendingOrders ? count($pendingOrders) : 0;
    }

    public static function countOnHold() {
        $deliveredOrders = self::where('status', '=', 'OnHold');
        return $deliveredOrders ? count($deliveredOrders) : 0;
    }

    public static function countOnWash() {
        $returnedOrders = self::where('status', '=', 'OnWash');
        return $returnedOrders ? count($returnedOrders) : 0;
    }

    public static function countOnDry() {
        $borrowedOrders = self::where('status', '=', 'OnDry');
        return $borrowedOrders ? count($borrowedOrders) : 0;
    }

    public static function countOnFold() {
        $borrowedOrders = self::where('status', '=', 'OnFold');
        return $borrowedOrders ? count($borrowedOrders) : 0;
    }

    public static function countDelivered() {
        $borrowedOrders = self::where('status', '=', 'Delivered');
        return $borrowedOrders ? count($borrowedOrders) : 0;
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
                    WHERE o.business_type = 'Laundry System'
                    AND DATE(o.created_at) = :today
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
                        c.fullname,
                        o.created_at,
                        c.address,
                        c.phone_number,
                        COALESCE(SUM(loi.quantity), 0) as total_quantity
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    LEFT JOIN laundry_ordered_items loi ON o.order_id = loi.order_id
                    WHERE o.business_type = 'Laundry System'
                    GROUP BY o.order_id, o.status, o.created_at, c.fullname, c.address, c.phone_number
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