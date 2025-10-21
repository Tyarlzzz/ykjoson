<?php
    /**
     * 🎯 ARCHIVE TIMING CONFIGURATION:
     * 
     * To change the archive eligibility window, modify this variable on line ~206:
     * $archiveDelayMinutes = 1; // Change this number (in minutes)
     * 
     * Examples:
     * - 0.5 = 30 seconds
     * - 1 = 1 minute
     * - 2 = 2 minutes  
     * - 5 = 5 minutes
     * - 10 = 10 minutes
     */

    require_once 'Models.php';

    class LaundryArchivedOrder extends Model {
        protected static $table = "laundry_archived_orders";

        public $archived_id;
        public $order_id;
        public $customer_id;
        public $user_id;
        public $fullname;
        public $phone_number;
        public $address;
        public $total_weight;
        public $total_price;
        public $is_rushed;
        public $note;
        public $date_created;
        public $date_delivered;
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
            return $result
                ? array_map(fn($data) => new self($data), $result)
                : null;
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
            $result = parent::updateByID($this->archived_id, $data);

            if ($result) {
                foreach ($data as $key => $value) {
                    if (property_exists($this, $key)) {
                        $this->$key = $value;
                    }
                }
                return true;
            } else {
                return false;
            }
        }

        public function save() {
            $data = [
                "order_id" => $this->order_id,
                "customer_id" => $this->customer_id,
                "user_id" => $this->user_id,
                "fullname" => $this->fullname,
                "phone_number" => $this->phone_number,
                "address" => $this->address,
                "total_weight" => $this->total_weight,
                "total_price" => $this->total_price,
                "is_rushed" => $this->is_rushed,
                "note" => $this->note,
                "date_created" => $this->date_created,
                "date_delivered" => $this->date_delivered,
                "updated_at" => $this->updated_at
            ];

            $this->update($data);
        }

        public function delete() {
            $result = parent::deleteByID($this->archived_id);
            
            if ($result) {
                foreach ($this as $key => $value) {
                    unset($this->$key);
                }
                return true;
            } else {
                return false;
            }
        }

        public static function where($column, $operation, $value) {
            $result = parent::where($column, $operation, $value);
            return $result
                ? array_map(fn($data) => new self($data), $result)
                : null;
        }

        /**
         * Archive a completed laundry order
         * This method takes an order_id and creates an archived record
         */
        public static function archiveOrder($order_id) {
            try {
                $sql = "INSERT INTO laundry_archived_orders 
                        (order_id, customer_id, user_id, fullname, phone_number, address, 
                         total_weight, total_price, is_rushed, note, date_created, date_delivered)
                        SELECT 
                            o.order_id,
                            o.customer_id,
                            o.user_id,
                            c.fullname,
                            c.phone_number,
                            c.address,
                            o.total_weight,
                            o.total_price,
                            o.is_rushed,
                            o.note,
                            o.created_at as date_created,
                            NOW() as date_delivered
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN laundry_ordered_items loi ON o.order_id = loi.order_id
                        WHERE o.order_id = :order_id 
                        AND o.business_type = 'Laundry System'
                        AND o.status = 'Delivered'
                        GROUP BY o.order_id, o.customer_id, o.user_id, c.fullname, c.phone_number, 
                                 c.address, o.is_rushed, o.note, o.created_at";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                
                return $stmt->execute();
                
            } catch (PDOException $e) {
                die("Error archiving order: " . $e->getMessage());
            }
        }

        /**
         * Get all archived orders with pagination support
         */
        public static function getAllArchived($limit = 50, $offset = 0) {
            try {
                $sql = "SELECT * FROM laundry_archived_orders 
                        ORDER BY date_delivered DESC 
                        LIMIT :limit OFFSET :offset";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 
                    ? array_map(fn($data) => new self($data), $results)
                    : null;
                
            } catch (PDOException $e) {
                die("Error fetching archived orders: " . $e->getMessage());
            }
        }

        /**
         * Get archived orders by date range
         */
        public static function getByDateRange($start_date, $end_date) {
            try {
                $sql = "SELECT * FROM laundry_archived_orders 
                        WHERE DATE(date_delivered) BETWEEN :start_date AND :end_date
                        ORDER BY date_delivered DESC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
                $stmt->execute();
                
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($results) > 0 
                    ? array_map(fn($data) => new self($data), $results)
                    : null;
                    
            } catch (PDOException $e) {
                die("Error fetching archived orders by date range: " . $e->getMessage());
            }
        }

        /**
         * Archive orders that were paid 2 days ago
         * This method should be called daily via a cron job or scheduled task
         */
        public static function archiveOrdersPaidTwoDaysAgo() {
            try {
                // 🎯 CUSTOMIZE ARCHIVE ELIGIBILITY WINDOW HERE:
                $archiveDelayMinutes = 1; // 1 = 1 minute, 2 = 2 minutes, 0.5 = 30 seconds
                
                // Calculate the eligibility window
                $eligibilityTime = date('Y-m-d H:i:s', strtotime("-{$archiveDelayMinutes} minute"));
                $twoDaysAgoStart = $eligibilityTime;
                $twoDaysAgoEnd = date('Y-m-d H:i:s'); // Current time

                // Find orders that were paid exactly 2 days ago and haven't been archived
                $sql = "SELECT o.order_id 
                        FROM orders o 
                        WHERE o.business_type = 'Laundry System' 
                        AND o.status = 'Paid'
                        AND o.paid_at BETWEEN :start_date AND :end_date
                        AND o.order_id NOT IN (SELECT order_id FROM laundry_archived_orders)";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':start_date', $twoDaysAgoStart);
                $stmt->bindParam(':end_date', $twoDaysAgoEnd);
                $stmt->execute();
                
                $ordersToArchive = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $archived_count = 0;
                $failed_count = 0;
                $results = [];
                
                foreach ($ordersToArchive as $order) {
                    $order_id = $order['order_id'];
                    
                    try {
                        if (self::archiveOrderWithCustomDeliveryDate($order_id)) {
                            $archived_count++;
                            $results[] = "Successfully archived order ID: $order_id";
                        } else {
                            $failed_count++;
                            $results[] = "Failed to archive order ID: $order_id";
                        }
                    } catch (Exception $e) {
                        $failed_count++;
                        $results[] = "Error archiving order ID $order_id: " . $e->getMessage();
                    }
                }
                
                return [
                    'total_processed' => count($ordersToArchive),
                    'archived_count' => $archived_count,
                    'failed_count' => $failed_count,
                    'details' => $results
                ];
                
            } catch (PDOException $e) {
                throw new Exception("Error finding orders to archive: " . $e->getMessage());
            }
        }

        /**
         * Archive a specific order with paid_at date as delivery date
         */
        public static function archiveOrderWithCustomDeliveryDate($order_id) {
            try {
                $sql = "INSERT INTO laundry_archived_orders 
                        (order_id, customer_id, user_id, fullname, phone_number, address, 
                         total_weight, total_price, is_rushed, note, date_created, date_delivered)
                        SELECT 
                            o.order_id,
                            o.customer_id,
                            o.user_id,
                            c.fullname,
                            c.phone_number,
                            c.address,
                            o.total_weight,
                            o.total_price,
                            o.is_rushed,
                            o.note,
                            o.created_at as date_created,
                            COALESCE(o.paid_at, NOW()) as date_delivered
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN laundry_ordered_items loi ON o.order_id = loi.order_id
                        WHERE o.order_id = :order_id 
                        AND o.business_type = 'Laundry System'
                        AND o.status = 'Paid'
                        GROUP BY o.order_id, o.customer_id, o.user_id, c.fullname, c.phone_number, 
                                 c.address, o.is_rushed, o.note, o.created_at, o.paid_at";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                
                return $stmt->execute();
                
            } catch (PDOException $e) {
                throw new Exception("Error archiving order with custom delivery date: " . $e->getMessage());
            }
        }

        /**
         * Check if an order is already archived
         */
        public static function isOrderArchived($order_id) {
            try {
                $sql = "SELECT COUNT(*) as count FROM laundry_archived_orders WHERE order_id = :order_id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->execute();
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return $result['count'] > 0;
                
            } catch (PDOException $e) {
                die("Error checking if order is archived: " . $e->getMessage());
            }
        }
    }
?>