$(document).ready(function () {

    var table = $('#GordersTable').DataTable({
        paging: false,
        info: false,
        searching: true,
        scrollCollapse: true,
        dom: 't',
        ordering: false,
        autoWidth: false
    });

    $('#GordersTable tbody tr').each(function () {
        var statusBox = $(this).find('td:last');
        var statusText = statusBox.text().trim();
        var idBox = $(this).find('td:first');

        if (statusText === 'Delivered') {
            statusBox.addClass('bg-[#D1F7EA] text-[#17CF93] font-semibold font-[Outfit] rounded-lg p-4 text-center');
        } else if (statusText === 'Pending') {
            statusBox.addClass('bg-[#F7F6D1] text-[#D3C30E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
        } else if (statusText === 'Borrowed') {
            statusBox.addClass('bg-[#F7DED1] text-[#D33F0E] font-semibold font-[Outfit] rounded-lg p-4 text-center');
        } else if (statusText === 'Returned') {
            statusBox.addClass('bg-[#E6D1F7] text-[#C60ED3] font-semibold font-[Outfit] rounded-lg p-4 text-center');
        }

    })

    $('#GcustomSearch').on('keyup', function () {
        table.search(this.value).draw();
    });

    $('#GstatusFilter').on('change', function () {
        table.column(5).search(this.value).draw();
    });

    $('#collapseBtn').on('click', function () {
        setTimeout(function () {
            $(window).trigger('resize');
        }, 500);
    });

    $(window).on('resize', function () {
        table.columns.adjust().draw();
    });
});