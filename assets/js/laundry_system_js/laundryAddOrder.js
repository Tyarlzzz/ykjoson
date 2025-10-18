const fullNameInput = document.getElementById('fullname');
const phoneInput = document.getElementById('phone_number');
const addressInput = document.getElementById('address');
const rushButton = document.getElementById('is_rushed');
const barongqty = document.getElementById('barong-qty');
const gownsqty = document.getElementById('gowns-qty');
const curtains = document.getElementById('curtains-qty');
const comforter = document.getElementById('comforter-qty');
const rushCheckbox = document.getElementById('rushOrderCheckbox');

// Suggestion dropdown
const suggestionBox = document.createElement('div');
suggestionBox.classList.add('absolute', 'bg-white', 'border', 'rounded', 'shadow', 'mt-1');
suggestionBox.style.zIndex = "1000";
fullNameInput.parentNode.style.position = "relative";
fullNameInput.parentNode.appendChild(suggestionBox);

fullNameInput.addEventListener('input', function() {
    const query = this.value.trim();
    if (query.length < 2) {
        suggestionBox.innerHTML = '';
        return;
    }

    fetch(`search_customer.php?term=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(data => {
            suggestionBox.innerHTML = '';
            data.forEach(customer => {
                const item = document.createElement('div');
                item.classList.add('p-2', 'cursor-pointer', 'hover:bg-gray-200');
                item.textContent = customer.fullname;

                item.addEventListener('click', () => {
                      console.log("Selected customer:", customer);

                      // Fill the inputs
                      fullNameInput.value = customer.fullname;
                      phoneInput.value = customer.phone_number;
                      addressInput.value = customer.address;

                      // 👇 PUT THEM HERE - Update the summary directly
                      document.getElementById('summary-name').textContent = customer.fullname;
                      document.getElementById('summary-phone').textContent = customer.phone_number;
                      document.getElementById('summary-address').textContent = customer.address;

                      // Trigger input events so summary updates
                      fullNameInput.dispatchEvent(new Event('input'));
                      phoneInput.dispatchEvent(new Event('input'));
                      addressInput.dispatchEvent(new Event('input'));

                      // Add hidden customer_id
                      let hiddenId = document.getElementById('customer_id');
                      if (!hiddenId) {
                          hiddenId = document.createElement('input');
                          hiddenId.type = 'hidden';
                          hiddenId.name = 'customer_id';
                          hiddenId.id = 'customer_id';
                          fullNameInput.form.appendChild(hiddenId);
                      }
                      hiddenId.value = customer.customer_id;

                      // Collapse suggestion box
                      suggestionBox.innerHTML = '';
                      suggestionBox.style.display = 'none';
                  });

                suggestionBox.appendChild(item);
            });
        });
});


// Smart initialization: Read from DOM if values exist (edit mode), otherwise start at 0 (add mode)
function getInitialQuantity(itemName) {
  const element = document.getElementById(`${itemName}-qty`);
  if (element && element.textContent.trim() !== '') {
    return parseInt(element.textContent) || 0;
  }
  return 0;
}

const quantities = {
  tops: getInitialQuantity('tops'),
  bottoms: getInitialQuantity('bottoms'),
  undies: getInitialQuantity('undies'),
  socks: getInitialQuantity('socks'),
  towels: getInitialQuantity('towels'),
  beds: getInitialQuantity('beds'),
  gowns: getInitialQuantity('gowns'),
  barong: getInitialQuantity('barong'),
  curtains: getInitialQuantity('curtains'),
  comforter: getInitialQuantity('comforter')
};

const ClothesLabels = {
  tops: 'Tops',
  bottoms: 'Bottoms',
  undies: 'Undies',
  socks: 'Socks',
  towels: 'Towels',
  beds: 'Bedsheets',
  gowns: 'Gowns',
  barong: 'Barong',
  curtains: 'Curtains',
  comforter: 'Comforter'
};

// Function to update button state based on fullName input
function updateButtonState() {
  const isNameEmpty = fullNameInput.value.trim() === '';
  
  const hasBarong = quantities.barong > 0 || quantities.gowns > 0 || quantities.curtains > 0 || quantities.comforter > 0;
  
  const shouldDisable = isNameEmpty || hasBarong;
  rushButton.disabled = shouldDisable;

  if (shouldDisable) {
    rushButton.classList.remove('bg-red-500', 'bg-red-500');
    rushButton.classList.add('bg-gray-500', 'hover:bg-gray-600');
    rushCheckbox.checked = false;
  }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  // Initialize displays with current quantities
  Object.keys(quantities).forEach(clothes => {
    updateDisplay(clothes);
  });
  
  updateButtonState();
  updateSummary();
});

fullNameInput.addEventListener('input', updateButtonState);

rushButton.addEventListener('click', function(e) {
  e.preventDefault();
  if (this.disabled) return; 

  // Toggle between gray and yellow/red (supports both color schemes)
  const isCurrentlyGray = this.classList.contains('bg-gray-500');
  
  if (isCurrentlyGray) {
    this.classList.remove('bg-gray-500', 'hover:bg-gray-600');
    this.classList.add('bg-red-500', 'hover:bg-red-600');
  } else {
    this.classList.remove('bg-red-500', 'hover:bg-red-600', 'bg-red-500', 'hover:bg-red-600');
    this.classList.add('bg-gray-500', 'hover:bg-gray-600');
  }
  
  const isRushActive = !this.classList.contains('bg-gray-500');
  rushCheckbox.checked = isRushActive;
});

function increaseQty(clothes) {
  quantities[clothes]++;
  updateDisplay(clothes);
  updateSummary();
  updateButtonState();

  if (clothes === 'barong') {
    console.log('Barong qty increased to:', quantities.barong);
  }
}

function decreaseQty(clothes) {
  if (quantities[clothes] > 0) {
    quantities[clothes]--;
    updateDisplay(clothes);
    updateSummary();
    updateButtonState();
  }
}

// manual edits (called on blur)
function handleQtyChange(clothes) {
  const qtyElement = document.getElementById(clothes + "-qty");
  if (qtyElement) {
    let newValue = parseInt(qtyElement.innerText.trim()) || 0;
    if (newValue >= 0) {
      quantities[clothes] = newValue; 
      updateSummary(); 
      updateButtonState();
    } else {
      // Revert to previous value if negative or invalid
      qtyElement.innerText = quantities[clothes];
    }
  }
}

function updateDisplay(clothes) {
  document.getElementById(`${clothes}-qty`).textContent = quantities[clothes];
  document.getElementById(`${clothes}-qty-input`).value = quantities[clothes];
}

function updateSummary() {
  // Update brand summary
  let clothesHTML = '';
  let hasClothes = false;

  Object.keys(quantities).forEach(clothes => {
    if (quantities[clothes] > 0) {
      clothesHTML += `
          <div class="flex justify-between items-center">
            <span class="text-gray-600">${ClothesLabels[clothes]}</span>
            <span class="font-semibold text-gray-800">${quantities[clothes]}</span>
          </div>
        `;
      hasClothes = true;
    }
  });

  document.getElementById('clothes-summary').innerHTML = hasClothes ? clothesHTML : '<p class="italic text-gray-500">No items selected</p>';

  // Calculate total
  const total = Object.values(quantities).reduce((sum, qty) => sum + qty, 0);
  document.getElementById('total-items').textContent = total;
}

// Update summary when input fields change
document.getElementById('fullname').addEventListener('input', function (e) {
  document.getElementById('summary-name').textContent = e.target.value || '-';
});

document.getElementById('phone_number').addEventListener('input', function (e) {
  document.getElementById('summary-phone').textContent = e.target.value || '-';
});

document.getElementById('address').addEventListener('input', function (e) {
  document.getElementById('summary-address').textContent = e.target.value || '-';
});

document.getElementById('note').addEventListener('input', function (e) {
  document.getElementById('summary-notes').textContent = e.target.value || 'No notes';
  document.getElementById('summary-notes').classList.toggle('italic', !e.target.value);
});

function cancelOrder() {
  if (confirm('Are you sure you want to cancel this order?')) {
    // Reset form
    document.getElementById('orderForm').reset();

    // Reset quantities
    Object.keys(quantities).forEach(key => {
      quantities[key] = 0;
      updateDisplay(key);
    });

    // Reset summary
    document.getElementById('summary-name').textContent = '-';
    document.getElementById('summary-phone').textContent = '-';
    document.getElementById('summary-address').textContent = '-';
    document.getElementById('summary-notes').textContent = 'No notes';
    document.getElementById('summary-notes').classList.add('italic');

    updateSummary();
  }
}

// Form validation before submitting
document.getElementById('orderForm').addEventListener('submit', function(e) {
  const fullName = document.getElementById('fullname').value.trim();
  const phoneNumber = document.getElementById('phone_number').value.trim();
  const address = document.getElementById('address').value.trim();

  // Validation if empty
  if (!fullName || !phoneNumber || !address) {
    e.preventDefault();
    alert('Please fill in all required fields (Name, Phone Number, and Address)');
    return false;
  }

  const total = Object.values(quantities).reduce((sum, qty) => sum + qty, 0);
  if (total === 0) {
    e.preventDefault();
    alert('Please select at least one type of clothing');
    return false;
  }

  const isRushOrder = document.getElementById('rushOrderCheckbox').checked;
  if (isRushOrder) {
    const confirmRush = confirm('Rush order selected. This may incur additional fees.');
    if (!confirmRush) {
      e.preventDefault();
      return false;
    }
  }

  // Submit when form is valid
  return true;
});