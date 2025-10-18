// statusUpdate.js - Handles order status updates with weight input for delivery
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('laundryStatusModal');
    const closeBtn = document.getElementById('closeLaundryModal');
    const statusContainer = document.getElementById('statusOptionsContainer');
    
    // All possible statuses with their styling
    const allStatuses = {
        'On Wash': {
            bgColor: 'bg-[#D1EBF7]',
            textColor: 'text-[#0E74D3]'
        },
        'On Dry': {
            bgColor: 'bg-[#F7DED1]',
            textColor: 'text-[#D33F0E]'
        },
        'On Fold': {
            bgColor: 'bg-[#E6D1F7]',
            textColor: 'text-[#C60ED3]'
        },
        'For Delivery': {
            bgColor: 'bg-[#F7F6D1]',
            textColor: 'text-[#D3C30E]'
        },
        'Delivered': {
            bgColor: 'bg-[#D1F7EA]',
            textColor: 'text-[#17CF93]'
        },
        'Paid': {
            bgColor: 'bg-green-600',
            textColor: 'text-white'
        },
    };
    
    // Function to show weight input modal
    function showWeightInputModal(orderId, customerName, customerAddress, customerPhone, total_quantity) {
        Swal.fire({
            title: 'Order Number: ' + orderId,
            html: `
                <div>
                    <div class="flex flex-row justify-between mb-4">
                        <p class="text-xl font-['Switzer'] text-gray-700"><strong class="font-['Outfit']">Name:</strong> ${customerName}</p>
                        <p class="text-xl font-['Switzer'] text-gray-700"><strong class="font-['Outfit']">Phone:</strong> ${customerPhone}</p>
                    </div>
                    <p class="text-xl mb-4 text-start font-['Switzer'] text-gray-700"><strong class="font-['Outfit']">Address:</strong> ${customerAddress}</p>
                    <p class="text-xl text-end mb-4 font-['Switzer'] text-gray-700"><strong class="font-['Outfit']">Total Pcs:</strong> ${total_quantity}</p>
                    
                    <div class="flex">
                        <p class="text-lg font-['Switzer'] text-gray-700 mb-4">Weights (kg)</p>
                    </div>

                    <div>
                        <label class="text-start text-lg font-medium text-gray-700 mb-1 font-['Switzer']">Clothes Weight (kg)</label>
                        <input type="number" id="clothesWeight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="0.00">
                    </div>
                    
                    <div>
                        <label class="text-start text-lg font-medium text-gray-700 mb-1 font-['Switzer']">Comforter/Curtains Weight (kg)</label>
                        <input type="number" id="comforter_curtainsWeight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="0.00">
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#19B900',
            cancelButtonColor: '#FF1D21',
            preConfirm: () => {
                const clothesWeight = parseFloat(document.getElementById('clothesWeight').value) || 0;
                const comforter_curtainsWeight = parseFloat(document.getElementById('comforter_curtainsWeight').value) || 0;
                
                const totalWeight = clothesWeight + comforter_curtainsWeight;
                
                if (totalWeight === 0) {
                    Swal.showValidationMessage('Please enter at least one weight value');
                    return false;
                }
                
                return {
                    clothesWeight: clothesWeight,
                    comforter_curtainsWeight: comforter_curtainsWeight,
                    totalWeight: totalWeight
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Update status with weight data
                updateOrderStatusWithWeights(orderId, 'For Delivery', result.value);
            } else {
                // If cancelled, close the status modal
                modal.classList.add('hidden');
            }
        });
    }
    
    // Function to update order status with weight data
    function updateOrderStatusWithWeights(orderId, newStatus, weights) {
        Swal.fire({
            title: 'Updating...',
            text: 'Please wait while we update the order status and weights',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('updateStatusWithWeights.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: parseInt(orderId),
                status: newStatus,
                clothes_weight: weights.clothesWeight,
                comforter_curtains_weight: weights.comforter_curtainsWeight,
                total_weight: weights.totalWeight
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
                    html: `
                        <div class="text-left">
                            <p class="mb-2">Order status updated to <strong>For Delivery</strong></p>
                            <div class="bg-gray-50 p-3 rounded-lg text-lg">
                                <p><strong>Clothes:</strong> ${weights.clothesWeight} kg</p>
                                <p><strong>Comforter:</strong> ${weights.comforter_curtainsWeight} kg</p>
                                <p class="mt-2 font-bold border-t pt-2"><strong>Total Weight:</strong> ${weights.totalWeight} kg</p>
                                <p class="mt-2 font-bold text-green-700"><strong>Total Price:</strong> ₱${data.total_price.toFixed(2)}</p>
                            </div>
                        </div>
                    `,
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
    
    // Function to update order status (without weights)
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
    
    // Open modal and populate with appropriate status options
    document.querySelectorAll('.openLaundryStatusModal').forEach(button => {
        button.addEventListener('click', function() {
            const currentStatus = this.getAttribute('data-current-status');
            const orderId = this.getAttribute('data-order-id');
            const customerName = this.getAttribute('data-customer-name');
            const customerAddress = this.getAttribute('data-customer-address');
            const customerPhone = this.getAttribute('data-customer-phone');
            
            // Clear previous options
            statusContainer.innerHTML = '';
            
            // Determine which statuses to show based on current status
            let availableStatuses = [];
            
            switch(currentStatus) {
                case 'On Hold':
                    availableStatuses = ['On Wash', 'On Dry', 'On Fold'];
                    break;
                case 'On Wash':
                    availableStatuses = ['On Dry', 'On Fold'];
                    break;
                case 'On Dry':
                    availableStatuses = ['On Wash', 'On Fold'];
                    break;
                case 'On Fold':
                    availableStatuses = ['For Delivery'];
                    break;
                case 'For Delivery':
                    availableStatuses = ['Delivered'];
                    break;
                case 'Delivered':
                    const confirmMessage = document.createElement('div');
                    confirmMessage.className = 'text-center py-6';
                    confirmMessage.innerHTML = `
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-[Outfit] text-xl font-semibold text-gray-800">Confirm payment?</p>
                        <p class="font-[Outfit] text-gray-600 mt-2">Are you sure you want to confirm payment for this order?</p>
                    `;
                    statusContainer.appendChild(confirmMessage);
                    availableStatuses = ['Paid'];
                    break;
                case 'Paid':
                    const successMessage = document.createElement('div');
                    successMessage.className = 'text-center py-6';
                    successMessage.innerHTML = `
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-[Outfit] text-xl font-semibold text-gray-800">Order already paid!</p>
                        <p class="font-[Outfit] text-gray-600 mt-2">This order has been successfully delivered.</p>
                    `;
                    statusContainer.appendChild(successMessage);
                    modal.classList.remove('hidden');
                    return;
                default:
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
            
            availableStatuses.forEach(status => {
                const statusInfo = allStatuses[status];
                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;
                
                // Add click event to update status
                button.addEventListener('click', function() {
                    // Check if status is "For Delivery" - show weight input modal
                    if (status === 'For Delivery') {
                        modal.classList.add('hidden'); // Close status modal first
                        showWeightInputModal(orderId, customerName, customerAddress, customerPhone);
                    } else {
                        // Regular status update for other statuses
                        updateOrderStatus(orderId, status);
                    }
                });
                
                statusContainer.appendChild(button);
            });
            
            modal.classList.remove('hidden');
        });
    });
    
    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});