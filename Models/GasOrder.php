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
}
?>
