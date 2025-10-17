<?php
    require_once '../layout/header.php';
    require_once '../database/Database.php';
    require_once '../Models/Rider.php';

    $database = new Database();
    $conn = $database->getConnection();
    Rider::setConnection($conn);
?>

<?php
    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        if (Rider::deleteByRiderId($id)) {
            echo '<script>
                    Swal.fire({
                        title: "Deleted!",
                        text: "Rider has been successfully deleted.",
                        icon: "success",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location = "manageRiders.php";
                    });
                </script>';
        } else {
            echo '<script>
                    Swal.fire({
                        title: "Error!",
                        text: "Failed to delete rider.",
                        icon: "error",
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "OK"
                    }).then(() => {
                        window.location = "manageRiders.php";
                    });
                </script>';
        }
    } else {
        echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "No rider ID provided.",
                    icon: "error",
                    confirmButtonColor: "#3085d6",
                    confirmButtonText: "OK"
                }).then(() => {
                    window.location = "manageRiders.php";
                });
            </script>';
    }
?>