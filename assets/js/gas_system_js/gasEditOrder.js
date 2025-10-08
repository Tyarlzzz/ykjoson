// Initialize quantities from data passed from PHP
const quantities = {
  petron: window.initialOrderData.petronQty,
  econo: window.initialOrderData.econoQty,
  seagas: window.initialOrderData.seagasQty
};

const brandLabels = {
  petron: 'Petron',
  econo: 'Econo',
  seagas: 'SeaGas'
};

function increaseQty(brand) {
  quantities[brand]++;
  updateDisplay(brand);
  updateSummary();
}

function decreaseQty(brand) {
  if (quantities[brand] > 0) {
    quantities[brand]--;
    updateDisplay(brand);
    updateSummary();
  }
}

function updateDisplay(brand) {
  document.getElementById(`${brand}-qty`).textContent = quantities[brand];
  // Update hidden input for form submission
  document.getElementById(`${brand}-qty-input`).value = quantities[brand];
}

function updateSummary() {
  // Update brand summary
  let brandHTML = '';
  let hasBrands = false;

  Object.keys(quantities).forEach(brand => {
    if (quantities[brand] > 0) {
      brandHTML += `
          <div class="flex justify-between items-center">
            <span class="text-gray-600">${brandLabels[brand]}</span>
            <span class="font-semibold text-gray-800">${quantities[brand]}</span>
          </div>
        `;
      hasBrands = true;
    }
  });

  document.getElementById('brand-summary').innerHTML = hasBrands ? brandHTML : '<p class="italic text-gray-500">No items selected</p>';

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

function cancelEdit() {
  if (confirm('Are you sure you want to cancel editing? All changes will be lost.')) {
    window.location.href = 'orderList.php';
  }
}

function deleteOrder() {
  if (confirm('Are you sure you want to delete this order? This action cannot be undone.')) {
    window.location.href = 'destroy.php?id=' + window.initialOrderData.id;
  }
}

// Form validation before submitting
document.getElementById('orderForm').addEventListener('submit', function (e) {
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
    alert('Please select at least one gas cylinder');
    return false;
  }

  // Submit when form is valid
  return true;
});

// Initialize summary on page load
updateSummary();