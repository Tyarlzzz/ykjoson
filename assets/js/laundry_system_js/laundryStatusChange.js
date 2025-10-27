// statusUpdate.js

// Check for pending archives on page load - but ONLY after 70 seconds
// This ensures we don't archive too early
setTimeout(() => {
    fetch('/ykjoson/Laundry/process_auto_archive.php')
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

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('laundryStatusModal');
    const closeBtn = document.getElementById('closeLaundryModal');
    const statusContainer = document.getElementById('statusOptionsContainer');
    
    const PHP_PRINT_URL = '../Receipt/printReceipt.php';
    
    const allStatuses = {
        'On Wash': { bgColor: 'bg-[#D1EBF7]', textColor: 'text-[#0E74D3]' },
        'On Dry': { bgColor: 'bg-[#F7DED1]', textColor: 'text-[#D33F0E]' },
        'On Fold': { bgColor: 'bg-[#E6D1F7]', textColor: 'text-[#C60ED3]' },
        'For Delivery': { bgColor: 'bg-[#F7F6D1]', textColor: 'text-[#D3C30E]' },
        'Delivered': { bgColor: 'bg-[#D1F7EA]', textColor: 'text-[#17CF93]' },
        'Paid': { bgColor: 'bg-green-600', textColor: 'text-white' }
    };

    
    function printReceipt(orderId) {
        console.log('=== PRINTING RECEIPT ===');
        console.log('Order ID:', orderId);
        
        return fetch(PHP_PRINT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: parseInt(orderId) })
        })
        .then(response => {
            console.log('Print response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Print response:', data);
            if (!data.success) {
                throw new Error(data.message || 'Failed to print receipt');
            }
            return data;
        })
        .catch(error => {
            console.error('Print error:', error);
            throw error;
        });
    }
    
    function showWeightInputModal(orderId, customerName, customerAddress, customerPhone, total_quantity) {
        console.log('Opening weight modal for order:', orderId);
        
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

                    <div class="mb-3">
                        <label class="text-start text-lg font-medium text-gray-700 mb-1 font-['Switzer']">
                            Clothes Weight (kg)
                        </label>
                        <input type="number" id="clothesWeight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="0.00 (leave 0 if none)">
                    </div>
                    
                    <div>
                        <label class="text-start text-lg font-medium text-gray-700 mb-1 font-['Switzer']">
                            Comforter/Curtains Weight (kg)
                        </label>
                        <input type="number" id="comforter_curtainsWeight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="0.00 (leave 0 if none)">
                        <p class="text-xs text-gray-500 mt-1">Enter 0 if order has no Comforter/Curtains items</p>
                    </div>
                </div>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Confirm & Print',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#19B900',
            cancelButtonColor: '#FF1D21',
            preConfirm: () => {
                const clothesWeight = parseFloat(document.getElementById('clothesWeight').value) || 0;
                const comforter_curtainsWeight = parseFloat(document.getElementById('comforter_curtainsWeight').value) || 0;
                const totalWeight = clothesWeight + comforter_curtainsWeight;
                
                // Allow proceeding even if all weights are 0 (for barong/gown only orders)
                // No validation needed - user can proceed with 0 weights
                
                return {
                    clothesWeight: clothesWeight,
                    comforter_curtainsWeight: comforter_curtainsWeight,
                    totalWeight: totalWeight
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateOrderStatusWithWeights(orderId, 'For Delivery', result.value);
            } else {
                modal.classList.add('hidden');
            }
        });
    }
    
    function updateOrderStatusWithWeights(orderId, newStatus, weights) {
        console.log('=== UPDATING STATUS WITH WEIGHTS ===');
        console.log('Order ID:', orderId);
        console.log('Weights:', weights);

        fetch('/ykjoson/Laundry/updateStatusWithWeights.php', {
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
            console.log('Update response status:', response.status);
            return response.text().then(text => {
                console.log('Raw response:', text.substring(0, 200));
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            console.log('Update response data:', data);
            
            if (data.success) {
                console.log('Status updated successfully, now printing...');
                
                Swal.fire({
                    title: 'Printing Receipt...',
                    text: 'Order updated successfully. Printing receipt now...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                setTimeout(() => {
                    console.log('Calling printReceipt with order_id:', orderId);
                    
                    printReceipt(orderId)
                        .then(printData => {
                            console.log('Print successful:', printData);
                            
                            let breakdownHTML = '';
                            if (data.breakdown) {
                                breakdownHTML = `
                                    <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                        <p class="font-bold mb-2 text-blue-800">Price Breakdown:</p>
                                        ${data.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(data.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(data.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${data.breakdown.barong_qty} pcs × ₱${parseFloat(data.breakdown.barong_price).toFixed(2)}): ₱${parseFloat(data.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.gowns_total > 0 ? `<p class="text-gray-700">• Gown (${data.breakdown.gowns_qty} pcs × ₱${parseFloat(data.breakdown.gowns_price).toFixed(2)}): ₱${parseFloat(data.breakdown.gowns_total).toFixed(2)}</p>` : ''}
                                    </div>
                                `;
                            }
                            
                            Swal.fire({
                                title: 'Success!',
                                html: `
                                    <div class="text-left">
                                        <p class="mb-2">Order status updated to <strong>For Delivery</strong></p>
                                        <p class="mb-2 text-green-600">✓ Receipt printed successfully</p>
                                        <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                            <p><strong>Clothes:</strong> ${weights.clothesWeight} kg</p>
                                            <p><strong>Comforter:</strong> ${weights.comforter_curtainsWeight} kg</p>
                                            <p class="mt-2 font-bold border-t pt-2"><strong>Total Weight:</strong> ${weights.totalWeight} kg</p>
                                        </div>
                                        ${breakdownHTML}
                                        <p class="mt-3 text-2xl font-bold text-green-700">TOTAL: ₱${parseFloat(data.total_price || 0).toFixed(2)}</p>
                                    </div>
                                `,
                                icon: 'success',
                                timer: 4000,
                                showConfirmButton: false
                            }).then(() => {
                                modal.classList.add('hidden');
                                location.reload();
                            });
                        })
                        .catch(printError => {
                            console.error('Print failed:', printError);
                            
                            let breakdownHTML = '';
                            if (data.breakdown) {
                                breakdownHTML = `
                                    <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                        <p class="font-bold mb-2 text-blue-800">Price Breakdown:</p>
                                        ${data.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(data.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(data.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${data.breakdown.barong_qty} pcs × ₱${parseFloat(data.breakdown.barong_price).toFixed(2)}): ₱${parseFloat(data.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.gowns_total > 0 ? `<p class="text-gray-700">• Gown (${data.breakdown.gowns_qty} pcs × ₱${parseFloat(data.breakdown.gowns_price).toFixed(2)}): ₱${parseFloat(data.breakdown.gowns_total).toFixed(2)}</p>` : ''}
                                    </div>
                                `;
                            }
                            
                            Swal.fire({
                                title: 'Partial Success',
                                html: `
                                    <div class="text-left">
                                        <p class="mb-2 text-green-600">✓ Order status updated successfully</p>
                                        <p class="mb-2 text-red-600">✗ Failed to print receipt</p>
                                        <p class="text-sm text-gray-600 mb-3">Error: ${printError.message}</p>
                                        <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                            <p><strong>Clothes:</strong> ${weights.clothesWeight} kg</p>
                                            <p><strong>Comforter:</strong> ${weights.comforter_curtainsWeight} kg</p>
                                            <p class="mt-2 font-bold border-t pt-2"><strong>Total Weight:</strong> ${weights.totalWeight} kg</p>
                                        </div>
                                        ${breakdownHTML}
                                        <p class="mt-3 text-xl font-bold text-green-700">TOTAL: ₱${parseFloat(data.total_price || 0).toFixed(2)}</p>
                                        <p class="mt-3 text-sm text-gray-600">You can manually print the receipt later.</p>
                                    </div>
                                `,
                                icon: 'warning',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#3085d6'
                            }).then(() => {
                                modal.classList.add('hidden');
                                location.reload();
                            });
                        });
                }, 800);
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

        fetch('/ykjoson/Laundry/updateStatus.php', {
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
        .then(response => response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        }))
        .then(data => {
            if (data.success) {
                // If status is "Paid", show countdown timer instead of immediate reload
                if (newStatus === 'Paid') {
                    let secondsLeft = 60;
                    Swal.fire({
                        title: '✓ Payment Confirmed!',
                        html: `
                            <div class="text-center">
                                <p class="text-lg font-semibold text-gray-700 mb-4">Order will auto-archive in...</p>
                                <div class="text-6xl font-bold text-red-600 mb-4" id="countdown">${secondsLeft}s</div>
                                <p class="text-sm text-gray-600">The order will automatically move to the archive in 60 seconds</p>
                            </div>
                        `,
                        icon: 'success',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: true,
                        confirmButtonText: 'OK, Got it!',
                        confirmButtonColor: '#10B981',
                        didOpen: () => {
                            // Start countdown
                            const countdownInterval = setInterval(() => {
                                secondsLeft--;
                                const countdownEl = document.getElementById('countdown');
                                if (countdownEl) {
                                    countdownEl.textContent = secondsLeft + 's';
                                }
                                
                                // Stop at 0
                                if (secondsLeft <= 0) {
                                    clearInterval(countdownInterval);
                                }
                            }, 1000);
                        }
                    }).then(() => {
                        modal.classList.add('hidden');
                        location.reload();
                    });
                } else {
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
                }
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

    
    // Function to fetch order total price for "Delivered" status
    function fetchOrderTotalPrice(orderId) {
        return fetch('getOrderTotal.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order_id: parseInt(orderId) })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.total_price || 0;
            }
            return 0;
        })
        .catch(error => {
            console.error('Error fetching total price:', error);
            return 0;
        });
    }
    
    document.querySelectorAll('.openLaundryStatusModal').forEach(button => {
        button.addEventListener('click', function() {
            const currentStatus = this.getAttribute('data-current-status');
            const orderId = this.getAttribute('data-order-id');
            const customerName = this.getAttribute('data-customer-name');
            const customerAddress = this.getAttribute('data-customer-address');
            const customerPhone = this.getAttribute('data-customer-phone');
            const totalQuantity = this.getAttribute('data-quantity') || '0';
            
            console.log('Modal opened - Order ID:', orderId, 'Status:', currentStatus);
            
            statusContainer.innerHTML = '';
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
                    
                    // Add Delivered button first
                    availableStatuses.forEach(status => {
                        const statusInfo = allStatuses[status];
                        const button = document.createElement('button');
                        button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                        button.setAttribute('data-status', status);
                        button.setAttribute('data-order-id', orderId);
                        button.textContent = status;
                        
                        button.addEventListener('click', function() {
                            updateOrderStatus(orderId, status);
                        });
                        
                        statusContainer.appendChild(button);
                    });
                    
                    // Add Reprint button below Delivered button
                    const reprintButton = document.createElement('button');
                    reprintButton.className = 'font-[Outfit] w-full bg-blue-500 hover:bg-blue-600 text-white py-4 rounded-md mt-2 transition-colors';
                    reprintButton.textContent = 'Reprint Receipt';
                    
                    reprintButton.addEventListener('click', function() {
                        // Close modal first
                        modal.classList.add('hidden');
                        
                        // Show printing loading
                        Swal.fire({
                            title: 'Reprinting Receipt...',
                            text: 'Please wait...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // First fetch order data for breakdown
                        fetch('getOrderTotal.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order_id: parseInt(orderId) })
                        })
                        .then(response => response.json())
                        .then(orderData => {
                            // Now call print function
                            return printReceipt(orderId)
                                .then(printData => {
                                    console.log('Reprint successful:', printData);
                                    
                                    let breakdownHTML = '';
                                    if (orderData.breakdown) {
                                        breakdownHTML = `
                                            <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                                <p class="font-bold mb-2 text-blue-800">Price Breakdown:</p>
                                                ${orderData.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(orderData.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(orderData.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${orderData.breakdown.barong_qty} pcs × ₱${parseFloat(orderData.breakdown.barong_price).toFixed(2)}): ₱${parseFloat(orderData.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.gowns_total > 0 ? `<p class="text-gray-700">• Gown (${orderData.breakdown.gowns_qty} pcs × ₱${parseFloat(orderData.breakdown.gowns_price).toFixed(2)}): ₱${parseFloat(orderData.breakdown.gowns_total).toFixed(2)}</p>` : ''}
                                            </div>
                                        `;
                                    }
                                    
                                    const clothesWeight = orderData.clothes_weight || 0;
                                    const comforterWeight = orderData.comforter_curtains_weight || 0;
                                    const totalWeight = orderData.total_weight || 0;
                                    
                                    Swal.fire({
                                        title: 'Receipt Reprinted!',
                                        html: `
                                            <div class="text-left">
                                                <p class="mb-2 text-green-600">✓ Receipt printed successfully</p>
                                                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                                    <p><strong>Clothes:</strong> ${clothesWeight} kg</p>
                                                    <p><strong>Comforter:</strong> ${comforterWeight} kg</p>
                                                    <p class="mt-2 font-bold border-t pt-2"><strong>Total Weight:</strong> ${totalWeight} kg</p>
                                                </div>
                                                ${breakdownHTML}
                                                <p class="mt-3 text-2xl font-bold text-green-700">TOTAL: ₱${parseFloat(orderData.total_price || 0).toFixed(2)}</p>
                                            </div>
                                        `,
                                        icon: 'success',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#10B981'
                                    });
                                })
                                .catch(printError => {
                                    console.error('Reprint failed:', printError);
                                    
                                    let breakdownHTML = '';
                                    if (orderData.breakdown) {
                                        breakdownHTML = `
                                            <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                                <p class="font-bold mb-2 text-blue-800">Price Breakdown:</p>
                                                ${orderData.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(orderData.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(orderData.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${orderData.breakdown.barong_qty} pcs × ₱${parseFloat(orderData.breakdown.barong_price).toFixed(2)}): ₱${parseFloat(orderData.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                                ${orderData.breakdown.gowns_total > 0 ? `<p class="text-gray-700">• Gown (${orderData.breakdown.gowns_qty} pcs × ₱${parseFloat(orderData.breakdown.gowns_price).toFixed(2)}): ₱${parseFloat(orderData.breakdown.gowns_total).toFixed(2)}</p>` : ''}
                                            </div>
                                        `;
                                    }
                                    
                                    const clothesWeight = orderData.clothes_weight || 0;
                                    const comforterWeight = orderData.comforter_curtains_weight || 0;
                                    const totalWeight = orderData.total_weight || 0;
                                    
                                    Swal.fire({
                                        title: 'Print Failed',
                                        html: `
                                            <div class="text-left">
                                                <p class="mb-2 text-red-600">✗ Failed to print receipt</p>
                                                <p class="text-sm text-gray-600 mb-3">Error: ${printError.message}</p>
                                                <div class="bg-gray-50 p-3 rounded-lg text-sm">
                                                    <p><strong>Clothes:</strong> ${clothesWeight} kg</p>
                                                    <p><strong>Comforter:</strong> ${comforterWeight} kg</p>
                                                    <p class="mt-2 font-bold border-t pt-2"><strong>Total Weight:</strong> ${totalWeight} kg</p>
                                                </div>
                                                ${breakdownHTML}
                                                <p class="mt-3 text-xl font-bold text-green-700">TOTAL: ₱${parseFloat(orderData.total_price || 0).toFixed(2)}</p>
                                            </div>
                                        `,
                                        icon: 'warning',
                                        confirmButtonText: 'OK',
                                        confirmButtonColor: '#3085d6'
                                    });
                                });
                        })
                        .catch(fetchError => {
                            console.error('Failed to fetch order data:', fetchError);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to fetch order details',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        });
                    });
                    
                    statusContainer.appendChild(reprintButton);
                    
                    modal.classList.remove('hidden');
                    return; // Exit early for For Delivery case
                    
                case 'Delivered':
                    // Fetch total price first, then display
                    fetchOrderTotalPrice(orderId).then(totalPrice => {
                        const confirmMessage = document.createElement('div');
                        confirmMessage.className = 'text-center py-6';
                        confirmMessage.innerHTML = `
                            <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="mt-4 bg-green-50 p-4 rounded-lg border-2 border-green-200">
                                <p class="font-[Outfit] text-3xl font-bold text-green-700">₱${parseFloat(totalPrice).toFixed(2)}</p>
                                <p class="font-[Outfit] text-sm text-gray-600 mt-1">Total Amount</p>
                            </div>
                            <p class="font-[Outfit] text-xl font-semibold text-gray-800 mt-4">Confirm payment?</p>
                            <p class="font-[Outfit] text-gray-600 mt-2">Are you sure you want to confirm payment for this order?</p>
                        `;
                        statusContainer.appendChild(confirmMessage);
                        
                        // Add the Paid button
                        availableStatuses = ['Paid'];
                        availableStatuses.forEach(status => {
                            const statusInfo = allStatuses[status];
                            const button = document.createElement('button');
                            button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md mt-4`;
                            button.setAttribute('data-status', status);
                            button.setAttribute('data-order-id', orderId);
                            button.textContent = status;
                            
                            button.addEventListener('click', function() {
                                updateOrderStatus(orderId, status);
                            });
                            
                            statusContainer.appendChild(button);
                        });
                    });
                    
                    modal.classList.remove('hidden');
                    return; // Exit early for Delivered case
                    
                case 'Paid':
                    const successMessage = document.createElement('div');
                    successMessage.className = 'text-center py-6';
                    successMessage.innerHTML = `
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-[Outfit] text-xl font-semibold text-gray-800">Order already paid!</p>
                        <p class="font-[Outfit] text-gray-600 mt-2">This order has been successfully completed.</p>
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
                    `;
                    statusContainer.appendChild(errorMessage);
                    modal.classList.remove('hidden');
                    return;
            }
            
            // For all other statuses (On Hold, On Wash, On Dry, On Fold, For Delivery)
            availableStatuses.forEach(status => {
                const statusInfo = allStatuses[status];
                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;
                
                button.addEventListener('click', function() {
                    if (status === 'For Delivery') {
                        modal.classList.add('hidden');
                        showWeightInputModal(orderId, customerName, customerAddress, customerPhone, totalQuantity);
                    } else {
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