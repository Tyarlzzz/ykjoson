<?php
require_once 'Models.php';

class Rider extends Model {
    protected static $table = 'riders';
    protected static $primaryKey = 'rider_id'; // Define the primary key

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
        try {
            $sql = "SELECT * FROM " . static::$table;
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ? array_map(fn($data) => new self($data), $results) : null;
        } catch (PDOException $e) {
            die("Error fetching riders: " . $e->getMessage());
        }
    }

    public static function find($id) {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE rider_id = :rider_id LIMIT 1";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':rider_id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? new self($result) : null;
        } catch (PDOException $e) {
            die("Error finding rider: " . $e->getMessage());
        }
    }

    public static function create(array $data) {
        try {
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            
            $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
            $stmt = self::$conn->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $lastId = self::$conn->lastInsertId();
            
            return self::find($lastId);
        } catch (PDOException $e) {
            die("Error creating rider: " . $e->getMessage());
        }
    }

    public function update(array $data) {
        try {
            $setParts = [];
            foreach ($data as $key => $value) {
                $setParts[] = "$key = :$key";
            }
            $setClause = implode(', ', $setParts);
            
            $sql = "UPDATE " . static::$table . " SET $setClause WHERE rider_id = :rider_id";
            $stmt = self::$conn->prepare($sql);
            
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':rider_id', $this->rider_id, PDO::PARAM_INT);
            
            $result = $stmt->execute();
            
            if($result) {
                foreach($data as $key => $value) {
                    if(property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            die("Error updating rider: " . $e->getMessage());
        }
    }

    public function save() {
        $data = [
            'fullname' => $this->fullname,
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'petty_cash' => $this->petty_cash ?? 0
        ];

        if (isset($this->rider_id)) {
            return $this->update($data);
        } else {
            $rider = self::create($data);
            if ($rider) {
                $this->rider_id = $rider->rider_id;
                return true;
            }
            return false;
        }
    }

    public function delete() {
        try {
            $sql = "DELETE FROM " . static::$table . " WHERE rider_id = :rider_id";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':rider_id', $this->rider_id, PDO::PARAM_INT);
            $result = $stmt->execute();
            
            if($result) {
                foreach($this as $key => $value) {
                    unset($this->$key);
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            die("Error deleting rider: " . $e->getMessage());
        }
    }

    public static function where($column, $operation, $value) {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE $column $operation :value";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':value', $value);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ? array_map(fn($data) => new self($data), $results) : null;
        } catch (PDOException $e) {
            die("Error fetching riders: " . $e->getMessage());
        }
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

    public static function getRider() {
        try {
            $sql = "SELECT * FROM " . static::$table . " ORDER BY fullname ASC";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ? array_map(fn($data) => new self($data), $results) : null;
        } catch (PDOException $e) {
            die("Error fetching riders: " . $e->getMessage());
        }
    }
}
?>