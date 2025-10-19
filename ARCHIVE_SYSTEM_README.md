# Laundry Orders Archive System

## Overview
The archive system automatically stores completed laundry orders for historical record-keeping and reporting purposes.

## Database Table: `laundry_archived_orders`

### Table Structure
- `archived_id` (Primary Key) - Auto-incremented unique identifier
- `order_id` - Original order ID from the orders table
- `customer_id` - Customer ID
- `user_id` - User who handled the order
- `fullname` - Customer full name
- `phone_number` - Customer phone number
- `address` - Customer address
- `total_weight` - Total weight of the order in kg
- `total_price` - Total price of the order
- `is_rushed` - Whether the order was rushed (1 or 0)
- `note` - Any additional notes
- `date_created` - Original order creation date
- `date_delivered` - Date when order status was changed to "Delivered"
- `created_at` - Archive record creation timestamp
- `updated_at` - Archive record last update timestamp

## How It Works

### Automatic Archiving
1. When a laundry order status is changed to "Delivered" through `updateStatus.php`
2. The system automatically creates an archive record with:
   - All order details
   - Customer information
   - Calculated total weight and price from `laundry_ordered_items`
   - Current timestamp as `date_delivered`

### Manual Management
- Use `archive_existing_orders.php` to archive existing delivered orders
- The `LaundryArchivedOrder` model provides methods for:
  - Creating archive records
  - Retrieving archived orders
  - Searching by date range
  - Checking if an order is already archived

## Files Modified/Created

### New Files
- `Models/LaundryArchivedOrder.php` - Model for archive operations
- `Laundry/archive_existing_orders.php` - Utility for bulk archiving

### Modified Files
- `Laundry/updateStatus.php` - Added automatic archiving on delivery
- `Laundry/archived.php` - Updated to display actual archived data with search

## Usage

### Viewing Archived Orders
Navigate to `Laundry/archived.php` to view all archived orders with:
- Search functionality (by name, phone, or order ID)
- Formatted display of order details
- Total count of archived orders

### API Methods (LaundryArchivedOrder class)
- `archiveOrder($order_id)` - Archive a specific order
- `getAllArchived($limit, $offset)` - Get all archived orders with pagination
- `getByDateRange($start_date, $end_date)` - Get orders by delivery date range
- `isOrderArchived($order_id)` - Check if order is already archived

## Benefits
1. **Historical Records** - Maintains complete order history
2. **Performance** - Separates active and completed orders
3. **Reporting** - Easy access to delivery statistics
4. **Data Integrity** - Prevents accidental loss of completed order data
5. **Search Capability** - Quick search through archived records

## Future Enhancements
- Export functionality (CSV, PDF)
- Advanced filtering options
- Archive retention policies
- Performance analytics from archived data