<?php
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/Rider.php';

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fullname = $_POST['fullname'] ?? '';
        $phone_number = $_POST['phone_number'] ?? '';
        $address = $_POST['address'] ?? '';
        $rider_id = $_POST['rider_id'] ?? null;

        // Find the rider
        $rider = Rider::find($rider_id);
        if (!$rider) {
            header('Location: ManageRiders.php?error=' . urlencode('Rider not found'));
            exit();
        }

        $success = $rider->update([
            'fullname' => $fullname,
            'phone_number' => $phone_number,
            'address' => $address
        ]);

        if ($success) {
            header('Location: ManageRiders.php?success=' . urlencode('Rider updated successfully!'));
            exit();
        } else {
            header('Location: Edit.php?id=' . $rider_id . '&error=' . urlencode('Failed to update rider'));
            exit();
        }
    } else {
        header('Location: ManageRiders.php');
        exit();
    }
?>