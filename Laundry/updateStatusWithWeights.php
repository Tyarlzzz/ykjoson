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

    // ✅ 1. Update order status
    $updateOrder = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
    $updateOrder->execute([':status' => $status, ':order_id' => $order_id]);

    // ✅ 2. Get pricing info
    $pricingStmt = $conn->query("SELECT item_type, standard_price FROM laundry_pricing");
    $pricing = [];
    while ($row = $pricingStmt->fetch()) {
        $pricing[$row['item_type']] = $row['standard_price'];
    }

    $clothes_price_per_kg = $pricing['standard_clothes'] ?? 0;
    $comforter_price_per_kg = $pricing['comforter_curtains'] ?? 0;

    // ✅ 3. Compute totals
    $clothes_total = $clothes_weight * $clothes_price_per_kg;
    $comforter_total = $comforter_curtains_weight * $comforter_price_per_kg;
    $grand_total = $clothes_total + $comforter_total;

    // ✅ 4. Update CLOTHES items (category = 'Clothing')
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

    // ✅ 5. Update COMFORTER/CURTAINS items (category = 'Others', product_name IN (...))
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

    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
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
