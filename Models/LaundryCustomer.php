<?php
require_once 'Models.php';

class LaundryCustomer extends Model {
    protected static $table = 'customer';

    public $customer_id;
    public $fullname;
    public $address;
    public $phone_number;
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
        $result = parent::updateById($this->customer_id, $data);

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
        ];

        if (isset($this->customer_id)) {
            return $this->update($data);
        } else {
            $customer = self::create($data);
            if ($customer) {
                $this->customer_id = $customer->customer_id;
                return true;
            }
            return false;
        }
    }

    public function delete() {
        $result = parent::deleteById($this->customer_id);

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
}
?>