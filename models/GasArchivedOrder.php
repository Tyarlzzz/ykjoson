<?php
/**
 * 🎯 GAS ARCHIVE MODEL (FINAL VERSION)
 * 
 * Handles automatic archiving for Gas System orders.
 * 
 * 💡 Archive Delay Configuration:
 *   - Change $archiveDelayMinutes to adjust how long after "Paid" an order should be archived.
 *   - Example:
 *       0.5 = 30 seconds
 *       1   = 1 minute
 *       2   = 2 minutes
 *       5   = 5 minutes
 */

require_once 'Models.php';

class GasArchivedOrder extends Model {
    protected static $table = "gas_archived_orders";

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

    /* ==========================================================
       ✅ CORE ARCHIVE METHOD
       Archives a single paid Gas order.
    ========================================================== */
    public static function archiveOrder($order_id) {
        try {
            $sql = "INSERT INTO gas_archived_orders 
                    (order_id, customer_id, user_id, fullname, phone_number, address, 
                     total_weight, total_price, is_rushed, note, date_created, date_delivered)
                    SELECT 
                        o.order_id,
                        o.customer_id,
                        COALESCE(o.user_id, 0) AS user_id,
                        c.fullname,
                        c.phone_number,
                        c.address,
                        COALESCE(SUM(goi.quantity), 0) AS total_weight,
                        COALESCE(SUM(goi.total), 0) AS total_price,
                        o.is_rushed,
                        o.note,
                        o.created_at AS date_created,
                        NOW() AS date_delivered
                    FROM orders o
                    INNER JOIN customer c ON o.customer_id = c.customer_id
                    LEFT JOIN gas_ordered_items goi ON o.order_id = goi.order_id
                    WHERE o.order_id = :order_id 
                      AND o.business_type = 'Gas System'
                      AND o.status = 'Paid'
                    GROUP BY o.order_id, o.customer_id, o.user_id, 
                             c.fullname, c.phone_number, c.address, 
                             o.is_rushed, o.note, o.created_at";

            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("⚠️ Failed to archive Gas order #$order_id: " . implode(' | ', $error));
                return false;
            }

            error_log("✅ Successfully archived Gas order #$order_id");
            return true;

        } catch (PDOException $e) {
            error_log("❌ PDO Exception during Gas archive: " . $e->getMessage());
            throw new Exception("Error archiving Gas order: " . $e->getMessage());
        }
    }

    /* ==========================================================
       ✅ AUTO-ARCHIVE FOR PAID ORDERS
       Archives all 'Paid' Gas orders after delay.
    ========================================================== */
    public static function autoArchivePaidOrders() {
        try {
            $archiveDelayMinutes = 1; // ⏱ Change delay time here

            $eligibleStart = date('Y-m-d H:i:s', strtotime("-{$archiveDelayMinutes} minute"));
            $now = date('Y-m-d H:i:s');

            $sql = "SELECT o.order_id
                    FROM orders o
                    WHERE o.business_type = 'Gas System'
                      AND o.status = 'Paid'
                      AND o.paid_at BETWEEN :start_time AND :end_time
                      AND o.order_id NOT IN (SELECT order_id FROM gas_archived_orders)";
            
            $stmt = self::$conn->prepare($sql);
            $stmt->bindParam(':start_time', $eligibleStart);
            $stmt->bindParam(':end_time', $now);
            $stmt->execute();

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $archivedCount = 0;
            $failed = [];

            foreach ($orders as $row) {
                $id = $row['order_id'];
                try {
                    if (self::archiveOrder($id)) {
                        $archivedCount++;
                    } else {
                        $failed[] = $id;
                    }
                } catch (Exception $e) {
                    $failed[] = $id . " (" . $e->getMessage() . ")";
                }
            }

            return [
                'total_checked' => count($orders),
                'archived' => $archivedCount,
                'failed' => $failed
            ];

        } catch (PDOException $e) {
            throw new Exception("Error during auto-archiving: " . $e->getMessage());
        }
    }

    /* ==========================================================
       ✅ CHECK IF ORDER IS ALREADY ARCHIVED
    ========================================================== */
    public static function isOrderArchived($order_id) {
        $sql = "SELECT COUNT(*) AS count FROM gas_archived_orders WHERE order_id = :order_id";
        $stmt = self::$conn->prepare($sql);
        $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && $row['count'] > 0;
    }

    /* ==========================================================
       ✅ FETCH ARCHIVED ORDERS
    ========================================================== */
public static function getAllArchived($limit = 50, $offset = 0) {
    $sql = "SELECT * FROM gas_archived_orders 
            ORDER BY date_delivered DESC 
            LIMIT :limit OFFSET :offset";
    $stmt = self::$conn->prepare($sql);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $results
        ? array_map(fn($data) => new self($data), $results)
        : [];
}


    
            public static function archiveOrderWithCustomDeliveryDate($order_id) {
            try {
                $sql = "INSERT INTO gas_archived_orders 
                        (order_id, customer_id, user_id, fullname, phone_number, address, 
                         total_weight, total_price, is_rushed, note, date_created, date_delivered)
                        SELECT 
                            o.order_id,
                            o.customer_id,
                            o.user_id,
                            c.fullname,
                            c.phone_number,
                            c.address,
                            COALESCE(SUM(loi.weight_kg), 0) as total_weight,
                            COALESCE(SUM(loi.total), 0) as total_price,
                            o.is_rushed,
                            o.note,
                            o.created_at as date_created,
                            COALESCE(o.paid_at, NOW()) as date_delivered
                        FROM orders o
                        INNER JOIN customer c ON o.customer_id = c.customer_id
                        LEFT JOIN gas_ordered_items loi ON o.order_id = loi.order_id
                        WHERE o.order_id = :order_id 
                        AND o.business_type = 'Gas System'
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

        public static function archiveOrdersPaidTwoDaysAgo() {
    try {
        // Archive delay: 2 days
        $startDate = date('Y-m-d H:i:s', strtotime('-2 days'));
        $endDate = date('Y-m-d H:i:s');

        $sql = "SELECT o.order_id 
                FROM orders o 
                WHERE o.business_type = 'Gas System' 
                  AND o.status = 'Paid'
                  AND o.paid_at BETWEEN :start_date AND :end_date
                  AND o.order_id NOT IN (SELECT order_id FROM gas_archived_orders)";
        
        $stmt = self::$conn->prepare($sql);
        $stmt->bindParam(':start_date', $startDate);
        $stmt->bindParam(':end_date', $endDate);
        $stmt->execute();

        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $archivedCount = 0;

        foreach ($orders as $order) {
            if (self::archiveOrder($order['order_id'])) {
                // ✅ Keep "Paid" as-is; do NOT change to "Delivered"
                $markArchived = self::$conn->prepare("
                    UPDATE orders 
                    SET archive_at = NOW() 
                    WHERE order_id = :order_id
                ");
                $markArchived->bindParam(':order_id', $order['order_id'], PDO::PARAM_INT);
                $markArchived->execute();

                $archivedCount++;
            }
        }

        return [
            'total_processed' => count($orders),
            'archived_count' => $archivedCount
        ];
    } catch (PDOException $e) {
        throw new Exception("Error auto-archiving Gas orders: " . $e->getMessage());
    }
}


    /* ==========================================================
       ✅ FETCH ARCHIVED ORDERS BY DATE RANGE
    ========================================================== */
    public static function getByDateRange($start, $end) {
        $sql = "SELECT * FROM gas_archived_orders 
                WHERE DATE(date_delivered) BETWEEN :start AND :end 
                ORDER BY date_delivered DESC";
        $stmt = self::$conn->prepare($sql);
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
