<?php
require_once '../database/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $order_id = $_POST['order_id'];
    $regular_weight = floatval($_POST['regular_weight']); // Weight of regular clothes
    $special_weight = floatval($_POST['special_weight']); // Weight of comforter/curtains
    
    if (!$order_id || ($regular_weight <= 0 && $special_weight <= 0)) {
        throw new Exception('Invalid order ID or weights');
    }
    
    $total_weight = $regular_weight + $special_weight;
    
    $db->beginTransaction();

    // Get order details
    $orderSql = "SELECT o.*, 
                GROUP_CONCAT(CONCAT(pc.brand, ':', loi.quantity)) as items
                FROM orders o
                LEFT JOIN laundry_ordered_items loi ON o.order_id = loi.order_id
                LEFT JOIN `product codes` pc ON loi.product_code_id = pc.code_id
                WHERE o.order_id = :order_id
                AND o.business_type = 'Laundry System'
                GROUP BY o.order_id";

    $orderStmt = $db->prepare($orderSql);
    $orderStmt->execute([':order_id' => $order_id]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    $is_rushed = $order['is_rushed'];
    $itemsData = explode(',', $order['items']);

    $total_price = 0;
    $standard_clothes_price = 0;
    $special_items_price = 0;

    $barong_count = 0;
    $gown_count = 0;

    // counter ng barong and gowns
    foreach($itemsData as $item) {
        list($brand, $qty) = explode(':', $item);
        $qty = intval($qty);
        
        if($brand === 'barong') {
            $barong_count += $qty;
        }
        if($brand === 'gowns') {
            $gown_count += $qty;
        }
    }
    
    // Calculate per-piece pricing for barong and gown
    if ($barong_count > 0) {
        $special_items_price += ($barong_count * 250);
    }
    if ($gown_count > 0) {
        $special_items_price += ($gown_count * 500);
    }
    
    // Calculate comforter/curtains pricing
    if ($special_weight > 0) {
        $special_items_price += ($special_weight * 60);
    }
    
    // Calculate standard clothes pricing
    if ($regular_weight > 0) {
        if ($regular_weight < 4) {
            // Flat rate for under 4kg
            $standard_clothes_price = $is_rushed ? 160 : 120;
        } else {
            // Per kilo rate
            $rate_per_kg = $is_rushed ? 40 : 30;
            $standard_clothes_price = $regular_weight * $rate_per_kg;
        }
    }
    
    // Total price
    $total_price = $standard_clothes_price + $special_items_price;
    $price_per_kg = $total_weight > 0 ? ($total_price / $total_weight) : 0;
    
    // Update laundry_ordered_items with weight and pricing
    $updateItemsSql = "UPDATE laundry_ordered_items 
                       SET weight_kg = :weight_kg,
                           price_per_kg = :price_per_kg,
                           total = :total,
                           updated_at = NOW()
                       WHERE order_id = :order_id";
    $updateItemsStmt = $db->prepare($updateItemsSql);
    $updateItemsStmt->execute([
        ':weight_kg' => $total_weight,
        ':price_per_kg' => $price_per_kg,
        ':total' => $total_price,
        ':order_id' => $order_id
    ]);
    
    // Update order status to "Delivered"
    $updateOrderSql = "UPDATE orders 
                       SET status = 'Delivered',
                           updated_at = NOW()
                       WHERE order_id = :order_id";
    $updateOrderStmt = $db->prepare($updateOrderSql);
    $updateOrderStmt->execute([':order_id' => $order_id]);
    
    // Create transaction record
    $transactionSql = "INSERT INTO transactions 
                       (order_id, customer_id, user_id, transaction_date, payment_status, credit, created_at)
                       VALUES (:order_id, :customer_id, :user_id, NOW(), 'Unpaid', :credit, NOW())";
    $transactionStmt = $db->prepare($transactionSql);
    $transactionStmt->execute([
        ':order_id' => $order_id,
        ':customer_id' => $order['customer_id'],
        ':user_id' => $order['user_id'],
        ':credit' => $total_price
    ]);
    
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'total' => number_format($total_price, 2),
        'total_weight' => $total_weight,
        'breakdown' => [
            'regular_clothes' => [
                'weight' => $regular_weight . ' kg',
                'price' => '₱' . number_format($standard_clothes_price, 2)
            ],
            'comforter_curtains' => [
                'weight' => $special_weight . ' kg',
                'price' => '₱' . number_format($special_weight * 60, 2)
            ],
            'barong' => $barong_count > 0 ? [
                'quantity' => $barong_count . ' pcs',
                'price' => '₱' . number_format($barong_count * 250, 2)
            ] : null,
            'gown' => $gown_count > 0 ? [
                'quantity' => $gown_count . ' pcs',
                'price' => '₱' . number_format($gown_count * 500, 2)
            ] : null
        ],
        'message' => 'Order delivered successfully'
    ]);
    
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>