<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/Laundry.php';
require_once '../Models/Item_inventory.php';
require_once '../Models/Expense_Laundry.php';


// Get sales report data for laundry system with optional filters

  function getLaundrySalesReport($pdo, $period = 'all', $year = null, $month = null, $week = null)
{
  try {
    $year = $year ?? date('Y');

    // ✅ Base SQL (combine active + archived)
    $sql = "
      SELECT 
        merged.order_id,
        merged.customer_id,
        merged.fullname,
        merged.address,
        merged.total_price,
        merged.order_date,
        merged.status
      FROM (
        -- Active orders
        SELECT 
          o.order_id,
          o.customer_id,
          c.fullname,
          c.address,
          o.total_price,
          o.order_date,
          o.status
        FROM orders o
        JOIN customer c ON o.customer_id = c.customer_id
        WHERE o.business_type = 'Laundry System'
          AND LOWER(o.status) = 'paid'

        UNION ALL

        -- Archived orders (date_created → order_date)
        SELECT 
          ao.order_id,
          ao.customer_id,
          ao.fullname,
          ao.address,
          ao.total_price,
          ao.date_created AS order_date,
          'paid' AS status
        FROM laundry_archived_orders ao
      ) AS merged
      WHERE YEAR(merged.order_date) = :year
    ";

    $params = [':year' => $year];

    // ✅ Period filters
    if ($month !== null) {
      $sql .= " AND MONTH(merged.order_date) = :month";
      $params[':month'] = $month;
    }

    if ($week !== null && $month !== null) {
      // Define weekly ranges for chart (1–7, 8–14, etc.)
      $weekRanges = [
        1 => [1, 7],
        2 => [8, 14],
        3 => [15, 21],
        4 => [22, 28],
        5 => [29, 31]
      ];

      if (isset($weekRanges[$week])) {
        $sql .= " AND DAY(merged.order_date) BETWEEN :day_start AND :day_end";
        $params[':day_start'] = $weekRanges[$week][0];
        $params[':day_end'] = $weekRanges[$week][1];
      }
    }

    $sql .= " ORDER BY merged.order_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Compute summary values
    $totalSales = 0;
    $totalPaid = 0;
    $paidCount = 0;
    $uniqueCustomers = [];

    foreach ($orders as $row) {
      $orderTotal = floatval($row['total_price']);
      $totalSales += $orderTotal;
      $uniqueCustomers[$row['customer_id']] = true;
      $totalPaid += $orderTotal;
      $paidCount++;
    }

    $customerCount = count($uniqueCustomers);
    
    // Get weekly expenses if week is specified
    $weeklyExpenses = 0;
    if ($week !== null && $month !== null) {
      $weeklyExpenses = getWeeklyExpenses($year, $month, $week);
    }
    
    // Calculate net worth: sales - expenses
    $netWorth = $totalSales - $weeklyExpenses;

    return [
      'salesData' => $orders,
      'totalSales' => $totalSales,
      'totalPaid' => $totalPaid,
      'customerCount' => $customerCount,
      'netWorth' => $netWorth,
      'paidCount' => $paidCount,
      'weeklyExpenses' => $weeklyExpenses
    ];

  } catch (PDOException $e) {
    error_log("Error fetching laundry sales data: " . $e->getMessage());
    return [
      'salesData' => [],
      'totalSales' => 0,
      'totalPaid' => 0,
      'customerCount' => 0,
      'netWorth' => 0,
      'paidCount' => 0,
      'weeklyExpenses' => 0
    ];
  }
}

// Get weekly expenses for a specific week
function getWeeklyExpenses($year, $month, $week)
{
  try {
    // Initialize database connection for Expense model
    $db = new Database();
    $pdo = $db->getConnection();
    Expense::setConnection($pdo);
    
    // Use numeric month (1-12) since that's what's stored in the database
    $expenses = Expense::getByMonthYear($month, $year, 'Laundry Business');
    
    // Find the expense for the specific week
    foreach ($expenses as $expense) {
      if ($expense['week_number'] == $week) {
        return floatval($expense['total_amount']);
      }
    }
    
    return 0;
  } catch (Exception $e) {
    error_log("Error fetching weekly expenses: " . $e->getMessage());
    return 0;
  }
}

