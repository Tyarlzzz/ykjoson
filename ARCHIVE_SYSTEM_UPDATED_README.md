# Laundry Archive System - Updated Setup Guide

## Changes Made

### Database Changes
1. **Added `paid_at` column** to `orders` table to track when orders are marked as paid
2. **Enhanced `laundry_archived_orders` table** structure remains the same

### Archive Logic Changes
- **Previous**: Orders archived immediately when marked as "Delivered"
- **New**: Orders archived 2 days after being marked as "Paid"

### New Files Created
1. `daily_archive_job.php` - Daily cron job script
2. `archive_management.php` - Web interface for manual archive control
3. Updated `archive_existing_orders.php` - Now handles paid orders instead of delivered

### Updated Files
1. `updateStatus.php` - Now tracks `paid_at` timestamp when status changes to "Paid"
2. `LaundryArchivedOrder.php` - Added new methods for scheduled archiving
3. `Order.php` - Added `paid_at` field

## How to Set Up Automated Archiving

### Option 1: Cron Job (Linux/macOS)
```bash
# Edit your crontab
crontab -e

# Add this line to run daily at midnight
0 0 * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/ykjoson/Laundry/daily_archive_job.php

# Or run at 2 AM daily
0 2 * * * /Applications/XAMPP/xamppfiles/bin/php /Applications/XAMPP/xamppfiles/htdocs/ykjoson/Laundry/daily_archive_job.php
```

### Option 2: Manual Execution
Visit: `http://localhost/ykjoson/Laundry/archive_management.php`
Click "Run Archive Process Now" button

### Option 3: Command Line
```bash
cd /Applications/XAMPP/xamppfiles/htdocs/ykjoson/Laundry
/Applications/XAMPP/xamppfiles/bin/php daily_archive_job.php
```

## Testing the System

### 1. Test Paid Status Tracking
1. Create a test order
2. Change status to "Paid"
3. Check if `paid_at` field is populated in database:
```sql
SELECT order_id, status, paid_at FROM orders WHERE status = 'Paid';
```

### 2. Test Manual Archiving
1. Visit `archive_management.php`
2. See eligible orders count
3. Click "Run Archive Process Now"
4. Check results

### 3. Test with Existing Data
```bash
# Run the utility to archive old paid orders
php archive_existing_orders.php
```

## Archive Timeline
```
Day 0: Order created
Day X: Order marked as "Paid" (paid_at timestamp recorded)
Day X+2: Order automatically archived by daily job
```

## Monitoring & Logs
- Archive job logs are stored in: `../logs/archive_job.log`
- Manual runs show results in the web interface
- Failed archives are logged with error details

## Benefits of New System
1. **Better timing control**: 2-day buffer after payment
2. **Automated processing**: No manual intervention needed
3. **Audit trail**: Clear payment timestamps
4. **Flexible management**: Web interface for manual control
5. **Robust logging**: Detailed logs for troubleshooting

## File Structure
```
Laundry/
├── archive_management.php          # Web interface for archive control
├── daily_archive_job.php          # Automated daily archive script
├── archive_existing_orders.php    # Utility for bulk archiving
├── archived.php                   # View archived orders
└── updateStatus.php               # Updated to track paid_at

Models/
└── LaundryArchivedOrder.php       # Enhanced with scheduled archiving

logs/
└── archive_job.log                # Archive process logs
```