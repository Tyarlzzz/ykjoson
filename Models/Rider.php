<?php
require_once 'Models.php';

class Rider extends Model {
    protected static $table = 'riders';

    public $rider_id;
    public $fullname;
    public $address;
    public $phone_number;
    public $petty_cash;
    public $created_at;
    public $updated_at;

    public function __construct(array $data = []) {
        foreach($data as $key => $value) {
            if(property_exists($this, $key)) {
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
        $result = parent::updateById($this->rider_id, $data);

        if($result) {
            foreach($data as $key => $value) {
                if(property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        return false;
    }

    public function save() {
        $data = [
            'fullname' => $this->fullname,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'petty_cash' => $this->petty_cash
        ];

        if (isset($this->rider_id)) {
            return $this->update($data);
        } else {
            $riders = self::create($data);
            if ($riders) {
                $this->rider_id = $riders->rider_id;
                return true;
            }
            return false;
        }
    }

    public function delete() {
        $result = parent::deleteById($this->rider_id);

        if($result) {
            foreach($this as $key => $value) {
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

    public static function deleteByRiderId($id) {
        try {
            $sql = "DELETE FROM " . static::$table . " WHERE rider_id = :rider_id";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':rider_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die("Error deleting rider: " . $e->getMessage());
        }
    }

}
?>