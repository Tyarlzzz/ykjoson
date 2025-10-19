<?php
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

ob_clean();
header('Content-Type: application/json');

$data = $_POST ?? json_decode(file_get_contents('php://input'), true) ?? [];

echo json_encode([
    'success' => true,
    'received_data' => $data,
    'method' => $_SERVER['REQUEST_METHOD'],
    'test' => 'This is a test'
]);

ob_end_flush();
exit;
?>
