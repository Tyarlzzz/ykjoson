<?php 
   require_once '../db\db_connect.php';
   require_once '../models/Ordered_Item.php';


include '../layout/header.php';

   $db = new Database();
   $conn = $db->getConnection();
   Ordered_item::setConnection($conn);

    $data = [];

        $data = [
            "product_code_id" => $this->product_code_id,    
            "order_id" => $this->order_id,
            "allotment_id" => $this->allotment_id,
            "weight_quantity" => $this->weight_quantity,
            "total" => $this->total,
        ];

        $create = User::create($data);

        if($create){
            header("Location: index.php");
        } else {
            echo "Not created";
            header("Location: index.php");
        }
    

?>

<?php include '../../layout/footer.php'; ?>