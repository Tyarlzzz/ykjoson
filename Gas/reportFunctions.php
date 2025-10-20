<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/GasOrder.php';
require_once '../Models/Item_inventory.php';
require_once '../Models/Expense_Gas.php';

// Get sales report data for gas system with optional filters
function getGasSalesReport($pdo, $period = 'all', $year = null, $month = null, $week = null)
{
  try {
    $year = $year ?? date('Y');


    $sql = "SELECT 
                    o.order_id,
                    o.order_date,
                    o.status,
                    o.created_at,
                    c.customer_id,
                    c.fullname,
                    c.address,
                    goi.quantity,
                    goi.unit_price,
                    goi.total,
                    p.product_name,
                    p.brand,
                    ii.item_name
                FROM orders o
                JOIN customer c ON o.customer_id = c.customer_id
                JOIN gas_ordered_items goi ON o.order_id = goi.order_id
                JOIN `product codes` p ON goi.product_code_id = p.code_id
                JOIN `item allotment` ia ON goi.allotment_id = ia.allotment_id
                JOIN `item inventory` ii ON ia.item_id = ii.item_id
                WHERE o.business_type = 'Gas System'
                AND YEAR(o.order_date) = :year";

    $params = [':year' => $year];

    // Add month filter if specified
    if ($month !== null) {
      $sql .= " AND MONTH(o.order_date) = :month";
      $params[':month'] = $month;
    }

    // Add week filter if specified (week is calculated as day ranges within a month)
    if ($week !== null && $month !== null) {
      $weekRanges = [
        1 => [1, 7],
        2 => [8, 14],
        3 => [15, 21],
        4 => [22, 28],
        5 => [29, 31]
      ];

      if (isset($weekRanges[$week])) {
        $sql .= " AND DAY(o.order_date) BETWEEN :day_start AND :day_end";
        $params[':day_start'] = $weekRanges[$week][0];
        $params[':day_end'] = $weekRanges[$week][1];
      }
    }

    // Only include orders with status 'paid' for sales calculation
    $sql .= " AND (o.status) = 'paid'";

    $sql .= " ORDER BY o.order_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compute summary values
    $totalSales = 0;
    $totalPaid = 0;
    $uniqueCustomers = [];
    $paidCount = 0;

    foreach ($salesData as $row) {
      $totalSales += $row['total'];
      $uniqueCustomers[$row['customer_id']] = true;

      if (strtolower($row['status']) === 'paid') {
        $totalPaid += $row['total'];
        $paidCount++;
      }
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
      'salesData' => $salesData,
      'totalSales' => $totalSales,
      'totalPaid' => $totalPaid,
      'customerCount' => $customerCount,
      'netWorth' => $netWorth,
      'paidCount' => $paidCount,
      'weeklyExpenses' => $weeklyExpenses
    ];

  } catch (PDOException $e) {
    error_log("Error fetching sales data: " . $e->getMessage());
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
    $db = new Database();
    $pdo = $db->getConnection();
    Expense_Gas::setConnection($pdo);
    
    // Use numeric month (1-12) format, as stored in the database
    $expenses = Expense_Gas::getByMonthYear($month, $year, 'Gas System');
    
    // Find week expenses
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

// Get available months with sales data for a specific year
function getAvailableMonths($pdo, $year = null)
{
  try {
    $year = $year ?? date('Y');

    $sql = "SELECT DISTINCT MONTH(o.order_date) as month_num, 
                       MONTHNAME(o.order_date) as month_name
                FROM orders o
                WHERE o.business_type = 'Gas System'
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
      $report = getGasSalesReport($pdo, 'week', $year, $month, $week);

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
    $report = getGasSalesReport($pdo, 'month', $year, $month);

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

    $report = getGasSalesReport($pdo, 'week', $year, $month, $week);

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