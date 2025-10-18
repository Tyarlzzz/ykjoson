document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('gasStatusModal');
    const closeBtn = document.getElementById('closeLaundryModal');
    const statusContainer = document.getElementById('statusOptionsContainer');

    // All possible statuses with their styling
    const allStatuses = {
        'Pending': {
            bgColor: 'bg-[#F7F6D1]',
            textColor: 'text-[#D3C30E]'
        },
        'Borrowed': {
            bgColor: 'bg-[#F7DED1]',
            textColor: 'text-[#D33F0E]'
        },
        'Returned': {
            bgColor: 'bg-[#E6D1F7]',
            textColor: 'text-[#C60ED3]'
        },
        'Delivered': {
            bgColor: 'bg-[#D1F7EA]',
            textColor: 'text-[#17CF93]'
        },
        'Paid': {
            bgColor: 'bg-[#D1F0F7]',
            textColor: 'text-[#0E8AD3]'
        }
    };

    // Status transitions for gas orders
    const statusTransitions = {
        'Pending': ['Borrowed', 'Delivered', 'Paid'],
        'Borrowed': ['Returned'],
        'Returned': [],
        'Delivered': []
    };

    // Function to update order status
    function updateOrderStatus(orderId, newStatus) {
        Swal.fire({
            title: 'Updating...',
            text: 'Please wait while we update the order status',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('updateStatus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: parseInt(orderId),
                status: newStatus
            })
        })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response ok:', response.ok);

            return response.text().then(text => {
                console.log('Raw response:', text);
                try {
                    const data = JSON.parse(text);
                    return data;
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Success!',
                    text: 'Order status updated to ' + newStatus,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    modal.classList.add('hidden');
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Update error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to update order status',
                icon: 'error',
                confirmButtonColor: '#3085d6'
            });
        });
    }

    // Open modal and populate with appropriate status options
    document.querySelectorAll('.openGasStatusModal').forEach(button => {
        button.addEventListener('click', function () {
            const currentStatus = this.getAttribute('data-current-status');
            const orderId = this.getAttribute('data-order-id');

            statusContainer.innerHTML = '';

            // available statuses based on current status
            let availableStatuses = statusTransitions[currentStatus] || [];

            if (currentStatus === 'Delivered') {
                // Show success message for delivered orders
                const successMessage = document.createElement('div');
                successMessage.className = 'text-center py-6';
                successMessage.innerHTML = `
                    <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-[Outfit] text-xl font-semibold text-gray-800">Delivery Successful!</p>
                    <p class="font-[Outfit] text-gray-600 mt-2">This order has been successfully delivered.</p>
                `;
                statusContainer.appendChild(successMessage);
                modal.classList.remove('hidden');
                return;
            }

            if (currentStatus === 'Returned') {
                // Show success message for delivered orders
                const successMessage = document.createElement('div');
                successMessage.className = 'text-center py-6';
                successMessage.innerHTML = `
                    <svg class="w-16 h-16 mx-auto mb-4 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-[Outfit] text-xl font-semibold text-gray-800">Gas Tank Returned!</p>
                    <p class="font-[Outfit] text-gray-600 mt-2">The borrowed tank has been returned.</p>
                `;
                statusContainer.appendChild(successMessage);
                modal.classList.remove('hidden');
                return;
            }

            if (!allStatuses.hasOwnProperty(currentStatus)) {
                // Show error message for unknown status
                const errorMessage = document.createElement('div');
                errorMessage.className = 'text-center py-6';
                errorMessage.innerHTML = `
                    <svg class="w-16 h-16 mx-auto mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-[Outfit] text-xl font-semibold text-gray-800">Error Getting Status</p>
                    <p class="font-[Outfit] text-gray-600 mt-2">Unable to retrieve the current status of this order.</p>
                `;
                statusContainer.appendChild(errorMessage);
                modal.classList.remove('hidden');
                return;
            }

            // buttons for available statuses
            availableStatuses.forEach(status => {
                const statusInfo = allStatuses[status];
                if (!statusInfo) return;
                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;

                button.addEventListener('click', function () {
                    updateOrderStatus(orderId, status);
                });

                statusContainer.appendChild(button);
            });

            modal.classList.remove('hidden');
        });
    });

    closeBtn.addEventListener('click', function () {
        modal.classList.add('hidden');
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});