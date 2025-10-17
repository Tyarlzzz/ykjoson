// Your separate JS file

const fullNameInput = document.getElementById('fullname');
const rushButton = document.getElementById('is_rushed');
const barongqty = document.getElementById('barong-qty');
const gownsqty = document.getElementById('gowns-qty');
const curtains = document.getElementById('curtains-qty');
const comforter = document.getElementById('comforter-qty');
const rushCheckbox = document.getElementById('rushOrderCheckbox'); // Reference to checkbox

// Initialize quantities object (still as const, but we'll mutate its properties later)
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

// Function to update button state based on fullName input
function updateButtonState() {
  const isNameEmpty = fullNameInput.value.trim() === '';
  
  const hasBarong = quantities.barong > 0 || quantities.gowns > 0 || quantities.curtains > 0 || quantities.comforter > 0;
  
  const shouldDisable = isNameEmpty || hasBarong;
  rushButton.disabled = shouldDisable;

  if (shouldDisable) {
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

// Manual edits (called on blur)
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

function initDisplays() {
  Object.keys(quantities).forEach(clothes => {
    updateDisplay(clothes);
  });
}

// Update display for a specific item
function updateDisplay(clothes) {
  document.getElementById(`${clothes}-qty`).textContent = quantities[clothes];
  document.getElementById(`${clothes}-qty-input`).value = quantities[clothes];
}

function updateSummary() {
  // Update clothes summary
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

// Event listeners for customer info
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
    document.getElementById('orderForm').reset();
    Object.keys(quantities).forEach(key => {
      quantities[key] = 0;
      updateDisplay(key);
    });
    document.getElementById('summary-name').textContent = '-';
    document.getElementById('summary-phone').textContent = '-';
    document.getElementById('summary-address').textContent = '-';
    document.getElementById('summary-notes').textContent = 'No notes';
    document.getElementById('summary-notes').classList.add('italic');
    updateSummary();
  }
}

document.getElementById('orderForm').addEventListener('submit', function(e) {
  const fullName = document.getElementById('fullname').value.trim();
  const phoneNumber = document.getElementById('phone_number').value.trim();
  const address = document.getElementById('address').value.trim();
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
  return true;
});

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
  // Initialize quantities from DOM elements (set by PHP)
  quantities.tops = parseInt(document.getElementById('tops-qty').textContent) || 0;
  quantities.bottoms = parseInt(document.getElementById('bottoms-qty').textContent) || 0;
  quantities.undies = parseInt(document.getElementById('undies-qty').textContent) || 0;
  quantities.socks = parseInt(document.getElementById('socks-qty').textContent) || 0;
  quantities.towels = parseInt(document.getElementById('towels-qty').textContent) || 0;
  quantities.beds = parseInt(document.getElementById('beds-qty').textContent) || 0;
  quantities.gowns = parseInt(document.getElementById('gowns-qty').textContent) || 0;
  quantities.barong = parseInt(document.getElementById('barong-qty').textContent) || 0;
  quantities.curtains = parseInt(document.getElementById('curtains-qty').textContent) || 0;
  quantities.comforter = parseInt(document.getElementById('comforter-qty').textContent) || 0;
  
  initDisplays();  // Now uses the updated quantities
  updateSummary();  // Update summary with initial values
  
  // Trigger initial updates for customer info summaries
  const fullNameEvent = new Event('input');
  document.getElementById('fullname').dispatchEvent(fullNameEvent);
  
  const phoneEvent = new Event('input');
  document.getElementById('phone_number').dispatchEvent(phoneEvent);
  
  const addressEvent = new Event('input');
  document.getElementById('address').dispatchEvent(addressEvent);
  
  const noteEvent = new Event('input');
  document.getElementById('note').dispatchEvent(noteEvent);
  
  updateButtonState();  // Ensure button state is correct on load
});
