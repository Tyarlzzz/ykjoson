<?php 
   require_once '../db\db_connect.php';
   require_once '../models/Transaction.php';


include '../layout/header.php';

   $db = new Database();
   $conn = $db->getConnection();
   Transaction::setConnection($conn);

    $data = [];

        $data = [
            "order_id" => $this->order_id,
                "customer_id" => $this->customer_id,
                "user_id" => $this->user_id,
                "transaction_date" => $this->transaction_date,
                "payment_status" => $this->payment_status,
                "credit" => $this->credit,
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