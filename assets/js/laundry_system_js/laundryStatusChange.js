// statusUpdate.js - Simplified version with validation
// Global debug function to test button clicking manually
window.testPaymentConfirm = function(orderId) {
    console.log('=== MANUAL TEST OF PAYMENT CONFIRM ===');
    console.log('Testing payment confirmation for order:', orderId);
    const button = document.getElementById('confirmPaymentBtn_' + orderId);
    if (button) {
        console.log('Button found:', button);
        console.log('Button style:', window.getComputedStyle(button));
        console.log('Button parent:', button.parentElement);
        button.click();
    } else {
        console.log('Button not found with ID: confirmPaymentBtn_' + orderId);
    }
};

window.testDirectStatusUpdate = function(orderId) {
    console.log('=== MANUAL TEST OF DIRECT STATUS UPDATE ===');
    updateOrderStatus(orderId, 'Paid');
};

// Helper function to get status button colors
function getStatusButtonColor(status) {
    const colorMap = {
        'On Hold': 'bg-yellow-500',
        'On Wash': 'bg-blue-500',
        'On Dry': 'bg-orange-500',
        'On Fold': 'bg-purple-500',
        'For Delivery': 'bg-indigo-500',
        'Delivered': 'bg-green-500',
        'Paid': 'bg-gray-500'
    };
    return colorMap[status] || 'bg-gray-500';
}

