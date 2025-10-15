<?php


    require_once 'Models.php';

    class User extends Model{
        protected static $table = "users"; //edit based sa pangalan ng table sa database

        //mga gusto maretrieve sa users na table, kung ano pangalan ng column sa users na table, dapat EXACTLY pareho sa column name sa table
        public $user_id;
        public $first_name;
         public $last_name;
        public $email;
        public $password;
        public $role;
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
            $result = parent::updateByID($this->user_id, $data);

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
                "first_name" => $this->first_name,
                "last_name" => $this->last_name,
                "email" => $this->email,
                "password" => $this->password,
                "role" => $this->role,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];

            $this->update($data);
        }
        public function delete(){
            $result = parent::deleteByID($this->user_id);
            
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