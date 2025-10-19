<?php
    class Expenses extends Model {
        protected static $table = 'expenses';

        public static function getAllExpenses() {
            return self::all();
        }

        public static function findExpense($id) {
            try {
                $sql = "SELECT * FROM " . self::$table . " WHERE expense_id = :expense_id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(':expense_id', $id);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row ?? null;
            } catch (PDOException $e) {
                die("Error fetching expense: " . $e->getMessage());
            }
        }

        public static function getByBusinessType($businessType) {
            return self::where('business_type', '=', $businessType);
        }

        public static function getByMonthYear($businessType, $month, $year) {
            try {
                $sql = "SELECT * FROM " . self::$table . 
                    " WHERE business_type = :business_type 
                        AND month = :month 
                        AND year = :year 
                    ORDER BY week_number ASC";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindValue(':business_type', $businessType);
                $stmt->bindValue(':month', $month);
                $stmt->bindValue(':year', $year);
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                return count($rows) > 0 ? $rows : null;
            } catch (PDOException $e) {
                die("Error fetching expenses: " . $e->getMessage());
            }
        }

        public static function weekExists($businessType, $weekNumber, $month, $year) {
            try {
                $sql = "SELECT COUNT(*) as count FROM " . self::$table . 
                    " WHERE business_type = :business_type 
                        AND week_number = :week_number 
                        AND month = :month 
                        AND year = :year";
                
                $stmt = self::$conn->prepare($sql);
                $stmt->bindValue(':business_type', $businessType);
                $stmt->bindValue(':week_number', $weekNumber);
                $stmt->bindValue(':month', $month);
                $stmt->bindValue(':year', $year);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                return $result['count'] > 0;
            } catch (PDOException $e) {
                die("Error checking week existence: " . $e->getMessage());
            }
        }

        public static function createExpense(array $data) {
            try {
                // Calculate total amount from expense_items
                $items = json_decode($data['expense_items'], true);
                $totalAmount = 0;
                foreach ($items as $item) {
                    $totalAmount += floatval($item['price']);
                }
                $data['total_amount'] = $totalAmount;

                $columns = implode(", ", array_keys($data));
                $values = implode(", ", array_map(fn($key) => ":$key", array_keys($data)));
                $sql = "INSERT INTO " . self::$table . " ($columns) VALUES ($values)";
                
                $stmt = self::$conn->prepare($sql);
                foreach ($data as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->execute();
                $id = self::$conn->lastInsertId();
                
                return self::findExpense($id);
            } catch (PDOException $e) {
                die("Error creating expense: " . $e->getMessage());
            }
        }

        public static function updateExpense($id, array $data) {
            try {
                // Calculate total amount from expense_items if provided
                if (isset($data['expense_items'])) {
                    $items = json_decode($data['expense_items'], true);
                    $totalAmount = 0;
                    foreach ($items as $item) {
                        $totalAmount += floatval($item['price']);
                    }
                    $data['total_amount'] = $totalAmount;
                }

                $set = implode(", ", array_map(fn($key) => "$key = :$key", array_keys($data)));
                $sql = "UPDATE " . self::$table . " SET $set WHERE expense_id = :expense_id";
                
                $stmt = self::$conn->prepare($sql);
                foreach ($data as $key => $value) {
                    $stmt->bindValue(":$key", $value);
                }
                $stmt->bindValue(":expense_id", $id);
                $stmt->execute();
                
                return self::findExpense($id);
            } catch (PDOException $e) {
                die("Error updating expense: " . $e->getMessage());
            }
        }

        public static function deleteExpense($id) {
            try {
                $sql = "DELETE FROM " . self::$table . " WHERE expense_id = :expense_id";
                $stmt = self::$conn->prepare($sql);
                $stmt->bindParam(":expense_id", $id);
                $stmt->execute();
                
                return $stmt->rowCount() > 0;
            } catch (PDOException $e) {
                die("Error deleting expense: " . $e->getMessage());
            }
        }

        public static function calculateWeekDates($weekNumber, $month, $year) {
            $firstDayOfMonth = strtotime("$year-$month-01");
            $startDay = ($weekNumber - 1) * 7 + 1;
            $endDay = min($startDay + 6, date('t', $firstDayOfMonth)); // t = days in month
            
            $weekStart = date('Y-m-d', strtotime("$year-$month-$startDay"));
            $weekEnd = date('Y-m-d', strtotime("$year-$month-$endDay"));
            
            return [
                'week_start_date' => $weekStart,
                'week_end_date' => $weekEnd
            ];
        }
    }
?>