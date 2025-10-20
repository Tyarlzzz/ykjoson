const createWeekBtn = document.getElementById('createWeekBtn');
const cardsList = document.getElementById('cardsList');
const weekTitle = document.getElementById('weekTitle');
const saveExpensesBtn = document.getElementById('saveExpensesBtn');
const cancelBtn = document.getElementById('cancelBtn');
const productList = document.querySelector('.productList');
const addBtn = document.getElementById('addProductBtn');

const MAX_WEEKS = 4;
const businessType = "Gas System";
let weekCount = 1;
let currentEditingWeek = 1;
let isNewWeek = false;


let weekExpenses = {};

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

  row.querySelector('.remove-btn').addEventListener('click', () => row.remove());
  return row;
}

function loadProductsForWeek(weekNumber) {
  productList.innerHTML = '';
  const weekData = weekExpenses[`week${weekNumber}`];

  if (!weekData || !weekData.products.length) {
    productList.appendChild(createProductRow());
  } else {
    weekData.products.forEach(product => {
      productList.appendChild(createProductRow(product.productName, product.price));
    });
  }
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
      <p class="font-[Switzer'] text-2xl">Total Expenses: </p> 
      <span class="font-['Outfit'] text-3xl week-total">₱ ${totalExpenses.toLocaleString()}</span>
    </div>
  `;

  card.appendChild(cardDiv);
  return card;
}

function openExpenseForm(weekNumber, isNew = false) {
  isNewWeek = isNew;
  currentEditingWeek = weekNumber;
  weekTitle.textContent = `Week ${weekNumber}`;
  loadProductsForWeek(weekNumber);

  cancelBtn.classList.toggle('hidden', isNew);
}

addBtn.addEventListener('click', () => {
  productList.appendChild(createProductRow());
});

createWeekBtn.addEventListener('click', () => {
  if (weekCount >= MAX_WEEKS) {
    Swal.fire('Limit Reached', 'All 4 weeks have been created.', 'info');
    return;
  }
  weekCount++;
  const newCard = createWeekCard(weekCount, 0);
  cardsList.appendChild(newCard);
  openExpenseForm(weekCount, true);
});

cancelBtn.addEventListener('click', () => {
  loadProductsForWeek(currentEditingWeek);
});

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
  weekExpenses = {};
  cardsList.innerHTML = '';
  const firstCard = createWeekCard(1, 0);
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

saveExpensesBtn.addEventListener('click', async () => {
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

  const data = {
    business_type: businessType,
    week_number: currentEditingWeek,
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    expense_items: products,
    total_amount: totalExpenses
  };

  try {
    const res = await fetch('../Gas/expenseHandler.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const result = await res.json();

    if (result.status === "success") {
      Swal.fire('Saved!', result.message, 'success');
      weekExpenses[`week${currentEditingWeek}`] = {
        products,
        total: totalExpenses
      };
      const card = cardsList.querySelector(`[data-week="${currentEditingWeek}"] .week-total`);
      if (card) card.textContent = `₱ ${totalExpenses.toLocaleString()}`;
      showResetOption();
    } else {
      Swal.fire('Error', result.message, 'error');
    }
  } catch (err) {
    Swal.fire('Error', 'Unable to save expenses. Check your connection.', 'error');
  }
});

async function loadExistingExpenses() {
  const month = new Date().getMonth() + 1;
  const year = new Date().getFullYear();

  try {
    const res = await fetch(`../Gas/expenseHandler.php?business_type=${businessType}&month=${month}&year=${year}`);
    const result = await res.json();

    if (result.status === "success" && result.data.weeks.length > 0) {
      result.data.weeks.forEach(w => {
        const weekKey = `week${w.week_number}`;
        weekExpenses[weekKey] = {
          products: JSON.parse(w.expense_items),
          total: parseFloat(w.total_amount)
        };
      });
    }
  } catch (err) {
    console.error("Error loading expenses:", err);
  }
}

async function initializeWeeks() {
  await loadExistingExpenses();

  const existingWeeks = Object.keys(weekExpenses).length;
  weekCount = existingWeeks || 1;

  if (existingWeeks > 0) {
    for (let i = 1; i <= existingWeeks; i++) {
      const data = weekExpenses[`week${i}`];
      cardsList.appendChild(createWeekCard(i, data ? data.total : 0));
    }
  } else {
    cardsList.appendChild(createWeekCard(1, 0));
  }

  openExpenseForm(1, existingWeeks === 0);
  showResetOption();
}

initializeWeeks();