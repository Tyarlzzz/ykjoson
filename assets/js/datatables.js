        $(document).ready(function() {

            var table = $('#ordersTable').DataTable({
                paging: false,
                info: false, 
                searching: true,
                scrollCollapse: true,
                dom: 't', 
                ordering: false,
                autoWidth: false
            });

            $('#ordersTable tbody tr').each(function() {
                var statusBox = $(this).find('td:last');
                var statusText = statusBox.text().trim();
                var idBox = $(this).find('td:first');

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