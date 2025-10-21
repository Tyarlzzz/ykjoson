$(document).ready(function() {

    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#orderlistTable')) {
        var table = $('#orderlistTable').DataTable();
    } else {
        var table = $('#orderlistTable').DataTable({
            paging: false,
            info: false, 
            searching: true,
            scrollCollapse: true,
            dom: 't', 
            ordering: false,
            autoWidth: false,
            language: {
                emptyTable: "No orders found. Create your first order to get started!",
                zeroRecords: "No orders match your search criteria."
            },
            columnDefs: [
                { targets: 0, orderable: false, searchable: false }, // # column
                { targets: 1, orderable: false, searchable: true },  // Name column
                { targets: 2, orderable: false, searchable: true },  // Location column
                { targets: 3, orderable: false, searchable: false }, // Phone column
                { targets: 4, orderable: false, searchable: false }, // Qty column
                { targets: 5, orderable: false, searchable: true },  // Status column
                {
                    targets: 1, // make name column searchable by visible text
                    render: function (data, type, row) {
                        return type === 'filter' || type === 'search' ? $(data).text() : data;
                    }
                }
            ]
        });
    } 
    
    var currentPath = window.location.pathname;
    var isGasSystem = currentPath.includes('/Gas/');
    var currentorderliststatusFilter = ''; // Track current filter

    function applyStatusColors() {
        $('#orderlistTable tbody tr').each(function() {
            var statusBox = $(this).find('td').eq(5);
            var statusText = statusBox.text().trim();

            if (isGasSystem) {
                if(statusText === 'Delivered') {
                    statusBox.addClass('bg-[#D1F7EA] text-[#17CF93] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'Pending') {
                    statusBox.addClass('bg-[#F7F6D1] text-[#D3C30E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'Borrowed') {
                    statusBox.addClass('bg-[#F7DED1] text-[#D33F0E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'Returned') {
                    statusBox.addClass('bg-[#E6D1F7] text-[#C60ED3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'Paid') {
                    statusBox.addClass('bg-[#D1F0F7] text-[#0E8AD3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                }
                
            } else {
                if(statusText === 'Delivered') {
                    statusBox.addClass('bg-[#D1F7EA] text-[#17CF93] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'For Delivery') {
                    statusBox.addClass('bg-[#F7F6D1] text-[#D3C30E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'On Hold') {
                    statusBox.addClass('bg-[#F9FFAB] text-[#1F2016] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'On Wash') {
                    statusBox.addClass('bg-[#D1EBF7] text-[#0E74D3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'On Dry') {
                    statusBox.addClass('bg-[#F7DED1] text-[#D33F0E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'On Fold') {
                    statusBox.addClass('bg-[#E6D1F7] text-[#C60ED3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'Paid') {
                    statusBox.addClass('bg-green-600 text-white font-semibold font-[Outfit] rounded-lg p-4 text-center');
                }
            }
        });
    }

    // Custom filter for rushed and status filtering
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        var row = $(table.row(dataIndex).node());
        var idCell = row.find('td').eq(0);
        var statusCell = row.find('td').eq(5);
        var isRushed = idCell.hasClass('font-bold') && idCell.hasClass('text-red-600');
        var statusText = statusCell.text().trim();

        // If rushed filter is active, only show rushed orders
        if (currentorderliststatusFilter === 'rushed') {
            return isRushed;
        }
        
        // If a status filter is active, show orders with that status
        if (currentorderliststatusFilter && currentorderliststatusFilter !== 'rushed') {
            return statusText === currentorderliststatusFilter;
        }
        
        // If no filter, show all
        return true;
    });

    applyStatusColors();

    // Check for URL parameters and apply filter
    var urlParams = new URLSearchParams(window.location.search);
    var statusParam = urlParams.get('status');
    var rushedParam = urlParams.get('is_rushed');
    
    if (rushedParam === '1') {
        currentorderliststatusFilter = 'rushed';
        $('#orderliststatusFilter').val('rushed');
        table.draw();
    } else if (statusParam) {
        currentorderliststatusFilter = statusParam;
        $('#orderliststatusFilter').val(statusParam);
        table.draw();
    }

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
        applyStatusColors();
    });

    $('#orderliststatusFilter').on('change', function() {
        currentorderliststatusFilter = this.value;
        table.draw();
        applyStatusColors();
        
        // Update URL without page reload
        var newUrl = currentorderliststatusFilter === 'rushed'
            ? window.location.pathname + '?is_rushed=1'
            : currentorderliststatusFilter
                ? window.location.pathname + '?status=' + encodeURIComponent(currentorderliststatusFilter)
                : window.location.pathname;
        window.history.pushState({}, '', newUrl);
    });
    
});