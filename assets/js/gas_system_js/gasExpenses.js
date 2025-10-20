const createWeekBtn = document.getElementById('createWeekBtn');
const cardsList = document.getElementById('cardsList');
const weekTitle = document.getElementById('weekTitle');
const saveExpensesBtn = document.getElementById('saveExpensesBtn');
const cancelBtn = document.getElementById('cancelBtn');
const productList = document.querySelector('.productList');
const addBtn = document.getElementById('addProductBtn');

let currentEditingWeek = 1;
let weekExpenses = {};
let MAX_WEEKS = 4;

const monthlyCardContainer = document.createElement('div');
monthlyCardContainer.className =
  "flex font-['Outfit'] bg-red-100 border border-red-600 text-red-800 p-4 mb-6 rounded-lg shadow justify-between items-center";
monthlyCardContainer.innerHTML = `
  <h2 class="text-3xl font-bold">Total Monthly Expenses</h2>
  <p id="monthlyTotal" class="text-4xl font-semibold mt-2">₱ 0.00</p>
`;
document.querySelector('.p-6').prepend(monthlyCardContainer);

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
      <p class="font-[Switzer'] text-2xl">Total Expenses: </p> 
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
    weekData.products.forEach(p => productList.appendChild(createProductRow(p.productName, p.price)));
  }
  weekTitle.textContent = `Week ${weekNumber}`;
}

function openExpenseForm(weekNumber) {
  currentEditingWeek = weekNumber;
  loadProductsForWeek(weekNumber);
  cancelBtn.classList.remove('hidden');
}

// Add new product
addBtn.addEventListener('click', () => productList.appendChild(createProductRow()));

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
    business_type: 'Gas Business',
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

      weekExpenses[`week${currentEditingWeek}`] = { products, total };
      const existingCard = cardsList.querySelector(`[data-week="${currentEditingWeek}"]`);
      if (existingCard) {
        existingCard.querySelector('.week-total').textContent =
          `₱ ${Number(total).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
      }

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

createWeekBtn.addEventListener('click', () => {
  const existingWeeks = Object.keys(weekExpenses).length;
  if (existingWeeks >= MAX_WEEKS) {
    Swal.fire({
      icon: 'info',
      title: 'All Weeks Created',
      text: 'All 4 weeks have been created. Start a new month instead.'
    });
    return;
  }
  const newWeekNum = existingWeeks + 1;
  weekExpenses[`week${newWeekNum}`] = { products: [], total: 0 };
  const newCard = createWeekCard(newWeekNum, 0);
  cardsList.appendChild(newCard);
  openExpenseForm(newWeekNum);
});

async function loadAllWeeks() {
  const month = new Date().getMonth() + 1;
  const year = new Date().getFullYear();

  try {
    const response = await fetch(`expenseHandler.php?month=${month}&year=${year}&business_type=Gas%20Business`);
    const result = await response.json();

    if (result.status === 'success') {
      const weeks = result.data.weeks || [];
      const monthlyTotal = result.data.monthly_total || 0;

      // Update monthly total card
      document.getElementById('monthlyTotal').textContent =
        `₱ ${Number(monthlyTotal).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;

      // Reset week cards
      cardsList.innerHTML = '';
      weekExpenses = {};

      if (weeks.length > 0) {
        weeks.forEach(w => {
          const weekNum = w.week_number;
          const products = JSON.parse(w.expense_items);
          weekExpenses[`week${weekNum}`] = { products, total: parseFloat(w.total_amount) };
          const card = createWeekCard(weekNum, w.total_amount);
          cardsList.appendChild(card);
        });
      } else {
        weekExpenses[`week1`] = { products: [], total: 0 };
        cardsList.appendChild(createWeekCard(1, 0));
      }

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
  }
}

function updateMonthlyTotalDisplay() {
  let total = 0;
  Object.values(weekExpenses).forEach(week => total += week.total || 0);
  document.getElementById('monthlyTotal').textContent =
    `₱ ${Number(total).toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
}

cancelBtn.addEventListener('click', () => loadProductsForWeek(currentEditingWeek));

document.addEventListener('DOMContentLoaded', loadAllWeeks);