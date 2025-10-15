    // JavaScript for modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('priceModal');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        const updateBtn = document.getElementById('updateBtn');
        const newPriceInput = document.getElementById('newPriceInput');
        const modalTitle = document.getElementById('modalTitle');
        const modalCurrentPrice = document.getElementById('modalCurrentPrice');
        const modalLabel = document.getElementById('modalLabel');
        const updateButtons = document.querySelectorAll('.update-btn');

        // Open modal and populate data
        updateButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const type = this.dataset.type;
                const title = this.dataset.title;
                const image = this.dataset.image;
                const price = this.dataset.price;
                const isWeight = this.dataset.isWeight === 'true';

                document.getElementById('modalImage').src = image;
                document.getElementById('modalImage').alt = title;
                modalTitle.textContent = title;

                // Dynamic current value display
                if (isWeight) {
                    modalCurrentPrice.innerHTML = `Current Minimum Weight: <strong>${price}</strong> kilos`;
                    modalLabel.textContent = 'New Minimum Weight (kilos)';
                    newPriceInput.placeholder = 'Enter new weight';
                } else {
                    // For prices: Determine unit based on type (same logic as PHP)
                    let unit = 'kilo';
                    if (type === 'gowns' || type === 'barong') {
                        unit = 'piece';
                    }
                    modalCurrentPrice.innerHTML = `Current Price: ₱<strong>${price}</strong>/${unit}`;
                    modalLabel.textContent = 'New Price';
                    newPriceInput.placeholder = 'Enter new price';
                }

                newPriceInput.value = ''; // Clear input
                newPriceInput.dataset.type = type; // Store type for update logic
                newPriceInput.dataset.isWeight = isWeight; // Store for update logic

                modal.classList.remove('hidden');
            });
        });

        // Close modal functions
        function closeModal() {
            modal.classList.add('hidden');
            newPriceInput.value = '';
        }

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Click outside to close
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        // Update button (for now, just alert the new value; integrate with store.php later)
        updateBtn.addEventListener('click', function() {
            const newValue = newPriceInput.value;
            const type = newPriceInput.dataset.type;
            const isWeight = newPriceInput.dataset.isWeight === 'true';
            if (newValue && newValue > 0) {
                const what = isWeight ? 'weight' : 'price';
                // For now, show alert; later, send to store.php via form submit
                alert(`Updating ${type} ${what} to ${newValue}. (Implement save logic here)`);
                closeModal();
         
                // document.querySelector(`#current-price-${type}`).textContent = newValue;
            } else {
                alert('Please enter a valid value.');
            }
        });
    });

// sample code kapag may store.php na
// // JavaScript for modal functionality
// document.addEventListener('DOMContentLoaded', function() {
//     const modal = document.getElementById('priceModal');
//     const closeBtn = document.getElementById('closeModal');
//     const cancelBtn = document.getElementById('cancelBtn');
//     const updateBtn = document.getElementById('updateBtn');
//     const newPriceInput = document.getElementById('newPriceInput');
//     const modalTitle = document.getElementById('modalTitle');
//     const modalCurrentPrice = document.getElementById('modalCurrentPrice');
//     const modalLabel = document.getElementById('modalLabel');
//     const updateButtons = document.querySelectorAll('.update-btn');

//     // Open modal and populate data
//     updateButtons.forEach(btn => {
//         btn.addEventListener('click', function() {
//             const type = this.dataset.type;
//             const title = this.dataset.title;
//             const image = this.dataset.image;
//             const price = this.dataset.price;
//             const isWeight = this.dataset.isWeight === 'true';

//             document.getElementById('modalImage').src = image;
//             document.getElementById('modalImage').alt = title;
//             modalTitle.textContent = title;

//             // Dynamic current value display
//             if (isWeight) {
//                 modalCurrentPrice.innerHTML = `Current Minimum Weight: <strong>${price}</strong> kilos`;
//                 modalLabel.textContent = 'New Minimum Weight (kilos)';
//                 newPriceInput.placeholder = 'Enter new weight';
//             } else {
//                 // For prices: Determine unit based on type (same logic as PHP)
//                 let unit = 'kilo';
//                 if (type === 'gowns' || type === 'barong') {
//                     unit = 'piece';
//                 }
//                 modalCurrentPrice.innerHTML = `Current Price: ₱<strong>${price}</strong>/${unit}`;
//                 modalLabel.textContent = 'New Price';
//                 newPriceInput.placeholder = 'Enter new price';
//             }

//             newPriceInput.value = ''; // Clear input
//             newPriceInput.dataset.type = type; // Store type for update logic
//             newPriceInput.dataset.isWeight = isWeight; // Store for update logic

//             modal.classList.remove('hidden');
//         });
//     });

//     // Close modal functions
//     function closeModal() {
//         modal.classList.add('hidden');
//         newPriceInput.value = '';
//     }

//     closeBtn.addEventListener('click', closeModal);
//     cancelBtn.addEventListener('click', closeModal);

//     // Click outside to close
//     modal.addEventListener('click', function(e) {
//         if (e.target === modal) {
//             closeModal();
//         }
//     });

//     // Update button: Send data to store.php
//     updateBtn.addEventListener('click', function() {
//         const newValue = newPriceInput.value;
//         const type = newPriceInput.dataset.type;
//         const isWeight = newPriceInput.dataset.isWeight === 'true';
        
//         if (newValue && newValue > 0) {
//             // Prepare data to send
//             const data = {
//                 type: type,
//                 isWeight: isWeight,
//                 newValue: newValue
//             };
            
//             fetch('store.php', {  // Assuming store.php is in the same directory
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json'  // Or 'application/x-www-form-urlencoded' if store.php expects form data
//                 },
//                 body: JSON.stringify(data)  // Send as JSON; adjust if needed
//             })
//             .then(response => {
//                 if (!response.ok) {
//                     throw new Error('Network response was not ok');
//                 }
//                 return response.json();  // Assuming store.php returns JSON
//             })
//             .then(data => {
//                 if (data.success) {  // Check for success based on your store.php response
//                     alert('Update successful!');
//                     closeModal();
                    
//                     // Optionally update the UI on the page
//                     // For example, if you have an element like #current-price-{type}
//                     const priceElement = document.querySelector(`#current-price-${type}`);
//                     if (priceElement) {
//                         priceElement.textContent = newValue;  // Update the displayed value
//                     }
                    
//                     // You might want to refresh the page or fetch updated data here
//                 } else {
//                     alert('Update failed: ' + data.message);  // Assuming the response has a 'message' field
//                 }
//             })
//             .catch(error => {
//                 console.error('Error updating data:', error);
//                 alert('An error occurred while updating. Please try again.');
//             });
//         } else {
//             alert('Please enter a valid value greater than 0.');
//         }
//     });
// });

    

// Example in store.php
    // <?php
    // // Process the request...
    // if ($success) {
    //     echo json_encode(['success' => true]);
    // } else {
    //     echo json_encode(['success' => false, 'message' => 'Error message']);
    // }
    // ?>
    