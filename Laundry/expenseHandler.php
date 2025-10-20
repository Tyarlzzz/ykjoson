<?php
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/Expense_Laundry.php';

    header('Content-Type: application/json');

    try {
        $database = new Database();
        $conn = $database->getConnection();
        Expense::setConnection($conn);

        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
                exit;
            }

            $required = ['business_type', 'week_number', 'month', 'year', 'expense_items', 'total_amount'];
            foreach ($required as $field) {
                if (!isset($input[$field])) {
                    echo json_encode(["status" => "error", "message" => "Missing field: $field"]);
                    exit;
                }
            }

            $month = $input['month'];
            $year = $input['year'];
            $week_number = $input['week_number'];
            $week_start = date('Y-m-d', strtotime("$year-$month-01 +".($week_number-1)." week"));
            $week_end = date('Y-m-d', strtotime("$week_start +6 days"));

            $id = Expense::saveOrUpdateWeek([
                'business_type' => $input['business_type'],
                'week_number' => $week_number,
                'month' => $month,
                'year' => $year,
                'week_start_date' => $week_start,
                'week_end_date' => $week_end,
                'expense_items' => $input['expense_items'],
                'total_amount' => $input['total_amount']
            ]);

            echo json_encode([
                "status" => "success",
                "message" => "Week $week_number expenses saved successfully!",
                "id" => $id
            ]);
            exit;
        }

        if ($method === 'GET') {
            $business_type = $_GET['business_type'] ?? 'Laundry Business';
            $month = $_GET['month'] ?? date('n');
            $year = $_GET['year'] ?? date('Y');

            $weeks = Expense::getByMonthYear($month, $year, $business_type);
            $monthly_total = Expense::getMonthlyTotal($month, $year, $business_type);

            echo json_encode([
                "status" => "success",
                "data" => [
                    "weeks" => $weeks,
                    "monthly_total" => $monthly_total
                ]
            ]);
            exit;
        }

        echo json_encode(["status" => "error", "message" => "Invalid request method."]);

    } catch (Throwable $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Server error: " . $e->getMessage()
        ]);
    }
?>