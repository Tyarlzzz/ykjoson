document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('gasStatusModal');
    const closeBtn = document.getElementById('closeGasModal');
    const statusContainer = document.getElementById('statusOptionsContainer');

setTimeout(() => {
    fetch('/ykjoson/Gas/process_auto_archive.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.archived_count > 0) {
                console.log(`✓ Archived ${data.archived_count} order(s) after 70 seconds`);
                // Silently reload the page so user sees archive updated
                location.reload();
            }
        })
        .catch(error => {
            console.log('Archive check completed');
        });
}, 70000); // Wait 70 seconds before checking (60 second delay + 10 second buffer)

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

    // Function to show delivery confirmation modal with existing brand data
    function showDeliveryConfirmationModal(orderId, customerName, customerAddress, customerPhone, total_quantity, petronQty, econoQty, seagasQty) {
        Swal.fire({
            title: 'Confirm Delivery - Order #' + orderId,
            html: `
                <div class="text-left">
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <p class="text-sm font-['Outfit'] font-semibold text-gray-600">Customer Name:</p>
                                <p class="text-base font-['Switzer'] text-gray-800">${customerName}</p>
                            </div>
                            <div>
                                <p class="text-sm font-['Outfit'] font-semibold text-gray-600">Phone Number:</p>
                                <p class="text-base font-['Switzer'] text-gray-800">${customerPhone}</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <p class="text-sm font-['Outfit'] font-semibold text-gray-600">Address:</p>
                            <p class="text-base font-['Switzer'] text-gray-800">${customerAddress}</p>
                        </div>
                        <div>
                            <p class="text-sm font-['Outfit'] font-semibold text-gray-600">Total Quantity:</p>
                            <p class="text-base font-['Switzer'] text-gray-800">${total_quantity}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 mb-3">
                        <p class="text-lg font-['Outfit'] font-semibold text-gray-700 mb-3">Ordered Brand/s:</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-blue-50 rounded-md">
                            <span class="font-['Outfit'] font-medium text-gray-700">Petron</span>
                            <span class="font-['Switzer'] font-semibold text-gray-900">${petronQty} units</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-green-50 rounded-md">
                            <span class="font-['Outfit'] font-medium text-gray-700">Econo</span>
                            <span class="font-['Switzer'] font-semibold text-gray-900">${econoQty} units</span>
                        </div>
                        
                        <div class="flex justify-between items-center p-3 bg-purple-50 rounded-md">
                            <span class="font-['Outfit'] font-medium text-gray-700">SeaGas</span>
                            <span class="font-['Switzer'] font-semibold text-gray-900">${seagasQty} units</span>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <p class="text-sm font-['Outfit'] text-yellow-800">
                            <strong>Note:</strong> Please verify the quantities before confirming delivery.
                        </p>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Confirm Delivery',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#19B900',
            cancelButtonColor: '#FF1D21'
        }).then((result) => {
            if (result.isConfirmed) {
                // Update status with existing brand quantities
                const brandData = {
                    petronQty: parseInt(petronQty),
                    econoQty: parseInt(econoQty),
                    seagasQty: parseInt(seagasQty),
                    totalQty: parseInt(total_quantity)
                };
                updateOrderStatusWithBrands(orderId, 'Delivered', brandData);
            } else {
                // If cancelled, close the status modal
                modal.classList.add('hidden');
            }
        });
    }

    // Function to update order status with brand quantities
    function updateOrderStatusWithBrands(orderId, newStatus, brandData) {
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

        fetch('/ykjoson/Gas/updateStatus.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: parseInt(orderId),
                status: newStatus,
                brand_quantities: brandData
            })
        })
            .then(response => {
                return response.text().then(text => {
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

    // Function to update order status (for simple status changes)
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

        fetch('/ykjoson/Gas/updateStatus.php', {
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
                return response.text().then(text => {
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

    // Status transitions for gas orders
    const statusTransitions = {
        'Pending': ['Borrowed', 'Delivered'],
        'Borrowed': ['Returned'],
        'Returned': [],
        'Delivered': ['Paid'],
        'Paid': []
    };

    // Open modal and populate with appropriate status options
    document.querySelectorAll('.openGasStatusModal').forEach(button => {
        button.addEventListener('click', function () {
            const currentStatus = this.getAttribute('data-current-status');
            const orderId = this.getAttribute('data-order-id');
            const customerName = this.getAttribute('data-customer-name');
            const customerAddress = this.getAttribute('data-customer-address');
            const customerPhone = this.getAttribute('data-customer-phone');
            const total_quantity = this.getAttribute('data-quantity');
            const petronQty = this.getAttribute('data-petron-qty') || 0;
            const econoQty = this.getAttribute('data-econo-qty') || 0;
            const seagasQty = this.getAttribute('data-seagas-qty') || 0;

            statusContainer.innerHTML = '';

            // Get available statuses based on current status
            let availableStatuses = statusTransitions[currentStatus] || [];

            // Show success message for Paid orders (final status)
            if (currentStatus === 'Paid') {
                const successMessage = document.createElement('div');
                successMessage.className = 'text-center py-6';
                successMessage.innerHTML = `
                    <svg class="w-16 h-16 mx-auto mb-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="font-[Outfit] text-xl font-semibold text-gray-800">Payment Completed!</p>
                    <p class="font-[Outfit] text-gray-600 mt-2">This order has been fully paid.</p>
                `;
                statusContainer.appendChild(successMessage);
                modal.classList.remove('hidden');
                return;
            }

            // Show success message for returned orders
            if (currentStatus === 'Returned') {
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

            // Create buttons for available statuses
            availableStatuses.forEach(status => {
                const statusInfo = allStatuses[status];
                if (!statusInfo) return;

                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md font-semibold text-lg`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;

                button.addEventListener('click', function () {
                    // If changing to Delivered, show the confirmation modal with existing brand data
                    if (status === 'Delivered') {
                        modal.classList.add('hidden');
                        showDeliveryConfirmationModal(orderId, customerName, customerAddress, customerPhone, total_quantity, petronQty, econoQty, seagasQty);
                    } else {
                        // For other status changes, update directly
                        updateOrderStatus(orderId, status);
                    }
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