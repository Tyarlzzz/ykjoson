<?php
    require_once '../database/Database.php';
    require_once '../Models/Rider.php';
    include '../layout/header.php';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        try {

            $database = new Database();
            $db = $database->getConnection();
            Rider::setConnection($db);

            $db->beginTransaction();

            $riderData = [
                'fullname' => $_POST['fullname'],
                'address' => $_POST['address'],
                'phone_number' => $_POST['phone_number']
            ];

            $custSql = "INSERT INTO riders (fullname, address, phone_number, created_at) 
                        VALUES (:fullname, :address, :phone_number, NOW())";
            $custStmt = $db->prepare($custSql);
            $custStmt->execute($riderData);
            
            $db->commit();
   
            echo '<script>
                    Swal.fire({
                        title: "Good job!",
                        text: "You Successfully Created an Order!",
                        icon: "success"
                    }).then(function() {
                        window.location = "index.php";
                    });
                </script>';

        } catch (Exception $e) {
            
            $db->rollBack();
            
            echo '<script>
                    Swal.fire({
                        title: "Error!",
                        text: "' . addslashes($e->getMessage()) . '",
                        icon: "error"
                    }).then(function() {
                        window.location = "create.php";
                    });
                </script>';
        }
    }

    include '../layout/footer.php';
?>