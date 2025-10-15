<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/Item_inventory.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $conn = $database->getConnection();
        Model::setConnection($conn);

        $brand = $_POST['brand'];
        $stock = intval($_POST['stock']);
        $price = floatval($_POST['price']);

        if (empty($brand) || $stock < 0 || $price < 0) {
            throw new Exception('Invalid input data');
        }

        $item = Item_inventory::getByItemName($brand);

        if (!$item) {
            throw new Exception('Item not found');
        }

        $success = Item_inventory::updateStockAndPrice($item->item_id, $stock, $price);

        if ($success) {
            echo '<script>
                    Swal.fire({
                        title: "Success!",
                        text: "' . ucfirst($brand) . ' inventory updated successfully!",
                        icon: "success"
                    }).then(function() {
                        window.location = "updateInventory.php";
                    });
                </script>';
        } else {
            throw new Exception('Failed to update inventory');
        }

    } catch (Exception $e) {
        echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "' . addslashes($e->getMessage()) . '",
                    icon: "error"
                }).then(function() {
                    window.location = "updateInventory.php";
                });
            </script>';
    }
}
?>