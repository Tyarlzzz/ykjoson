<?php


    require_once 'Models.php';

    class Assigned_Delivery extends Model{
        protected static $table = "assigned_deliveries"; //edit based sa pangalan ng table sa database

        //mga gusto maretrieve sa users na table, kung ano pangalan ng column sa users na table, dapat EXACTLY pareho sa column name sa table
        public $delivery_id;
        public $order_id;
         public $delivered_by;
        public $assigned_at;
        public $delivered_at;
        public $status;
        public $remarks;
        public $created_at;
        public $updated_at;

        public function __construct(array $data = []){
            foreach ($data as $key => $value){
                if (property_exists($this, $key)){
                    $this->$key = $value;
                }
            }
        }

        public static function all(){
            $result = parent::all(); //naovverride na ni user table si table
            return $result
            ? array_map(fn ($data) => new self ($data), $result) //to change associative array into object
            : null;
        }

        public static function find($id){
            $result = parent::find($id);
            return $result ? new self($result) : null; //hindi naman kasi buong array yung laman ng result, isang row lang
        }

        public static function create(array $data){
            $result = parent::create($data);

            return $result ? new self($result) : null;
        }

        //now magccreate na ng nonstatic na function, need muna na may existing na object para matawaag

        public function update(array $data){
            $result = parent::updateByID($this->delivery_id, $data);

            if ($result){
                foreach ($data as $key => $value){
                    if (property_exists($this, $key)){
                        $this->$key = $value;
                    }
                }
                return true;
            } else {
                return false;
            }
        }
//yung object itself ay merong id 

        public function save(){
            $data = [
                "order_id" => $this->order_id,
                "delivered_by" => $this->delivered_by,
                "assigned_at" => $this->assigned_at,
                "delivered_at" => $this->delivered_at,
                "status" => $this->status,
                "remarks" => $this->remarks,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];

            $this->update($data);
        }
        public function delete(){
            $result = parent::deleteByID($this->delivery_id);
            
            if ($result){
                foreach ($this as $key => $value){
                    unset($this->$key);
                }
                return true;
            } else {
                return false;
            }
        }

        public static function where($column, $operation, $value){
            $result = parent::where($column, $operation, $value);
    
            return $result
                ? array_map(fn($data) => new self($data), $result)
                : null;
        }
        
    }   



?>