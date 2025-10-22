<?php
require_once 'Models.php';

class Expense extends Model
{
    protected static $table = 'expenses';

    public static function getByMonthYear($month, $year, $business_type = 'Gas System')
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " 
                        WHERE business_type = :business_type 
                        AND month = :month 
                        AND year = :year 
                        ORDER BY week_number ASC";
            $stmt = self::$conn->prepare($sql);
            $stmt->bindValue(':business_type', $business_type);
            $stmt->bindValue(':month', $month);
            $stmt->bindValue(':year', $year);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error fetching expenses: " . $e->getMessage());
        }
    }

    public static function saveOrUpdateWeek($data)
    {
        try {
            $sql = "SELECT expense_id FROM " . static::$table . " 
                        WHERE business_type = :business_type 
                        AND week_number = :week_number 
                        AND month = :month 
                        AND year = :year";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([
                ':business_type' => $data['business_type'],
                ':week_number' => $data['week_number'],
                ':month' => $data['month'],
                ':year' => $data['year']
            ]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $updateSql = "UPDATE " . static::$table . " 
                                SET expense_items = :expense_items, 
                                    total_amount = :total_amount,
                                    week_start_date = :week_start_date,
                                    week_end_date = :week_end_date,
                                    updated_at = NOW()
                                WHERE expense_id = :id";
                $updateStmt = self::$conn->prepare($updateSql);
                $updateStmt->execute([
                    ':expense_items' => json_encode($data['expense_items']),
                    ':total_amount' => $data['total_amount'],
                    ':week_start_date' => $data['week_start_date'],
                    ':week_end_date' => $data['week_end_date'],
                    ':id' => $existing['expense_id']
                ]);
                return $existing['expense_id'];
            } else {
                $insertSql = "INSERT INTO " . static::$table . " 
                                (business_type, week_number, month, year, week_start_date, week_end_date, expense_items, total_amount)
                                VALUES (:business_type, :week_number, :month, :year, :week_start_date, :week_end_date, :expense_items, :total_amount)";
                $insertStmt = self::$conn->prepare($insertSql);
                $insertStmt->execute([
                    ':business_type' => $data['business_type'],
                    ':week_number' => $data['week_number'],
                    ':month' => $data['month'],
                    ':year' => $data['year'],
                    ':week_start_date' => $data['week_start_date'],
                    ':week_end_date' => $data['week_end_date'],
                    ':expense_items' => json_encode($data['expense_items']),
                    ':total_amount' => $data['total_amount']
                ]);
                return self::$conn->lastInsertId();
            }
        } catch (PDOException $e) {
            die("Error saving expense: " . $e->getMessage());
        }
    }

    public static function deleteMonth($month, $year, $business_type)
    {
        try {
            $sql = "DELETE FROM " . static::$table . " WHERE business_type = :business_type AND month = :month AND year = :year";
            $stmt = self::$conn->prepare($sql);
            $success = $stmt->execute([
                ':business_type' => $business_type,
                ':month' => $month,
                ':year' => $year
            ]);
            return $success;
        } catch (PDOException $e) {
            die("Error deleting expenses: " . $e->getMessage());
        }
    }

    public static function getMonthlyTotal($month, $year, $business_type = 'Gas System')
    {
        try {
            $sql = "SELECT SUM(total_amount) AS monthly_total
                        FROM " . static::$table . "
                        WHERE business_type = :business_type 
                        AND month = :month 
                        AND year = :year";
            $stmt = self::$conn->prepare($sql);
            $stmt->execute([
                ':business_type' => $business_type,
                ':month' => $month,
                ':year' => $year
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['monthly_total'] ?? 0;
        } catch (PDOException $e) {
            die("Error calculating total: " . $e->getMessage());
        }
    }
}
?>