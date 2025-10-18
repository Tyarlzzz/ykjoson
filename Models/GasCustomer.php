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
                'customer_id' => $this->customer_id,
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

        public static function getTodaysOrders() {
            try {
                $today = date('Y-m-d');
                
                $sql = "SELECT 
                            o.order_id,
                            o.status,
                            o.created_at,
                            c.fullname,
                            c.address,
                            c.phone_number,
                            GROUP_CONCAT(DISTINCT ii.item_name ORDER BY ii.item_name SEPARATOR ', ') AS brands,
                            COALESCE(SUM(goi.quantity), 0) AS total_quantity
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN gas_ordered_items goi ON o.order_id = goi.order_id
                        LEFT JOIN `item allotment` ia ON goi.allotment_id = ia.allotment_id
                        LEFT JOIN `item inventory` ii ON ia.item_id = ii.item_id
                        WHERE o.business_type = 'Gas System'
                        AND DATE(o.created_at) = :today
                        GROUP BY 
                            o.order_id, 
                            o.status, 
                            o.created_at, 
                            c.fullname, 
                            c.address, 
                            c.phone_number
                        ORDER BY o.created_at DESC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':today', $today);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 ? $results : null;
                
            } catch (PDOException $e) {
                die("Error fetching today's gas orders: " . $e->getMessage());
            }
        }
    }
?>