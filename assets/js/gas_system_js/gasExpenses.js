const createWeekBtn = document.getElementById('createWeekBtn');
const cardsList = document.getElementById('cardsList');
const weekTitle = document.getElementById('weekTitle');
const saveExpensesBtn = document.getElementById('saveExpensesBtn');
const cancelBtn = document.getElementById('cancelBtn');

// Product management elements
const productList = document.querySelector('.productList');
const addBtn = document.getElementById('addProductBtn');

// State variables
let weekCount = 1;
let currentEditingWeek = 1;
let isNewWeek = false;
const MAX_WEEKS = 4;

// Store expenses per week in sessionStorage
let weekExpenses = JSON.parse(sessionStorage.getItem('gasWeekExpenses')) || {};

// Function to create a new product row (dropdown for product)
function createProductRow(productName = '', price = '') {
  const row = document.createElement('div');
  row.className = 'flex justify-between items-center product-row gap-4 mx-4';

  row.innerHTML = `
  <div class="flex items-center gap-3 flex-1">
      <button type="button" class="bg-red-600 text-white font-['Outfit'] rounded-full w-10 h-10 flex items-center justify-center text-2xl remove-btn flex-shrink-0">−</button>
      <select class="flex-1 text-lg bg-white border-2 border-gray-300 focus:border-red-600 focus:outline-none px-4 py-2 rounded-lg transition-colors product-input">
        <option value="">Select product</option>
        <option value="petron" ${productName === 'petron' ? 'selected' : ''}>Petron</option>
        <option value="econo" ${productName === 'econo' ? 'selected' : ''}>Econo</option>
        <option value="seagas" ${productName === 'seagas' ? 'selected' : ''}>Seagas</option>
      </select>
  </div>
  <input type="number" placeholder="0.00" value="${price}" class="me-4 w-24 text-lg bg-white border-2 border-gray-300 focus:border-red-600 focus:outline-none px-4 py-2 rounded-lg text-right transition-colors price-input">
  `;

  // Attach remove event to the new button
  row.querySelector('.remove-btn').addEventListener('click', () => {
    row.remove();
  });

  return row;
}

// Load products for a specific week
function loadProductsForWeek(weekNumber) {
  productList.innerHTML = '';
  const weekData = weekExpenses[`week${weekNumber}`];

  if (!weekData || weekData.products.length === 0) {
    // Add one empty row if no products
    productList.appendChild(createProductRow());
  } else {
    weekData.products.forEach(product => {
      productList.appendChild(createProductRow(product.productName, product.price));
    });
  }
}