document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('laundryStatusModal');
    const closeBtn = document.getElementById('closeLaundryModal');
    const statusContainer = document.getElementById('statusOptionsContainer');
    
    // PHP Print URL
    const PHP_PRINT_URL = '../Receipt/printReceipt.php';
    
    // All possible statuses with their styling
    const allStatuses = {
        'On Wash': { bgColor: 'bg-[#D1EBF7]', textColor: 'text-[#0E74D3]' },
        'On Dry': { bgColor: 'bg-[#F7DED1]', textColor: 'text-[#D33F0E]' },
        'On Fold': { bgColor: 'bg-[#E6D1F7]', textColor: 'text-[#C60ED3]' },
        'For Delivery': { bgColor: 'bg-[#F7F6D1]', textColor: 'text-[#D3C30E]' },
        'Delivered': { bgColor: 'bg-[#D1F7EA]', textColor: 'text-[#17CF93]' },
        'Paid': { bgColor: 'bg-green-600', textColor: 'text-white' }
    };
    
    // Function to print receipt using PHP
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
    
    // Function to show weight input modal
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
                            Clothes Weight (kg) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="clothesWeight" step="0.01" min="0" value="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" 
                            placeholder="0.00" required>
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
                
                if (clothesWeight === 0 && comforter_curtainsWeight === 0) {
                    Swal.showValidationMessage('Please enter at least clothes weight');
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
                updateOrderStatusWithWeights(orderId, 'For Delivery', result.value);
            } else {
                modal.classList.add('hidden');
            }
        });
    }
    
    // Function to update order status with weight data
    function updateOrderStatusWithWeights(orderId, newStatus, weights) {
        console.log('=== UPDATING STATUS WITH WEIGHTS ===');
        console.log('Order ID:', orderId);
        console.log('Weights:', weights);
        
        // Removed loading dialog - status update happens instantly

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

                // Wait for database to commit before printing
                setTimeout(() => {
                    console.log('Calling printReceipt with order_id:', orderId);
                    
                    printReceipt(orderId)
                        .then(printData => {
                            console.log('Print successful:', printData);
                            
                            // Build breakdown HTML
                            let breakdownHTML = '';
                            if (data.breakdown) {
                                breakdownHTML = `
                                    <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                        <p class="font-bold mb-2 text-blue-800">Price Breakdown:</p>
                                        ${data.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(data.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(data.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${data.breakdown.barong_qty} pcs × ₱${parseFloat(data.breakdown.barong_price).toFixed(2)}): ₱${parseFloat(data.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.gown_total > 0 ? `<p class="text-gray-700">• Gown (${data.breakdown.gown_qty} pcs × ₱${parseFloat(data.breakdown.gown_price).toFixed(2)}): ₱${parseFloat(data.breakdown.gown_total).toFixed(2)}</p>` : ''}
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
                            
                            // Build breakdown HTML for error case too
                            let breakdownHTML = '';
                            if (data.breakdown) {
                                breakdownHTML = `
                                    <div class="bg-blue-50 p-3 rounded-lg text-sm mt-3 border border-blue-200">
                                        <p class="font-bold mb-2 text-blue-800">💰 Price Breakdown:</p>
                                        ${data.breakdown.clothes_total > 0 ? `<p class="text-gray-700">• Clothes: ₱${parseFloat(data.breakdown.clothes_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.comforter_total > 0 ? `<p class="text-gray-700">• Comforter/Curtains: ₱${parseFloat(data.breakdown.comforter_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.barong_total > 0 ? `<p class="text-gray-700">• Barong (${data.breakdown.barong_qty} pcs × ₱250): ₱${parseFloat(data.breakdown.barong_total).toFixed(2)}</p>` : ''}
                                        ${data.breakdown.gown_total > 0 ? `<p class="text-gray-700">• Gown (${data.breakdown.gown_qty} pcs × ₱500): ₱${parseFloat(data.breakdown.gown_total).toFixed(2)}</p>` : ''}
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
    
    // Function to update order status (without weights)
    function updateOrderStatus(orderId, newStatus) {
        console.log('=== UPDATE ORDER STATUS CALLED ===');
        console.log('Order ID:', orderId);
        console.log('New Status:', newStatus);
        console.log('Call stack:', new Error().stack);
        console.log('Current time:', new Date().toISOString());
        
        // Removed loading dialog - status update happens instantly

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
            return response.text().then(text => {
                console.log('Response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                }
            });
        })
        .then(data => {
            console.log('=== RESPONSE RECEIVED ===');
            console.log('Response data:', data);
            console.log('data.success:', data.success);
            console.log('newStatus:', newStatus);
            
            if (data.success) {
                console.log('✅ Status update successful!');
                
                // Update the status button text immediately
                const statusButton = document.querySelector(`[data-order-id="${orderId}"]`);
                if (statusButton) {
                    console.log('Found status button, updating immediately...');
                    statusButton.textContent = newStatus;
                    statusButton.setAttribute('data-current-status', newStatus);
                    
                    // Update button color based on status
                    statusButton.className = statusButton.className.replace(/bg-\w+-\d+/, getStatusButtonColor(newStatus));
                } else {
                    console.log('❌ Could not find status button with data-order-id:', orderId);
                }
                
                // Always hide modal and reload, with or without SweetAlert
                const closeAndReload = () => {
                    console.log('Closing modal and reloading page...');
                    modal.classList.add('hidden');
                    
                    // For "Paid" status, refresh immediately without delay
                    if (newStatus === 'Paid') {
                        console.log('Status changed to Paid - refreshing page immediately');
                        location.reload();
                    } else {
                        setTimeout(() => location.reload(), 100);
                    }
                };
                
                try {
                    console.log('Checking if newStatus is Paid:', newStatus === 'Paid');
                    
                    // For "Paid" status, show simple success and reload
                    if (newStatus === 'Paid') {
                        console.log('🎯 PAID STATUS - STARTING RELOAD PROCESS');
                        
                        // Hide modal
                        modal.classList.add('hidden');
                        
                        // Show a simple alert instead of SweetAlert
                        alert('Order marked as Paid! Page will refresh.');
                        
                        // Force reload
                        console.log('Forcing page reload...');
                        window.location.reload(true);
                        return;
                    }
                    
                    // For other statuses, use SweetAlert
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Order status updated to ' + newStatus,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        willClose: closeAndReload
                    });
                } catch (sweetAlertError) {
                    console.error('SweetAlert error:', sweetAlertError);
                    // Fallback: just close and reload without SweetAlert
                    alert(data.message || 'Order status updated to ' + newStatus);
                    closeAndReload();
                }
            } else {
                console.error('Backend returned success:false', data);
                throw new Error(data.message || 'Failed to update status');
            }
        })
        .catch(error => {
            console.error('Update error:', error);
            console.error('Error stack:', error.stack);
            
            try {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to update order status',
                    icon: 'error',
                    confirmButtonColor: '#3085d6'
                });
            } catch (sweetAlertError) {
                console.error('SweetAlert error in catch:', sweetAlertError);
                alert('Error: ' + (error.message || 'Failed to update order status'));
            }
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
                    break;
                case 'Delivered':
                    // Create a manual payment confirmation that only triggers when YOU click it
                    const paymentMessage = document.createElement('div');
                    paymentMessage.className = 'text-center py-6';
                    paymentMessage.innerHTML = `
                        <svg class="w-16 h-16 mx-auto mb-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="font-[Outfit] text-xl font-semibold text-gray-800">Order Delivered!</p>
                        <p class="font-[Outfit] text-gray-600 mt-2">When customer pays, click the button below to mark as paid.</p>
                    `;
                    statusContainer.appendChild(paymentMessage);
                    
                    // Create ONLY a "Mark as Paid" button (no automatic confirmation)
                    const paymentButtonContainer = document.createElement('div');
                    paymentButtonContainer.className = 'flex justify-center mt-4';
                    
                    const markPaidBtn = document.createElement('button');
                    markPaidBtn.className = 'font-[Outfit] bg-green-500 text-white px-6 py-3 rounded-md hover:bg-green-600';
                    markPaidBtn.textContent = '✓ Mark as Paid';
                    
                    markPaidBtn.addEventListener('click', function() {
                        console.log('BUTTON CLICKED - REFRESHING IMMEDIATELY');
                        
                        // Send request in background (don't wait)
                        fetch('updateStatus.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ order_id: parseInt(orderId), status: 'Paid' })
                        });
                        
                        // REFRESH IMMEDIATELY - NO WAITING
                        window.location.reload();
                    });
                    
                    paymentButtonContainer.appendChild(markPaidBtn);
                    statusContainer.appendChild(paymentButtonContainer);
                    
                    modal.classList.remove('hidden');
                    return;
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
            
            availableStatuses.forEach(status => {
                const statusInfo = allStatuses[status];
                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;
                
                button.addEventListener('click', function() {
                    console.log('=== STATUS BUTTON CLICKED ===');
                    console.log('Button clicked for status:', status);
                    console.log('Order ID:', orderId);
                    console.log('Current time:', new Date().toISOString());
                    
                    if (status === 'For Delivery') {
                        console.log('Opening weight input modal...');
                        modal.classList.add('hidden');
                        showWeightInputModal(orderId, customerName, customerAddress, customerPhone, totalQuantity);
                    } else {
                        console.log('Calling updateOrderStatus...');
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
