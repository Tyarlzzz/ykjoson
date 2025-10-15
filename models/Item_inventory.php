<?php
    require_once 'Models.php';

    class Item_inventory extends Model{
        protected static $table = "item inventory";

        public $item_id;
        public $business_type;
        public $item_name;
        public $stocks;
        public $cost;
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
            $result = parent::all();
            return $result
            ? array_map(fn ($data) => new self ($data), $result)
            : null;
        }

        public static function find($id){
            $result = parent::find($id);
            return $result ? new self($result) : null;
        }

        public static function create(array $data){
            $result = parent::create($data);
            return $result ? new self($result) : null;
        }

        public function update(array $data){
            $result = parent::updateByID($this->item_id, $data);

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

        public function save(){
            $data = [
                "business_type" => $this->business_type,
                "item_name" => $this->item_name,
                "stocks" => $this->stocks,
                "cost" => $this->cost,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];

            $this->update($data);
        }

        public function delete(){
            $result = parent::deleteByID($this->item_id);
            
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

        public static function getGasInventory() {
            try {
                $sql = "SELECT * FROM `item inventory` 
                        WHERE business_type = 'Gas System' 
                        ORDER BY item_name ASC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return $results ? array_map(fn($data) => new self($data), $results) : null;
                
            } catch (PDOException $e) {
                die("Error fetching gas inventory: " . $e->getMessage());
            }
        }

        public static function getByItemName($itemName) {
            try {
                $sql = "SELECT * FROM `item inventory` 
                        WHERE LOWER(item_name) = LOWER(:item_name) 
                        AND business_type = 'Gas System'
                        LIMIT 1";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute([':item_name' => $itemName]);
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return $result ? new self($result) : null;
                
            } catch (PDOException $e) {
                die("Error fetching item: " . $e->getMessage());
            }
        }

        public static function updateStock($item_id, $new_stock) {
            try {
                $sql = "UPDATE `item inventory` 
                        SET stocks = :stocks, 
                            updated_at = NOW() 
                        WHERE item_id = :item_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':stocks' => $new_stock,
                    ':item_id' => $item_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating stock: " . $e->getMessage());
            }
        }

        public static function updatePrice($item_id, $new_price) {
            try {
                $sql = "UPDATE `item inventory` 
                        SET cost = :cost, 
                            updated_at = NOW() 
                        WHERE item_id = :item_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':cost' => $new_price,
                    ':item_id' => $item_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating price: " . $e->getMessage());
            }
        }

        public static function updateStockAndPrice($item_id, $new_stock, $new_price) {
            try {
                $sql = "UPDATE `item inventory` 
                        SET stocks = :stocks, 
                            cost = :cost, 
                            updated_at = NOW() 
                        WHERE item_id = :item_id";
                
                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':stocks' => $new_stock,
                    ':cost' => $new_price,
                    ':item_id' => $item_id
                ]);
                
            } catch (PDOException $e) {
                die("Error updating inventory: " . $e->getMessage());
            }
        }

        public static function getLowStockItems($threshold = 50) {
            try {
                $sql = "SELECT * FROM `item inventory` 
                        WHERE business_type = 'Gas System' 
                        AND stocks < :threshold 
                        ORDER BY stocks ASC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->execute([':threshold' => $threshold]);
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return $results ? array_map(fn($data) => new self($data), $results) : null;
                
            } catch (PDOException $e) {
                die("Error fetching low stock items: " . $e->getMessage());
            }
        }

        public function isLowStock($threshold = 50) {
            return $this->stocks < $threshold;
        }
    }   
?>