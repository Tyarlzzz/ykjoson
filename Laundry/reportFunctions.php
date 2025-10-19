<?php
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/Laundry.php';
require_once '../Models/Item_inventory.php';

// Get sales report data for laundry system with optional filters
function getLaundrySalesReport($pdo, $period = 'all', $year = null, $month = null, $week = null)
{
  try {
    $year = $year ?? date('Y');

    // ✅ 1. Pull only unique orders (not item-level)
    $sql = "SELECT 
              o.order_id,
              o.order_date,
              o.status,
              o.created_at,
              c.customer_id,
              c.fullname,
              c.address,
              o.total_price
            FROM orders o
            JOIN customer c ON o.customer_id = c.customer_id
            WHERE o.business_type = 'Laundry System'
              AND YEAR(o.order_date) = :year";

    $params = [':year' => $year];

    // ✅ Add month filter if specified
    if ($month !== null) {
      $sql .= " AND MONTH(o.order_date) = :month";
      $params[':month'] = $month;
    }

    // ✅ Add week filter if specified (week as day range)
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

    // ✅ Include only paid orders
    $sql .= " AND LOWER(o.status) = 'paid'";
    $sql .= " ORDER BY o.order_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ 2. Compute correct totals (no duplication)
    $totalSales = 0;
    $totalPaid = 0;
    $paidCount = 0;
    $uniqueCustomers = [];

    foreach ($orders as $row) {
      $orderTotal = floatval($row['total_price']);
      $totalSales += $orderTotal;
      $uniqueCustomers[$row['customer_id']] = true;

      if (strtolower($row['status']) === 'paid') {
        $totalPaid += $orderTotal;
        $paidCount++;
      }
    }

    $customerCount = count($uniqueCustomers);
    $netWorth = $totalPaid; // same as totalPaid, can be adjusted later

    return [
      'salesData' => $orders,
      'totalSales' => $totalSales,
      'totalPaid' => $totalPaid,
      'customerCount' => $customerCount,
      'netWorth' => $netWorth,
      'paidCount' => $paidCount
    ];

  } catch (PDOException $e) {
    error_log("Error fetching laundry sales data: " . $e->getMessage());
    return [
      'salesData' => [],
      'totalSales' => 0,
      'totalPaid' => 0,
      'customerCount' => 0,
      'netWorth' => 0,
      'paidCount' => 0
    ];
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
      'netWorth' => $report['netWorth']
    ];

  } catch (Exception $e) {
    error_log("Error fetching current week data: " . $e->getMessage());
    return [
      'week' => 1,
      'sales' => 0,
      'customers' => 0,
      'paid' => 0,
      'netWorth' => 0
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

// Get short month name 
function getShortMonthName($monthNum)
{
  $monthNames = [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'May',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Aug',
    9 => 'Sep',
    10 => 'Oct',
    11 => 'Nov',
    12 => 'Dec'
  ];

  return $monthNames[$monthNum] ?? 'Jan';
}
?>