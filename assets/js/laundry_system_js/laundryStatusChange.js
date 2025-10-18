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
        }
    };
    
    // Open modal and populate with appropriate status options
    document.querySelectorAll('.openLaundryStatusModal').forEach(button => {
        button.addEventListener('click', function() {
            const currentStatus = this.getAttribute('data-current-status');
            const orderId = this.getAttribute('data-order-id');
            
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
                    availableStatuses = ['For Delivery', 'Delivered'];
                    break;
                case 'For Delivery':
                    availableStatuses = ['Delivered'];
                    break;
                case 'Delivered':
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
                default:
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
                const button = document.createElement('button');
                button.className = `font-[Outfit] status-option w-full ${statusInfo.bgColor} ${statusInfo.textColor} py-4 rounded-md`;
                button.setAttribute('data-status', status);
                button.setAttribute('data-order-id', orderId);
                button.textContent = status;
                
                // Add click event to update status
                button.addEventListener('click', function() {
                    updateOrderStatus(orderId, status);
                });
                
                statusContainer.appendChild(button);
            });
            
            // Show modal
            modal.classList.remove('hidden');
        });
    });
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.add('hidden');
    });
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
});