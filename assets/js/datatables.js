        $(document).ready(function() {
            // Skip DataTables initialization for archived page since it has different structure
            var currentPath = window.location.pathname;
            if (currentPath.includes('archived.php')) {
                return; // Don't initialize DataTables for archived page
            }

            var table = $('#ordersTable').DataTable({
                paging: false,
                info: false, 
                searching: true,
                scrollCollapse: true,
                dom: 't', 
                ordering: false,
                autoWidth: false
            });

            // determine if the path is for gas or laundry system
            var isGasSystem = currentPath.includes('/Gas/');

            // Skip status styling and other functionality for archived page
            if (currentPath.includes('archived.php')) {
                return; // Don't run the rest of the script for archived page
            }

            $('#ordersTable tbody tr').each(function() {
                var statusBox = $(this).find('td:last');
                var statusText = statusBox.text().trim().toLowerCase();
                var idBox = $(this).find('td:first');

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
                    if(statusText === 'delivered') {
                        statusBox.addClass('bg-[#D1F7EA] text-[#17CF93] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    } else if(statusText === 'for delivery') {
                        statusBox.addClass('bg-[#F7F6D1] text-[#D3C30E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    } else if(statusText === 'on hold') {
                        statusBox.addClass('bg-[#F9FFAB] text-[#1F2016] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    } else if(statusText === 'on wash') {
                        statusBox.addClass('bg-[#D1EBF7] text-[#0E74D3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    } else if(statusText === 'on dry') {
                        statusBox.addClass('bg-[#F7DED1] text-[#D33F0E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    } else if(statusText === 'on fold') {
                        statusBox.addClass('bg-[#E6D1F7] text-[#C60ED3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
                    }
                }
            })

            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            $('#statusFilter').on('change', function() {
                table.column(5).search(this.value).draw();
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