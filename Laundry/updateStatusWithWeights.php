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

    // Step 1: Get order info (is_rushed)
    $orderStmt = $conn->prepare("SELECT is_rushed FROM orders WHERE order_id = :order_id");
    $orderStmt->execute([':order_id' => $order_id]);
    $order = $orderStmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    $is_rushed = (int)$order['is_rushed'];

    // Step 2: Update order status
    $updateOrder = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
    $updateOrder->execute([':status' => $status, ':order_id' => $order_id]);

    // Step 3: Get ALL pricing from database (including barong and gown)
    $pricingStmt = $conn->query("
        SELECT item_type, pricing_type, standard_price, rush_price, minimum_weight, flat_rate_standard, flat_rate_rush 
        FROM laundry_pricing
    ");
    $pricing = [];
    while ($row = $pricingStmt->fetch()) {
        $pricing[$row['item_type']] = $row;
    }

    // Step 4: Calculate clothes price
    $clothes_price_per_kg = $is_rushed 
        ? $pricing['standard_clothes']['rush_price'] 
        : $pricing['standard_clothes']['standard_price'];

    $min_weight = $pricing['standard_clothes']['minimum_weight'];
    $flat_rate_standard = $pricing['standard_clothes']['flat_rate_standard'];
    $flat_rate_rush = $pricing['standard_clothes']['flat_rate_rush'];

    if ($clothes_weight < $min_weight && $min_weight > 0) {
        $clothes_total = $is_rushed ? $flat_rate_rush : $flat_rate_standard;
    } else {
        $clothes_total = $clothes_weight * $clothes_price_per_kg;
    }

    // Step 5: Calculate comforter/curtains price
    $comforter_price_per_kg = $is_rushed 
        ? $pricing['comforter_curtains']['rush_price'] 
        : $pricing['comforter_curtains']['standard_price'];

    $comforter_total = $comforter_curtains_weight * $comforter_price_per_kg;

    // Step 6: Get Barong and Gown prices from database
    $barong_price = $is_rushed 
        ? $pricing['barong']['rush_price'] 
        : $pricing['barong']['standard_price'];
    
    $gown_price = $is_rushed 
        ? $pricing['gown']['rush_price'] 
        : $pricing['gown']['standard_price'];

    // Step 7: Get Barong and Gown quantities from order
    $barongGownStmt = $conn->prepare("
        SELECT pc.product_name, loi.quantity
        FROM laundry_ordered_items loi
        JOIN `product codes` pc ON loi.product_code_id = pc.code_id
        WHERE loi.order_id = :order_id
        AND pc.product_name IN ('Barong', 'Gown')
    ");
    $barongGownStmt->execute([':order_id' => $order_id]);
    $barongGownItems = $barongGownStmt->fetchAll();

    $barong_qty = 0;
    $gown_qty = 0;
    $barong_total = 0;
    $gown_total = 0;

    foreach ($barongGownItems as $item) {
        if ($item['product_name'] === 'Barong') {
            $barong_qty = intval($item['quantity']);
            $barong_total = $barong_qty * $barong_price;
        } elseif ($item['product_name'] === 'Gown') {
            $gown_qty = intval($item['quantity']);
            $gown_total = $gown_qty * $gown_price;
        }
    }

    // Step 8: Calculate grand total
    $grand_total = $clothes_total + $comforter_total + $barong_total + $gown_total;

    // Step 9: Update CLOTHES items (excluding Barong and Gown)
    $updateClothes = $conn->prepare("
        UPDATE laundry_ordered_items
        SET weight_kg = :weight_kg,
            price_per_kg = :price_per_kg,
            total = :total
        WHERE order_id = :order_id
        AND product_code_id IN (
            SELECT code_id FROM `product codes`
            WHERE business_type = 'Laundry System' 
            AND category = 'Clothing'
            AND product_name NOT IN ('Barong', 'Gown')
        )
    ");
    $updateClothes->execute([
        ':weight_kg' => $clothes_weight,
        ':price_per_kg' => $clothes_price_per_kg, 
        ':total' => $clothes_total,
        ':order_id' => $order_id
    ]);

    // Step 10: Update COMFORTER/CURTAINS items
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

    // Step 11: Update BARONG items (per piece pricing from database)
    if ($barong_qty > 0) {
        $updateBarong = $conn->prepare("
            UPDATE laundry_ordered_items
            SET weight_kg = 0,
                price_per_kg = :price_per_piece,
                total = :total
            WHERE order_id = :order_id
            AND product_code_id IN (
                SELECT code_id FROM `product codes`
                WHERE business_type = 'Laundry System'
                AND product_name = 'Barong'
            )
        ");
        $updateBarong->execute([
            ':price_per_piece' => $barong_price,
            ':total' => $barong_total,
            ':order_id' => $order_id
        ]);
    }

    // Step 12: Update GOWN items (per piece pricing from database)
    if ($gown_qty > 0) {
        $updateGown = $conn->prepare("
            UPDATE laundry_ordered_items
            SET weight_kg = 0,
                price_per_kg = :price_per_piece,
                total = :total
            WHERE order_id = :order_id
            AND product_code_id IN (
                SELECT code_id FROM `product codes`
                WHERE business_type = 'Laundry System'
                AND product_name = 'Gown'
            )
        ");
        $updateGown->execute([
            ':price_per_piece' => $gown_price,
            ':total' => $gown_total,
            ':order_id' => $order_id
        ]);
    }

    // Step 13: Return JSON with detailed breakdown
    echo json_encode([
        'success' => true,
        'message' => 'Order updated successfully',
        'is_rushed' => $is_rushed,
        'pricing_used' => $is_rushed ? 'Rush' : 'Standard',
        'total_price' => $grand_total,
        'breakdown' => [
            'clothes_total' => $clothes_total,
            'comforter_total' => $comforter_total,
            'barong_qty' => $barong_qty,
            'barong_price' => $barong_price,
            'barong_total' => $barong_total,
            'gown_qty' => $gown_qty,
            'gown_price' => $gown_price,
            'gown_total' => $gown_total,
            'grand_total' => $grand_total
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
