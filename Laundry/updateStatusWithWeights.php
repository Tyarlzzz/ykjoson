<?php  
header('Content-Type: application/json');
require_once __DIR__ . '/../database/Database.php';

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['order_id'], $data['status'], $data['clothes_weight'], $data['comforter_curtains_weight'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$order_id = intval($data['order_id']);
$status = $data['status'];
$clothes_weight = floatval($data['clothes_weight']);
$comforter_curtains_weight = floatval($data['comforter_curtains_weight']);
$total_weight = $clothes_weight + $comforter_curtains_weight;

try {
    $db = new Database();
    $conn = $db->getConnection();


    $orderStmt = $conn->prepare("SELECT is_rushed FROM orders WHERE order_id = :order_id");
    $orderStmt->execute([':order_id' => $order_id]);
    $order = $orderStmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $is_rushed = (int)$order['is_rushed'];

    // ✅ Step 2: Update order status
    $updateOrder = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
    $updateOrder->execute([':status' => $status, ':order_id' => $order_id]);


    $pricingStmt = $conn->query("
        SELECT item_type, standard_price, rush_price, minimum_weight, flat_rate_standard, flat_rate_rush 
        FROM laundry_pricing
    ");
    $pricing = [];
    while ($row = $pricingStmt->fetch()) {
        $pricing[$row['item_type']] = $row;
    }


    $clothes_price_per_kg = $is_rushed 
        ? $pricing['standard_clothes']['rush_price'] 
        : $pricing['standard_clothes']['standard_price'];

    $comforter_price_per_kg = $is_rushed 
        ? $pricing['comforter_curtains']['rush_price'] 
        : $pricing['comforter_curtains']['standard_price'];


    $min_weight = $pricing['standard_clothes']['minimum_weight'];
    $flat_rate_standard = $pricing['standard_clothes']['flat_rate_standard'];
    $flat_rate_rush = $pricing['standard_clothes']['flat_rate_rush'];

    if ($clothes_weight < $min_weight && $min_weight > 0) {
        $clothes_total = $is_rushed ? $flat_rate_rush : $flat_rate_standard;
    } else {
        $clothes_total = $clothes_weight * $clothes_price_per_kg;
    }

    $comforter_total = $comforter_curtains_weight * $comforter_price_per_kg;
    $grand_total = $clothes_total + $comforter_total;


    $updateClothes = $conn->prepare("
        UPDATE laundry_ordered_items
        SET weight_kg = :weight_kg,
            price_per_kg = :price_per_kg,
            total = :total
        WHERE order_id = :order_id
        AND product_code_id IN (
            SELECT code_id FROM `product codes`
            WHERE business_type = 'Laundry System' AND category = 'Clothing'
        )
    ");
    $updateClothes->execute([
        ':weight_kg' => $clothes_weight,
        ':price_per_kg' => $clothes_price_per_kg, 
        ':total' => $clothes_total,
        ':order_id' => $order_id
    ]);

    // ✅ Step 7: Update COMFORTER items
    $updateComforter = $conn->prepare("
        UPDATE laundry_ordered_items
        SET weight_kg = :weight_kg,
            price_per_kg = :price_per_kg,
            total = :total
        WHERE order_id = :order_id
        AND product_code_id IN (
            SELECT code_id FROM `product codes`
            WHERE business_type = 'Laundry System'
            AND product_name IN ('Curtains', 'Comforter')
        )
    ");
    $updateComforter->execute([
        ':weight_kg' => $comforter_curtains_weight,
        ':price_per_kg' => $comforter_price_per_kg, 
        ':total' => $comforter_total,
        ':order_id' => $order_id
    ]);

    // ✅ Step 8: Return JSON
    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'is_rushed' => $is_rushed,
        'pricing_used' => $is_rushed ? 'Rush' : 'Standard',
        'total_price' => $grand_total,
        'details' => [
            'clothes_total' => $clothes_total,
            'comforter_total' => $comforter_total,
            'grand_total' => $grand_total
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
