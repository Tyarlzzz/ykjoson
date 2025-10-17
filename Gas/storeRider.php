<?php
    require_once '../database/Database.php';
    require_once '../Models/Rider.php';
    include '../layout/header.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $database = new Database();
            $db = $database->getConnection();
            Rider::setConnection($db);

            $sql = "INSERT INTO riders (fullname, address, phone_number, petty_cash, created_at)
                    VALUES (:fullname, :address, :phone_number, :petty_cash, NOW())";
            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':fullname' => $_POST['fullname'],
                ':address' => $_POST['address'],
                ':phone_number' => $_POST['phone_number'],
                ':petty_cash' => 0
            ]);

            echo '
                <script>
                    Swal.fire({
                        title: "Good job!",
                        text: "Rider successfully added!",
                        icon: "success"
                    }).then(() => {
                        window.location = "manageRiders.php";
                    });
                </script>';

        } catch (Exception $e) {
            echo '
                <script>
                    Swal.fire({
                        title: "Error!",
                        text: "' . addslashes($e->getMessage()) . '",
                        icon: "error"
                    }).then(() => {
                        window.location = "manageRiders.php";
                    });
                </script>';
        }
    }

    include '../layout/footer.php';
?>