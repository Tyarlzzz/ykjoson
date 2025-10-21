// DataTables configuration for archived orders page
document.addEventListener('DOMContentLoaded', function() {
    // Check if archivedOrdersTable exists
    if (document.getElementById('GasArchivedOrdersTable')) {
        $('#archivedOrdersTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            order: [[0, 'desc']], // Order by Order ID descending (newest first)
            columnDefs: [
                {
                    targets: [0], // Order # column
                    className: 'text-center',
                    width: '10%'
                },
                {
                    targets: [1, 2], // Date Created and Date Delivered columns
                    className: 'text-center',
                    width: '15%'
                },
                {
                    targets: [3], // Customer Name column
                    className: 'text-left',
                    width: '20%'
                },
                {
                    targets: [4], // Phone Number column
                    className: 'text-center',
                    width: '15%'
                },
                {
                    targets: [5], // Weight column
                    className: 'text-center',
                    width: '10%'
                },
                {
                    targets: [6], // Total Price column
                    className: 'text-right',
                    width: '15%'
                }
            ],
            language: {
                search: "Search archived orders:",
                searchPlaceholder: "Order ID, Name, or Phone...",
                lengthMenu: "Show _MENU_ archived orders per page",
                info: "Showing _START_ to _END_ of _TOTAL_ archived orders",
                infoEmpty: "No archived orders available",
                infoFiltered: "(filtered from _MAX_ total archived orders)",
                emptyTable: `
                    <div class="flex flex-col items-center py-8">
                        <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m13-8V4a1 1 0 10-2 0v1m-1 0H7m5 0V4a1 1 0 10-2 0v1m2 5.5V11a1 1 0 10-2 0v.5M9 11v.5a1 1 0 11-2 0V11m0 0V9a1 1 0 112 0v2M7 19h10"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Archived Orders</h3>
                        <p class="text-gray-500 text-center">Orders that have been paid for more than 1 minute will appear here automatically.</p>
                    </div>
                `,
                zeroRecords: `
                    <div class="flex flex-col items-center py-8">
                        <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <p class="text-lg font-medium text-gray-900">No matching archived orders found</p>
                        <p class="text-sm text-gray-500">Try adjusting your search terms</p>
                    </div>
                `,
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4"<"mb-2 sm:mb-0"l><"mb-2 sm:mb-0"f>>rtip',
            initComplete: function() {
                // Custom styling for search input
                $('.dataTables_filter input').addClass('px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500');
                $('.dataTables_filter label').addClass('text-sm font-medium text-gray-700');
                
                // Custom styling for length menu
                $('.dataTables_length select').addClass('px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500');
                $('.dataTables_length label').addClass('text-sm font-medium text-gray-700');
                
                console.log('Archived Orders DataTable initialized successfully');
            }
        });
    }
});