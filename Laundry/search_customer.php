<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';

// Setup DB connection
$database = new Database();
$db = $database->getConnection();
Model::setConnection($db);

// Always show errors while debugging
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (isset($_GET['term'])) {
    $term = trim($_GET['term']);

    // Prepare query
    $stmt = $db->prepare("
        SELECT customer_id, fullname, phone_number, address
        FROM customer
        WHERE fullname LIKE ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute(["%$term%"]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}

// If no term provided
echo json_encode([]);
