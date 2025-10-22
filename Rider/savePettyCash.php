<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/Database.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data['rider_id'], $data['petty_cash'])) {
    echo json_encode(['success' => false, 'message' => 'Missing fields']);
    exit;
}

$rider_id = intval($data['rider_id']);
$petty_cash = floatval($data['petty_cash']);
$action = $data['action'] ?? 'add'; // "add" or "clear"

try {
    $db = new Database();
    $conn = $db->getConnection();

    // ✅ Check if rider exists
    $check = $conn->prepare("SELECT petty_cash FROM riders WHERE rider_id = :rider_id");
    $check->execute([':rider_id' => $rider_id]);
    $rider = $check->fetch(PDO::FETCH_ASSOC);

    if (!$rider) {
        echo json_encode(['success' => false, 'message' => 'Rider not found']);
        exit;
    }

    $current_petty = floatval($rider['petty_cash']);
    $new_petty = $current_petty;

    if ($action === 'add') {
        $new_petty += $petty_cash;
    } elseif ($action === 'clear') {
        $new_petty = 0;
    }

    // ✅ Update petty cash
    $stmt = $conn->prepare("
        UPDATE riders
        SET petty_cash = :new_petty
        WHERE rider_id = :rider_id
    ");
    $stmt->execute([
        ':new_petty' => $new_petty,
        ':rider_id' => $rider_id
    ]);

    // ✅ Compute today's total sales (Gas + Laundry)
    $today = date('Y-m-d');

    // GAS
    $stmt_gas = $conn->prepare("
        SELECT IFNULL(SUM(goi.total), 0)
        FROM gas_ordered_items goi
        INNER JOIN orders o ON o.order_id = goi.order_id
        WHERE DATE(o.order_date) = :today
    ");
    $stmt_gas->execute([':today' => $today]);
    $gas_sales = (float) $stmt_gas->fetchColumn();

    // LAUNDRY
    $stmt_laundry = $conn->prepare("
        SELECT IFNULL(SUM(loi.total), 0)
        FROM laundry_ordered_items loi
        INNER JOIN orders o ON o.order_id = loi.order_id
        WHERE DATE(o.order_date) = :today
    ");
    $stmt_laundry->execute([':today' => $today]);
    $laundry_sales = (float) $stmt_laundry->fetchColumn();

    $total_sales = $gas_sales + $laundry_sales;

    // ✅ Get total petty cash from ALL riders
    $stmt_all_petty = $conn->query("SELECT IFNULL(SUM(petty_cash), 0) AS total_petty FROM riders");
    $total_petty_all = (float) $stmt_all_petty->fetchColumn();

    // ✅ Combine for grand total display
    $total_amount = $total_petty_all + $total_sales;


    echo json_encode([
        'success' => true,
        'message' => $action === 'clear'
            ? 'Petty cash cleared successfully'
            : 'Petty cash updated successfully',
        'previous_amount' => $current_petty,
        'new_amount' => $new_petty,
        'total_sales' => $total_sales,
        'total_amount' => $total_amount
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
