<?php 
   require_once '../db\db_connect.php';
   require_once '../models/Customer.php';


include '../layout/header.php';

   $db = new Database();
   $conn = $db->getConnection();
   Customer::setConnection($conn);

    $data = [];

        $data = [
               "first_name" => $this->first_name,
                "last_name" => $this->last_name,
                "address" => $this->address,
                "phone_number" => $this->phone_number,
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