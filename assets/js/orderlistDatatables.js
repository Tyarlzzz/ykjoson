$(document).ready(function() {

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
                targets: 1,
                orderable: false,
                searchable: false
            }
        ]
    });

    var currentPath = window.location.pathname;
    var isGasSystem = currentPath.includes('/Gas/');

    function applyStatusColors() {
        $('#orderlistTable tbody tr').each(function() {
            var statusBox = $(this).find('td').eq(6);
            var statusText = statusBox.text().trim();

            if (isGasSystem) {
                if(statusText === 'delivered') {
                    statusBox.addClass('bg-[#D1F7EA] text-[#17CF93] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'pending') {
                    statusBox.addClass('bg-[#F7F6D1] text-[#D3C30E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'borrowed') {
                    statusBox.addClass('bg-[#F7DED1] text-[#D33F0E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                } else if(statusText === 'returned') {
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
                }
            }
        });
    }

    applyStatusColors();

    var allSelected = false;
    $('.select-btn').on('click', function() {
        allSelected = !allSelected;
        
        var rows = table.rows({ 'search': 'applied' }).nodes();
        
        $('input[type="checkbox"]', rows).prop('checked', allSelected);
        
        if(allSelected) {
            $(this).text('Deselect all');
            $(this).parent().removeClass('bg-gray-200').addClass('bg-green-500 text-white');
        } else {
            $(this).text('Select all');
            $(this).parent().removeClass('bg-green-500 text-white').addClass('bg-gray-200');
        }
    });

    $('#orderlistTable tbody').on('change', 'input[type="checkbox"]', function() {
        var rows = table.rows({ 'search': 'applied' }).nodes();
        var totalCheckboxes = $('input[type="checkbox"]', rows).length;
        var checkedCheckboxes = $('input[type="checkbox"]:checked', rows).length;
        
        if(checkedCheckboxes < totalCheckboxes) {
            allSelected = false;
            $('.select-btn').text('Select All');
            $('.select-btn').parent().removeClass('bg-green-500 text-white').addClass('bg-gray-200');
        } else if(checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
            allSelected = true;
            $('.select-btn').text('Deselect All');
            $('.select-btn').parent().removeClass('bg-gray-200').addClass('bg-green-500 text-white');
        }
    });

    $('#customSearch').on('keyup', function() {
        table.search(this.value).draw();
        applyStatusColors(); 
    });

    $('#statusFilter').on('change', function() {
        table.column(6).search(this.value).draw();
        applyStatusColors(); 
    });
    
    $('#collapseBtn').on('click', function() {
        setTimeout(function() {
            $(window).trigger('resize');
        }, 500);
    });
    
    $(window).on('resize', function() {
        table.columns.adjust().draw();
    });
});