function getAvailableMonths($pdo, $year = null)
{
  try {
    $year = $year ?? date('Y');

    $sql = "SELECT DISTINCT MONTH(o.order_date) as month_num, 
                       MONTHNAME(o.order_date) as month_name
                FROM orders o
                WHERE o.business_type = 'Laundry System'
                AND YEAR(o.order_date) = :year
                ORDER BY month_num ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':year' => $year]);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no data exists, return current month
    if (empty($results)) {
      $currentMonth = date('n');
      $monthNames = [
        '',
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
      ];
      return [
        [
          'month_num' => $currentMonth,
          'month_name' => $monthNames[$currentMonth]
        ]
      ];
    }

    return $results;

  } catch (PDOException $e) {
    error_log("Error fetching available months: " . $e->getMessage());
    return [];
  }
}

// Get weekly sales data for a specific month
function getWeeklySalesData($pdo, $year, $month)
{
  try {
    $weeklyData = [];

    // Calculate data for each week (5 weeks per month)
    for ($week = 1; $week <= 5; $week++) {
      $report = getLaundrySalesReport($pdo, 'week', $year, $month, $week);

      $weeklyData[] = [
        'week' => $week,
        'sales' => $report['totalSales'],
        'customers' => $report['customerCount'],
        'paid' => $report['paidCount']
      ];
    }

    return $weeklyData;

  } catch (Exception $e) {
    error_log("Error fetching weekly sales data: " . $e->getMessage());
    return [];
  }
}

// Get monthly summary for a specific month
function getMonthlySummary($pdo, $year, $month)
{
  try {
    $report = getLaundrySalesReport($pdo, 'month', $year, $month);

    return [
      'sales' => $report['totalSales'],
      'customers' => $report['customerCount'],
      'paid' => $report['paidCount']
    ];

  } catch (Exception $e) {
    error_log("Error fetching monthly summary: " . $e->getMessage());
    return [
      'sales' => 0,
      'customers' => 0,
      'paid' => 0
    ];
  }
}

// Get yearly sales data with monthly and weekly breakpoints
function getYearlySalesData($pdo, $year = null)
{
  try {
    $year = $year ?? date('Y');
    $monthNames = [
      1 => 'January',
      2 => 'February',
      3 => 'March',
      4 => 'April',
      5 => 'May',
      6 => 'June',
      7 => 'July',
      8 => 'August',
      9 => 'September',
      10 => 'October',
      11 => 'November',
      12 => 'December'
    ];

    $yearlyData = [];

    // Get all months with data
    $availableMonths = getAvailableMonths($pdo, $year);

    foreach ($availableMonths as $monthData) {
      $monthNum = $monthData['month_num'];
      $monthName = $monthNames[$monthNum];

      $weeklyData = getWeeklySalesData($pdo, $year, $monthNum);
      $monthlySummary = getMonthlySummary($pdo, $year, $monthNum);

      $yearlyData[$monthName] = [
        'weeks' => $weeklyData,
        'monthly' => $monthlySummary
      ];
    }

    return $yearlyData;

  } catch (Exception $e) {
    error_log("Error fetching yearly sales data: " . $e->getMessage());
    return [];
  }
}

// Get current week's summary data
function getCurrentWeekData($pdo)
{
  try {
    $year = date('Y');
    $month = date('n');
    $day = date('j');

    // Determine which week of the month we're in
    $week = 1;
    if ($day >= 8 && $day <= 14)
      $week = 2;
    elseif ($day >= 15 && $day <= 21)
      $week = 3;
    elseif ($day >= 22 && $day <= 28)
      $week = 4;
    elseif ($day >= 29)
      $week = 5;

    $report = getLaundrySalesReport($pdo, 'week', $year, $month, $week);

    return [
      'week' => $week,
      'sales' => $report['totalSales'],
      'customers' => $report['customerCount'],
      'paid' => $report['paidCount'],
      'netWorth' => $report['netWorth'],
      'expenses' => $report['weeklyExpenses']
    ];

  } catch (Exception $e) {
    error_log("Error fetching current week data: " . $e->getMessage());
    return [
      'week' => 1,
      'sales' => 0,
      'customers' => 0,
      'paid' => 0,
      'netWorth' => 0,
      'expenses' => 0
    ];
  }
}

// Functions for month names
function getMonthName($monthNum)
{
  $monthNames = [
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December'
  ];

  return $monthNames[$monthNum] ?? 'January';
}
?>