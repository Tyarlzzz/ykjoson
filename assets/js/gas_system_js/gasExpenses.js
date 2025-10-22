const cardsList = document.getElementById('cardsList');
const weekTitle = document.getElementById('weekTitle');
const saveExpensesBtn = document.getElementById('saveExpensesBtn');
const cancelBtn = document.getElementById('cancelBtn');
const productList = document.querySelector('.productList');
const addBtn = document.getElementById('addProductBtn');
const resetMonthBtn = document.getElementById('createWeekBtn');

let currentEditingWeek = 1;
let weekExpenses = {};
const MAX_WEEKS = 4;

const monthlyCardContainer = document.createElement('div');
monthlyCardContainer.className =
  "flex font-['Outfit'] bg-red-100 border border-red-600 text-red-800 p-4 mb-6 rounded-lg shadow justify-between items-center";
monthlyCardContainer.innerHTML = `
  <h2 class="text-3xl font-bold">Total Monthly Expenses</h2>
  <p id="monthlyTotal" class="text-4xl font-semibold mt-2">₱ 0.00</p>
`;
document.querySelector('.p-6').prepend(monthlyCardContainer);

// Initialize all weeks with empty data
function initializeWeeks() {
  for (let i = 1; i <= MAX_WEEKS; i++) {
    weekExpenses[`week${i}`] = { products: [], total: 0 };
  }
}

function createProductRow(productName = '', price = '') {
  const row = document.createElement('div');
  row.className = 'flex justify-between items-center product-row gap-4 mx-4';
  row.innerHTML = `
    <div class="flex items-center gap-3 flex-1">
      <button type="button" class="bg-red-600 text-white rounded-full w-10 h-10 flex items-center justify-center text-2xl remove-btn flex-shrink-0">−</button>
      <input type="text" placeholder="Enter product" value="${productName}" class="flex-1 text-lg bg-white border-2 border-gray-300 focus:border-red-600 focus:outline-none px-4 py-2 rounded-lg transition-colors product-input">
    </div>
    <input type="number" placeholder="0.00" value="${price}" class="me-4 w-24 text-lg bg-white border-2 border-gray-300 focus:border-red-600 focus:outline-none px-4 py-2 rounded-lg text-right transition-colors price-input">
  `;
  row.querySelector('.remove-btn').addEventListener('click', () => row.remove());
  return row;
}

function createWeekCard(weekNumber, totalExpenses = 0) {
  const card = document.createElement('button');
  card.type = 'button';
  card.className = 'text-left hover:opacity-90 transition-opacity week-card-btn';
  card.setAttribute('data-week', weekNumber);
  card.onclick = () => openExpenseForm(weekNumber);

  const cardDiv = document.createElement('div');
  cardDiv.className = 'bg-red-600 text-white rounded-xl p-8';
  cardDiv.innerHTML = `
    <h1 class="text-3xl font-['Outfit'] pb-2">Week ${weekNumber}</h1>
    <div class="flex justify-between items-center">
      <p class="font-['Switzer'] text-2xl">Total Expenses:</p> 
      <span class="font-['Outfit'] text-3xl week-total">₱ ${Number(totalExpenses).toLocaleString('en-US', { minimumFractionDigits: 2 })}</span>
    </div>
  `;

  card.appendChild(cardDiv);
  return card;
}

function loadProductsForWeek(weekNumber) {
  productList.innerHTML = '';
  const weekData = weekExpenses[`week${weekNumber}`];
  if (!weekData || weekData.products.length === 0) {
    productList.appendChild(createProductRow());
  } else {
    weekData.products.forEach(p => {
      productList.appendChild(createProductRow(p.productName, p.price));
    });
  }
  weekTitle.textContent = `Week ${weekNumber}`;
}

function openExpenseForm(weekNumber) {
  currentEditingWeek = weekNumber;
  loadProductsForWeek(weekNumber);
  cancelBtn.classList.remove('hidden');
}

