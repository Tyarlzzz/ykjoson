<?php
require_once 'Order.php';

class GasOrder extends Order {
    public static function all() {
        // Get all orders and filter by business_type = 'Gas System'
        $allOrders = parent::all();
        if (!$allOrders) return null;

        return array_filter($allOrders, function($order) {
            return isset($order->business_type) && $order->business_type === 'Gas System';
        });
    }

    public static function where($column, $operation, $value) {
        // First get filtered results from parent where method
        $results = parent::where($column, $operation, $value);

        if (!$results) return null;

        // Then filter by business_type = 'Gas System'
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
        // Ensure business_type is set to 'Gas System' for gas orders
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
                GROUP BY o.order_id, o.status, o.created_at, c.fullname, c.address, c.phone_number
                ORDER BY o.created_at DESC";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return count($results) > 0 ? $results : null;
        
    } catch (PDOException $e) {
        die("Error fetching gas orders: " . $e->getMessage());
    }
    }

    public static function getStatusColorClass($status) {
        switch($status) {
            case 'Pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'Delivered':
                return 'bg-green-100 text-green-800';
            case 'Returned':
                return 'bg-blue-100 text-blue-800';
            case 'Borrowed':
                return 'bg-purple-100 text-purple-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}
?>
