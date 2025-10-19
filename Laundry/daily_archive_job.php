<?php
/**
 * Daily Archive Job - Archives orders that were paid 2 days ago
 * 
 * This script should be run daily via cron job at any time (e.g., midnight)
 * Cron job example: 0 0 * * * /path/to/php /path/to/daily_archive_job.php
 * 
 * Or can be run manually: php daily_archive_job.php
 */

require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/LaundryArchivedOrder.php';

// For logging
$logFile = '../logs/archive_job.log';

function logMessage($message) {
    global $logFile;
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    
    // Write to log file
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    // Also output to console if running from command line
    if (php_sapi_name() === 'cli') {
        echo $logEntry;
    }
}

try {
    logMessage("Starting daily archive job...");
    
    // Initialize database connection
    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);
    
    // Archive orders paid 2 days ago
    $result = LaundryArchivedOrder::archiveOrdersPaidTwoDaysAgo();
    
    logMessage("Archive job completed:");
    logMessage("- Total orders processed: " . $result['total_processed']);
    logMessage("- Successfully archived: " . $result['archived_count']);
    logMessage("- Failed to archive: " . $result['failed_count']);
    
    if (!empty($result['details'])) {
        foreach ($result['details'] as $detail) {
            logMessage("  " . $detail);
        }
    }
    
    if ($result['total_processed'] === 0) {
        logMessage("No orders found to archive (no orders paid exactly 2 days ago)");
    }
    
    logMessage("Archive job finished successfully");
    
    // Return success for cron job monitoring
    exit(0);
    
} catch (Exception $e) {
    $errorMessage = "Archive job failed: " . $e->getMessage();
    logMessage("ERROR: " . $errorMessage);
    
    // You could send an email notification here if needed
    // mail('admin@yoursite.com', 'Archive Job Failed', $errorMessage);
    
    // Return error code for cron job monitoring
    exit(1);
}
?>