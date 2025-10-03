// Set current date - enUS format (ex: April 01, 2001)
const options = { year: 'numeric', month: 'long', day: '2-digit' };
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);

const fullNameInput = document.getElementById('fullName');
const rushButton = document.getElementById('rushButton');
const rushCheckbox = document.getElementById('rushOrderCheckbox'); // New: Reference to checkbox
// Function to update button state based on fullName input
function updateButtonState() {
  const isNameEmpty = fullNameInput.value.trim() === '';
  rushButton.disabled = isNameEmpty;

  if (isNameEmpty) {
    rushButton.classList.remove('bg-red-500', 'hover:bg-red-600');
    rushButton.classList.add('bg-gray-500', 'hover:bg-gray-600');

    rushCheckbox.checked = false;
  }
}

updateButtonState();
fullNameInput.addEventListener('input', updateButtonState);
rushButton.addEventListener('click', function(e) {
  e.preventDefault();
  if (this.disabled) return; 

  this.classList.toggle('bg-gray-500');
  this.classList.toggle('bg-red-500');
  this.classList.toggle('hover:bg-gray-600');
  this.classList.toggle('hover:bg-red-600');
  
  const isRushActive = this.classList.contains('bg-red-500');
  rushCheckbox.checked = isRushActive;
});


const quantities = {
  tops: 0,
  bottoms: 0,
  undies: 0,
  socks: 0,
  towels: 0,
  beds: 0,
  gowns: 0,
  barong: 0,
  curtains: 0,
  comforter: 0
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

function increaseQty(clothes) {
  quantities[clothes]++;
  updateDisplay(clothes);
  updateSummary();
}

function decreaseQty(clothes) {
  if (quantities[clothes] > 0) {
    quantities[clothes]--;
    updateDisplay(clothes);
    updateSummary();
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
    } else {
      // Revert to previous value if negative or invalid
      qtyElement.innerText = quantities[clothes];
    }
  }
}

function initDisplays() {
  Object.keys(quantities).forEach(clothes => {
    updateDisplay(clothes);
  });
}

// Call initDisplays()
document.addEventListener('DOMContentLoaded', initDisplays);
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
document.getElementById('fullName').addEventListener('input', function (e) {
  document.getElementById('summary-name').textContent = e.target.value || '-';
});

document.getElementById('phoneNumber').addEventListener('input', function (e) {
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
  const fullName = document.getElementById('fullName').value.trim();
  const phoneNumber = document.getElementById('phoneNumber').value.trim();
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

// Initialize summary on page load
updateSummary();