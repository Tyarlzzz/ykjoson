<?php
    require_once 'Models.php';

    class laundry_pricing extends Model {
        protected static $table = "laundry_pricing";

        public $pricing_id;
        public $item_type;
        public $pricing_type;
        public $standard_price;
        public $rush_price;
        public $minimum_weight;
        public $flat_rate_standard;
        public $flat_rate_rush;
        public $created_at;
        public $updated_at;

        public function __construct(array $data = []) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        public static function all() {
            $result = parent::all();
            return $result ? array_map(fn($data) => new self($data), $result) : null;
        }

        public static function find($id) {
            $result = parent::find($id);
            return $result ? new self($result) : null;
        }

        public static function create(array $data) {
            $result = parent::create($data);
            return $result ? new self($result) : null;
        }

        public function update(array $data) {
            $result = parent::updateByID($this->pricing_id, $data);
            if ($result) {
                foreach ($data as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            }
            return false;
        }

        public function save() {
            $data = [
                "item_type" => $this->item_type,
                "pricing_type" => $this->pricing_type,
                "standard_price" => $this->standard_price,
                "rush_price" => $this->rush_price,
                "minimum_weight" => $this->minimum_weight,
                "flat_rate_standard" => $this->flat_rate_standard,
                "flat_rate_rush" => $this->flat_rate_rush,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ];

            $this->update($data);
        }

        public function delete() {
            $result = parent::deleteByID($this->pricing_id);
            if ($result) {
                foreach ($this as $key => $value) {
                    unset($this->$key);
                }
                return true;
            }
            return false;
        }

        public static function where($column, $operation, $value) {
            $result = parent::where($column, $operation, $value);
            return $result ? array_map(fn($data) => new self($data), $result) : null;
        }

        public static function getLaundryPricing() {
            try {
                $sql = "SELECT * FROM `laundry_pricing` ORDER BY item_type ASC";
                $stmt = self::$conn->prepare($sql);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $results ? array_map(fn($data) => new self($data), $results) : null;
            } catch (PDOException $e) {
                die("Error fetching laundry pricing: " . $e->getMessage());
            }
        }

        public static function getByItemType($itemType) {
            try {
                $sql = "SELECT * FROM `laundry_pricing` WHERE LOWER(item_type) = LOWER(:item_type) LIMIT 1";
                $stmt = self::$conn->prepare($sql);
                $stmt->execute([':item_type' => $itemType]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result ? new self($result) : null;
            } catch (PDOException $e) {
                die("Error fetching item: " . $e->getMessage());
            }
        }

        public static function updatePrice($pricing_id, $new_price, $price_column = 'standard_price') {
            try {
                // naka include lang dito ung minimum weight
                $allowed_columns = ['standard_price', 'rush_price', 'flat_rate_standard', 'flat_rate_rush', 'minimum_weight'];

                if (!in_array($price_column, $allowed_columns)) {
                    throw new Exception("Invalid price column specified");
                }

                $sql = "UPDATE `laundry_pricing`
                        SET {$price_column} = :new_price,
                            updated_at = NOW()
                        WHERE pricing_id = :pricing_id";

                $stmt = self::$conn->prepare($sql);
                return $stmt->execute([
                    ':new_price' => $new_price,
                    ':pricing_id' => $pricing_id
                ]);
            } catch (PDOException $e) {
                die("Error updating price: " . $e->getMessage());
            }
        }
    }
?>