// Update the week card display
function updateWeekCard(weekNumber, total) {
  const existingCard = cardsList.querySelector(`[data-week="${weekNumber}"]`);
  if (existingCard) {
    existingCard.querySelector('.week-total').textContent =
      `₱ ${Number(total).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
  }
}

// Update monthly total display
function updateMonthlyTotalDisplay() {
  let total = 0;
  Object.values(weekExpenses).forEach(week => {
    total += week.total || 0;
  });
  document.getElementById('monthlyTotal').textContent =
    `₱ ${Number(total).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}

// Render all week cards
function renderAllWeekCards() {
  cardsList.innerHTML = '';
  for (let i = 1; i <= MAX_WEEKS; i++) {
    const weekData = weekExpenses[`week${i}`] || { products: [], total: 0 };
    const card = createWeekCard(i, weekData.total);
    cardsList.appendChild(card);
  }
}

// Add new product row
addBtn.addEventListener('click', () => {
  productList.appendChild(createProductRow());
});
// Save expenses
saveExpensesBtn.addEventListener('click', async () => {
  const productRows = document.querySelectorAll('.product-row');
  const products = [];
  let total = 0;

  productRows.forEach(row => {
    const productName = row.querySelector('.product-input').value.trim();
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    if (productName || price) {
      products.push({ productName, price });
      total += price;
    }
  });

  if (products.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'No Products Entered',
      text: 'Please add at least one product before saving.'
    });
    return;
  }

  const payload = {
    business_type: 'Gas System',
    week_number: currentEditingWeek,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    expense_items: products,
    total_amount: total
  };

  try {
    const response = await fetch('expenseHandler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await response.json();

    if (result.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: `Week ${currentEditingWeek} Saved!`,
        text: `Your expenses for Week ${currentEditingWeek} have been successfully saved.`,
        timer: 1500,
        showConfirmButton: false
      });

      // Update local state
      weekExpenses[`week${currentEditingWeek}`] = { products, total };
      
      // Update card display
      updateWeekCard(currentEditingWeek, total);
      
      // Update monthly total
      updateMonthlyTotalDisplay();

    } else {
      Swal.fire({
        icon: 'error',
        title: 'Error Saving!',
        text: result.message || 'An unknown error occurred.'
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Connection Error!',
      text: error.message
    });
  }
});

async function loadAllWeeks() {
  const month = new Date().getMonth() + 1;
  const year = new Date().getFullYear();

  try {
    const response = await fetch(
      `expenseHandler.php?month=${month}&year=${year}&business_type=Gas%20System`
    );
    const result = await response.json();

    if (result.status === 'success') {
      const weeks = result.data.weeks || [];
      const monthlyTotal = result.data.monthly_total || 0;

      // Initialize all weeks with empty data first
      initializeWeeks();

      // Populate with data
      if (weeks.length > 0) {
        weeks.forEach(w => {
          const weekNum = Number(w.week_number);
          if (weekNum >= 1 && weekNum <= MAX_WEEKS) {
            let products = [];
            try {
              products = w.expense_items ? JSON.parse(w.expense_items) : [];
            } catch (e) {
              console.error('Error parsing expense items:', e);
              products = [];
            }
            
            const total = parseFloat(w.total_amount);
            weekExpenses[`week${weekNum}`] = {
              products,
              total: Number.isFinite(total) ? total : 0
            };
          }
        });
      }

      // Render all week cards
      renderAllWeekCards();

      document.getElementById('monthlyTotal').textContent =
        `₱ ${Number(monthlyTotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

      // Open Week 1 by default
      openExpenseForm(1);

    } else {
      Swal.fire({
        icon: 'error',
        title: 'Load Failed',
        text: result.message || 'Could not fetch expense data.'
      });
    }
  } catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error Loading!',
      text: error.message
    });
    
    // Initialize with empty data on error
    initializeWeeks();
    renderAllWeekCards();
    openExpenseForm(1);
  }
}

// Cancel editing - reload current week
cancelBtn.addEventListener('click', () => {
  loadProductsForWeek(currentEditingWeek);
});

// Reset month - clear all expenses
resetMonthBtn.addEventListener('click', async () => {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Reset Month?',
    text: 'This will reset all expenses for the current month. This action cannot be undone.',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, Reset Month',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    const month = new Date().getMonth() + 1;
    const year = new Date().getFullYear();

    try {
      const response = await fetch('expenseHandler.php', {
        method: 'DELETE',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          business_type: 'Gas System',
          month: month,
          year: year
        })
      });
      const deleteResult = await response.json();

      if (deleteResult.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Month Reset!',
          text: 'All expenses have been cleared successfully.',
          timer: 1500,
          showConfirmButton: false
        });

        // Reset local state
        initializeWeeks();
        renderAllWeekCards();
        updateMonthlyTotalDisplay();
        openExpenseForm(1);

      } else {
        Swal.fire({
          icon: 'error',
          title: 'Reset Failed',
          text: deleteResult.message || 'Could not reset month data.'
        });
      }
    } catch (error) {
      Swal.fire({
        icon: 'error',
        title: 'Connection Error!',
        text: error.message
      });
    }
  }
});

// Load data on page load
document.addEventListener('DOMContentLoaded', loadAllWeeks);