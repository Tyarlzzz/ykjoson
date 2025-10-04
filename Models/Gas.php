<?php
    require_once 'models.php';

    class Gas extends Model{
        public static $table = 'customer';

        public $customer_id;
        public $fullname;
        public $address;
        public $phone_number;

        public function __construct(array $data = []){
            foreach($data as $key => $value){
                if(property_exists($this, $key)){
                    $this->$key = $value;
                }
            }
        }

        public static function all () {
            $result = parent::all();

            return $result ? array_map(fn($data) => new self($data), $result) : null;
        }

        public static function find ($id) {
            $result = parent::find($id);

            return $result ? new self($result) : null;
        }

        public static function create (array $data) {
            $result = parent::create($data);
            return $result ? new self($result) : null;
        }

        public function update(array $data) {
            $result = parent::updateById($this->customer_id, $data);

            if($result) {
                foreach($data as $key => $value) {
                    if(property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        public function save () {
            $data = [
                'curtomer_id' => $this->customer_id,
                'fullname' => $this->fullname,
                'address' => $this->address,
                'phone_number' => $this->phone_number,
            ];

            if (isset($this->id)) {
                return $this->update($data);
            } else {
                return Gas::create($data);
            }
        }

        public function delete () {
            $result = parent::deleteById($this->customer_id);

            if($result) {
                foreach($this as $key => $value) {
                    unset($this->$key);
                }
                return true;
            } else {
                return false;
            }
        }

        public static function count() {
            $query = "SELECT COUNT(*) as count FROM " . static::$table;
            $stmt = self::$conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['count'];
        }

        public static function getNewUsersCount() {
            $date = date('Y-m-d H:i:s', strtotime("-24 hours"));
            $query = "SELECT COUNT(*) as count FROM " . static::$table . " WHERE created_at >= :date";
            $stmt = self::$conn->prepare($query);
            $stmt->bindParam(':date', $date);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['count'];
        }
    }
?>
