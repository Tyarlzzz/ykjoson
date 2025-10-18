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
            columnDefs: [
                {
                    targets: 1, // make name column searchable by visible text
                    render: function (data, type, row) {
                        return type === 'filter' || type === 'search' ? $(data).text() : data;
                    }
                },
                {
                    targets: 1,
                    orderable: false,
                    searchable: true
                }
            ]
        });
    } 
    
    var currentPath = window.location.pathname;
    var isGasSystem = currentPath.includes('/Gas/');

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

    function applyFilters() {
    table.columns().search('').draw();
    
    if (currentSearchTerm) {
        table.columns([1, 2, 3]).search(currentSearchTerm, true, false).draw();
    }
    
    // Apply status filter to column 5
    if (currentStatusFilter) {
        table.column(5).search('^' + currentStatusFilter + '$', true, false).draw();
    }
    
    applyStatusColors();
    }

    applyStatusColors();

    // Check for status parameter in URL and apply filter
    var urlParams = new URLSearchParams(window.location.search);
    var statusParam = urlParams.get('status');
    
    if (statusParam) {
        $('#statusFilter').val(statusParam);
        table.column(5).search(statusParam).draw();
        applyStatusColors();
    }

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
        applyStatusColors();
    });

    $('#statusFilter').on('change', function() {
        var selectedStatus = this.value;
        table.column(5).search(selectedStatus).draw();
        applyStatusColors();
        
        // Update URL without page reload
        var newUrl = selectedStatus 
            ? window.location.pathname + '?status=' + encodeURIComponent(selectedStatus)
            : window.location.pathname;
        window.history.pushState({}, '', newUrl);
    });
    
});