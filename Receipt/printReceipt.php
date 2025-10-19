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

// Configuration
$comPort = "COM9"; // Change this to your Bluetooth COM port

// ESC/POS Commands
define('ESC', "\x1B");
define('GS', "\x1D");

// Initialize
$ESC_INIT = ESC . "@";
$ESC_ALIGN_CENTER = ESC . "a" . chr(1);
$ESC_ALIGN_LEFT = ESC . "a" . chr(0);
$ESC_ALIGN_RIGHT = ESC . "a" . chr(2);
$ESC_BOLD_ON = ESC . "E" . chr(1);
$ESC_BOLD_OFF = ESC . "E" . chr(0);
$ESC_SIZE_NORMAL = GS . "!" . chr(0);
$ESC_SIZE_DOUBLE = GS . "!" . chr(17);
$ESC_CUT = GS . "V" . chr(66) . chr(0);

try {
    $database = new Database();
    $db = $database->getConnection();

    // Fetch order details INCLUDING total_price
    $orderSql = "SELECT o.*, c.fullname, c.address, c.phone_number
                 FROM orders o
                 JOIN customer c ON o.customer_id = c.customer_id
                 WHERE o.order_id = :order_id";
    $orderStmt = $db->prepare($orderSql);
    $orderStmt->execute([':order_id' => $order_id]);
    $order = $orderStmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }

    // Fetch order items
    $itemsSql = "SELECT loi.*, pc.product_name, pc.category
                 FROM laundry_ordered_items loi
                 LEFT JOIN `product codes` pc ON loi.product_code_id = pc.code_id
                 WHERE loi.order_id = :order_id
                 ORDER BY pc.category, pc.product_name";
    $itemsStmt = $db->prepare($itemsSql);
    $itemsStmt->execute([':order_id' => $order_id]);
    $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals from items (for quantity and weight display)
    $totalQty = 0;
    $totalWeight = 0;

    foreach ($items as $item) {
        $totalQty += intval($item['quantity'] ?? 0);
        $totalWeight += floatval($item['weight_kg'] ?? 0);
    }

    // Use total_price from database (already calculated and stored)
    $totalAmount = floatval($order['total_price'] ?? 0);

    // Open COM port
    $fp = @fopen($comPort, "w");
    
    if (!$fp) {
        echo json_encode([
            'success' => false, 
            'message' => "Cannot open printer on port $comPort. Please check printer connection."
        ]);
        exit;
    }

    // Build receipt content
    $receipt = "";
    
    // Initialize printer
    $receipt .= $ESC_INIT;
    
    // Header - Center aligned
    $receipt .= $ESC_ALIGN_CENTER;
    $receipt .= $ESC_SIZE_DOUBLE;
    $receipt .= "YK Joson\n";
    $receipt .= $ESC_SIZE_NORMAL;
    $receipt .= "Laundry and Gasul Services\n";
    $receipt .= "\n";
    $receipt .= "================================\n";
    $receipt .= "\n";
    
    // Date - Left aligned
    $receipt .= $ESC_ALIGN_LEFT;
    $orderDate = date('m/d/Y', strtotime($order['order_date']));
    $receipt .= "Date: " . $orderDate . "\n";
    $receipt .= "--------------------------------\n";
    
    // Customer Info
    $receipt .= "Name: " . $order['fullname'] . "\n";
    $receipt .= "Phone: " . ($order['phone_number'] ?: 'N/A') . "\n";
    $receipt .= "Address: " . ($order['address'] ?: 'N/A') . "\n";
    $receipt .= "--------------------------------\n";
    
    // Rushed order checkbox
    $rushedCheck = $order['is_rushed'] ? "[X]" : "[ ]";
    $receipt .= $rushedCheck . " Rushed Order\n";
    $receipt .= "\n";
    
    // Rider name (hardcoded)
    $receipt .= "Rider: Rommel Santos\n";
    $receipt .= "--------------------------------\n";
    
    // Items header
    $receipt .= "Item Name                    Qty\n";
    $receipt .= "--------------------------------\n";
    
    // Print items
    foreach ($items as $item) {
        $name = substr($item['product_name'] ?? 'Item', 0, 25);
        $namePart = str_pad($name, 25);
        $qtyPart = str_pad($item['quantity'] ?? '0', 3, ' ', STR_PAD_LEFT);
        
        $receipt .= $namePart . $qtyPart . "\n";
    }
    
    $receipt .= "--------------------------------\n";
    
    // Totals
    $receipt .= "Total Qty:" . str_pad($totalQty, 20, ' ', STR_PAD_LEFT) . "\n";
    $receipt .= "Total Weight:" . str_pad(number_format($totalWeight, 2) . " kg", 17, ' ', STR_PAD_LEFT) . "\n";
    
    $receipt .= "================================\n";
    
    // TOTAL (large) - FROM DATABASE
    $receipt .= $ESC_ALIGN_CENTER;
    $receipt .= $ESC_SIZE_DOUBLE;
    $receipt .= "TOTAL: P" . number_format($totalAmount, 2) . "\n";
    $receipt .= $ESC_SIZE_NORMAL;
    $receipt .= "================================\n";
    $receipt .= "\n";
    
    // Note (if exists)
    if (!empty($order['note'])) {
        $receipt .= $ESC_ALIGN_LEFT;
        $receipt .= "Note:\n";
        $receipt .= $order['note'] . "\n";
        $receipt .= "\n";
    }
    
    // Footer - Center aligned
    $receipt .= $ESC_ALIGN_CENTER;
    $receipt .= "Thank You!\n";
    $receipt .= "\n";
    $receipt .= "================================\n";
    $receipt .= "\n\n\n";
    
    // Cut paper
    $receipt .= $ESC_CUT;
    
    // Send to printer
    fwrite($fp, $receipt);
    fclose($fp);
    
    echo json_encode([
        'success' => true,
        'message' => 'Receipt printed successfully!',
        'order_id' => $order_id,
        'total_amount' => $totalAmount,
        'total_weight' => $totalWeight
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>