// Function to create a week card
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
      <p class="font-[Switzer'] text-2xl">Total Expenses: </p> 
      <span class="font-['Outfit'] text-3xl week-total">P ${totalExpenses.toLocaleString()}</span>
    </div>
  `;

  card.appendChild(cardDiv);
  return card;
}

// Function to open expense form
function openExpenseForm(weekNumber, isNew = false) {
  isNewWeek = isNew;
  currentEditingWeek = weekNumber;
  weekTitle.textContent = `Week ${weekNumber}`;

  // Load products for this specific week
  loadProductsForWeek(weekNumber);

  // Show/hide cancel button based on whether it's a new week
  if (isNew) {
    cancelBtn.classList.add('hidden');
  } else {
    cancelBtn.classList.remove('hidden');
  }
}

// Add new product row on click
addBtn.addEventListener('click', () => {
  const newRow = createProductRow();
  productList.appendChild(newRow);
});

// Create week button
createWeekBtn.addEventListener('click', () => {
  if (weekCount >= MAX_WEEKS) {
    alert('All 4 weeks have been created. Please complete all weeks before creating a new month.');
    return;
  }
  weekCount++;
  const newCard = createWeekCard(weekCount, 0);
  newCard.setAttribute('data-week', weekCount);
  cardsList.appendChild(newCard);
  openExpenseForm(weekCount, true);
});

// Cancel button - reload the saved data for current week
cancelBtn.addEventListener('click', () => {
  loadProductsForWeek(currentEditingWeek);
});

// Reset weeks button (shown when all 4 weeks are completed)
function showResetOption() {
  const allWeeksCards = cardsList.querySelectorAll('.week-card-btn');
  if (allWeeksCards.length === MAX_WEEKS) {
    createWeekBtn.innerHTML = `
      <svg class="w-10 h-10 flex-shrink-0 mt-2" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="-5.0 -10.0 110.0 135.0">
        <path d="m83.602 16.398c-18.5-18.5-48.699-18.5-67.199 0s-18.5 48.699 0 67.199 48.699 18.5 67.199 0c18.5-18.496 18.5-48.699 0-67.199zm-9.1016 37.801h-20.398v20.398h-8.3984v-20.398h-20.301v-8.3984h20.301l-0.003906-20.301h8.3984v20.301h20.301z" fill="white"/>
      </svg>
      <span class="text-2xl">Start New Month</span>
    `;
    createWeekBtn.onclick = resetWeeks;
  }
}

function resetWeeks() {
  weekCount = 1;
  cardsList.innerHTML = '';
  weekExpenses = {}; // Clear all week expenses
  sessionStorage.setItem('gasWeekExpenses', JSON.stringify(weekExpenses));

  const firstCard = createWeekCard(1, 0);
  firstCard.setAttribute('data-week', 1);
  cardsList.appendChild(firstCard);
  openExpenseForm(1, true);

  createWeekBtn.innerHTML = `
    <svg class="w-10 h-10 flex-shrink-0 mt-2" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="-5.0 -10.0 110.0 135.0">
      <path d="m83.602 16.398c-18.5-18.5-48.699-18.5-67.199 0s-18.5 48.699 0 67.199 48.699 18.5 67.199 0c18.5-18.496 18.5-48.699 0-67.199zm-9.1016 37.801h-20.398v20.398h-8.3984v-20.398h-20.301v-8.3984h20.301l-0.003906-20.301h8.3984v20.301h20.301z" fill="white"/>
    </svg>
    <span class="text-2xl">Create Week Expenses</span>
  `;
  createWeekBtn.onclick = null;
}

// Save expenses
saveExpensesBtn.addEventListener('click', () => {
  const productRows = document.querySelectorAll('.product-row');
  let totalExpenses = 0;
  const products = [];

  productRows.forEach(row => {
    const productName = row.querySelector('.product-input').value;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;

    if (productName || price) {
      products.push({ productName, price });
      totalExpenses += price;
    }
  });

  // Save to weekExpenses object
  weekExpenses[`week${currentEditingWeek}`] = {
    products: products,
    total: totalExpenses
  };
  sessionStorage.setItem('gasWeekExpenses', JSON.stringify(weekExpenses));

  // Update card total
  const existingCard = cardsList.querySelector(`[data-week="${currentEditingWeek}"]`);
  if (existingCard) {
    const weekTotal = existingCard.querySelector('.week-total');
    if (weekTotal) {
      weekTotal.textContent = `P ${totalExpenses.toLocaleString()}`;
    }
  }

  showResetOption();
});

// Initialize - Load existing weeks from sessionStorage
function initializeWeeks() {
  // Count existing weeks
  const existingWeeks = Object.keys(weekExpenses).length;
  weekCount = existingWeeks || 1;

  if (existingWeeks > 0) {
    // Load all existing week cards
    for (let i = 1; i <= existingWeeks; i++) {
      const weekData = weekExpenses[`week${i}`];
      const card = createWeekCard(i, weekData ? weekData.total : 0);
      card.setAttribute('data-week', i);
      cardsList.appendChild(card);
    }
  } else {
    // Create first week if none exist
    const firstCard = createWeekCard(1, 0);
    firstCard.setAttribute('data-week', 1);
    cardsList.appendChild(firstCard);
  }

  // Open the first week
  openExpenseForm(1, existingWeeks === 0);

  // Check if we should show reset option
  showResetOption();
}

// Initialize on page load
initializeWeeks();
