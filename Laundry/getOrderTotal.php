<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/Database.php';

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing order_id']);
    exit;
}

$order_id = intval($data['order_id']);

try {
    $db = new Database();
    $conn = $db->getConnection();

    // Fetch total_price and is_rushed from orders table
    $stmt = $conn->prepare("SELECT total_price, is_rushed FROM orders WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch weight information and totals from laundry_ordered_items
    $itemsStmt = $conn->prepare("
        SELECT 
            loi.weight_kg,
            loi.price_per_kg,
            loi.quantity,
            loi.total,
            pc.product_name,
            pc.category
        FROM laundry_ordered_items loi
        JOIN `product codes` pc ON loi.product_code_id = pc.code_id
        WHERE loi.order_id = :order_id
        AND pc.business_type = 'Laundry System'
    ");
    $itemsStmt->execute([':order_id' => $order_id]);
    $items = $itemsStmt->fetchAll();

    // Calculate totals and weights
    $clothes_weight = 0;
    $comforter_curtains_weight = 0;
    $clothes_total = 0;
    $comforter_total = 0;
    $barong_qty = 0;
    $barong_total = 0;
    $barong_price = 0;
    $gowns_qty = 0;
    $gowns_total = 0;
    $gowns_price = 0;

    foreach ($items as $item) {
        $product_name = $item['product_name'];
        $category = $item['category'];
        
        if ($product_name === 'Barong') {
            $barong_qty = intval($item['quantity']);
            $barong_total = floatval($item['total']);
            $barong_price = $barong_qty > 0 ? floatval($item['price_per_kg']) : 0;
        } elseif ($product_name === 'Gowns') {
            $gowns_qty = intval($item['quantity']);
            $gowns_total = floatval($item['total']);
            $gowns_price = $gowns_qty > 0 ? floatval($item['price_per_kg']) : 0;
        } elseif ($product_name === 'Comforter' || $product_name === 'Curtains') {
            $comforter_curtains_weight += floatval($item['weight_kg']);
            $comforter_total += floatval($item['total']);
        } elseif ($category === 'Clothing' && $product_name !== 'Barong' && $product_name !== 'Gowns') {
            // Regular clothing items
            $clothes_weight += floatval($item['weight_kg']);
            $clothes_total += floatval($item['total']);
        }
    }

    $total_weight = $clothes_weight + $comforter_curtains_weight;

    echo json_encode([
        'success' => true,
        'total_price' => floatval($order['total_price'] ?? 0),
        'is_rushed' => $order['is_rushed'] ?? 0,
        'clothes_weight' => $clothes_weight,
        'comforter_curtains_weight' => $comforter_curtains_weight,
        'total_weight' => $total_weight,
        'breakdown' => [
            'clothes_total' => $clothes_total,
            'comforter_total' => $comforter_total,
            'barong_qty' => $barong_qty,
            'barong_price' => $barong_price,
            'barong_total' => $barong_total,
            'gowns_qty' => $gowns_qty,
            'gowns_price' => $gowns_price,
            'gowns_total' => $gowns_total
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>