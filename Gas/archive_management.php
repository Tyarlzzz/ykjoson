<?php
require '../layout/header.php';
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/GasArchivedOrder.php';

$message = '';
$messageType = '';

// Handle manual archive trigger
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // Initialize database connection
        $database = new Database();
        $conn = $database->getConnection();
        Model::setConnection($conn);
        
        if ($_POST['action'] === 'archive_now') {
            $result = GasArchivedOrder::archiveOrdersPaidTwoDaysAgo();
            
            $message = "Archive job completed:\n";
            $message .= "- Total orders processed: " . $result['total_processed'] . "\n";
            $message .= "- Successfully archived: " . $result['archived_count'] . "\n";
            $message .= "- Failed to archive: " . $result['failed_count'] . "\n";
            
            if (!empty($result['details'])) {
                $message .= "\nDetails:\n" . implode("\n", $result['details']);
            }
            
            $messageType = 'success';
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Get some statistics
try {
    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);
    
    // Count orders eligible for archiving (paid 2+ days ago)
    $twoDaysAgo = date('Y-m-d H:i:s', strtotime('-2 days'));
    $sql = "SELECT COUNT(*) as count FROM orders 
            WHERE business_type = 'Gas System' 
            AND status = 'Paid' 
            AND paid_at IS NOT NULL
            AND paid_at <= :two_days_ago
            AND order_id NOT IN (SELECT order_id FROM gas_archived_orders)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':two_days_ago', $twoDaysAgo);
    $stmt->execute();
    $eligibleCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count total archived
    $sql = "SELECT COUNT(*) as count FROM gas_archived_orders";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $totalArchived = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (Exception $e) {
    $eligibleCount = 0;
    $totalArchived = 0;
}
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-blue-600 text-gray-800">Archive Management</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
        <a href="expenses.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Expenses</a>
        <a href="salesReport.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales Report</a>
        <a href="pricing.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Pricing</a>
        <a href="../Rider/ManageRiders.php" class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
        <a href="archived.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Archived</a>
        <a href="archive_management.php" class="px-8 py-1 bg-blue-600 text-white font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archive Mgmt</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <div class="py-8 px-4">
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Eligible for Archive</dt>
                  <dd class="text-lg font-medium text-gray-900"><?php echo $eligibleCount; ?></dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Archived</dt>
                  <dd class="text-lg font-medium text-gray-900"><?php echo $totalArchived; ?></dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Archive Delay</dt>
                  <dd class="text-lg font-medium text-gray-900">2 Days</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Message Display -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'; ?>">
          <pre class="whitespace-pre-wrap font-mono text-sm"><?php echo htmlspecialchars($message); ?></pre>
        </div>
        <?php endif; ?>

        <!-- Archive Controls -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Manual Archive Control</h3>
          <p class="text-gray-600 mb-4">
            Orders are automatically archived 2 days after being marked as "Paid". 
            You can manually trigger the archive process below.
          </p>
          
          <form method="POST" class="space-y-4">
            <div class="flex items-center space-x-4">
              <button type="submit" name="action" value="archive_now" 
                      class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                Run Archive Process Now
              </button>
              <span class="text-sm text-gray-500">
                This will archive all orders paid 2+ days ago
              </span>
            </div>
          </form>
        </div>

        <!-- Information Section -->
        <div class="bg-blue-50 rounded-lg p-6">
          <h3 class="text-lg font-semibold text-blue-900 mb-4">How Archiving Works</h3>
          <div class="space-y-3 text-blue-800">
            <div class="flex items-start">
              <span class="font-medium mr-2">1.</span>
              <span>When an order status is changed to "Paid", the system records the payment timestamp.</span>
            </div>
            <div class="flex items-start">
              <span class="font-medium mr-2">2.</span>
              <span>Orders are automatically archived 2 days after the payment date.</span>
            </div>
            <div class="flex items-start">
              <span class="font-medium mr-2">3.</span>
              <span>Archived orders include all customer details, weights, prices, and payment dates.</span>
            </div>
            <div class="flex items-start">
              <span class="font-medium mr-2">4.</span>
              <span>The daily archive job runs automatically, but can also be triggered manually above.</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</main>

<?php require '../layout/footer.php' ?>