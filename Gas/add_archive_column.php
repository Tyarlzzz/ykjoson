<?php
require_once __DIR__ . '/../database/Database.php';

// Add archive_at column to orders table if it doesn't exist
$db = new Database();
$conn = $db->getConnection();

// Convert PDO to mysqli for ALTER TABLE
$mysqli = new mysqli('127.0.0.1', 'root', 'Gwenadam1217.', 'ykjoson');

// For MySQL versions that don't support IF NOT EXISTS, check first
$checkColumn = "SHOW COLUMNS FROM orders LIKE 'archive_at'";
$result = $mysqli->query($checkColumn);

if ($result->num_rows == 0) {
    $sql = "ALTER TABLE orders ADD COLUMN archive_at DATETIME NULL AFTER paid_at";
    if ($mysqli->query($sql)) {
        echo "SUCCESS: archive_at column added\n";
    } else {
        echo "ERROR: " . $mysqli->error . "\n";
    }
} else {
    echo "INFO: archive_at column already exists\n";
}
