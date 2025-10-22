document.addEventListener('DOMContentLoaded', function () {
  setupFilterListeners();
  setTimeout(() => {
    updateDashboard();
  }, 100);
});

function setupFilterListeners() {
  const salesFilterType = document.getElementById('salesFilterType');
  const salesMonth = document.getElementById('salesMonth');
  const customerFilterType = document.getElementById('customerFilterType');
  const customerMonth = document.getElementById('customerMonth');

  // Sales filters
  salesFilterType?.addEventListener('change', () => {
    updateSalesChart();
  });
  salesMonth?.addEventListener('change', () => {
    updateSalesChart();
    updateSummaryCardsForMonth();
  });

  // Customer filters
  customerFilterType?.addEventListener('change', () => {
    updateCustomerChart();
  });
  customerMonth?.addEventListener('change', () => {
    updateCustomerChart();
  });
}

function updateSalesChart() {
  if (!window.salesChart || !window.salesData) return;

  const filterType = document.getElementById('salesFilterType').value;
  const month = document.getElementById('salesMonth').value;
  const year = window.currentYear || new Date().getFullYear();

  const data = window.salesData[year]?.[month];
  if (!data) {
    console.warn(`No data found for ${month} ${year}`);
    return;
  }

  let labels, values;
  if (filterType === 'Week') {
    labels = data.weeks.map((w, i) => `Week ${i + 1} - ${month}`);
    values = data.weeks.map(w => w.sales);
  } else {
    labels = [month];
    values = [data.monthly.sales];
  }

  window.salesChart.data.labels = labels;
  window.salesChart.data.datasets[0].data = values;
  window.salesChart.options.scales.y = {
    beginAtZero: true,
    ticks: {
      callback: function (value) {
        return '₱ ' + value.toLocaleString();
      }
    }
  };
  window.salesChart.update();

  // Update chart title
  const salesChartTitle = document.getElementById('salesChartTitle');
  if (salesChartTitle) {
    salesChartTitle.textContent = `${month} Sales Summary`;
  }
}

function updateCustomerChart() {
  if (!window.customerChart || !window.salesData) return;

  const filterType = document.getElementById('customerFilterType').value;
  const month = document.getElementById('customerMonth').value;
  const year = window.currentYear || new Date().getFullYear();

  const data = window.salesData[year]?.[month];
  if (!data) {
    console.warn(`No data found for ${month} ${year}`);
    return;
  }

  let labels, values;

  if (filterType === 'Week') {
    labels = data.weeks.map((w, i) => `Week ${i + 1} - ${month}`);
    values = data.weeks.map(w => w.customers);
  } else {
    labels = [month];
    values = [data.monthly.customers];
  }

  window.customerChart.data.labels = labels;
  window.customerChart.data.datasets[0].data = values;
  window.customerChart.options.scales.y = {
    beginAtZero: true,
    ticks: {
      stepSize: 10
    }
  };
  window.customerChart.update();

  // Update chart title
  const customerChartTitle = document.getElementById('customerChartTitle');
  if (customerChartTitle) {
    customerChartTitle.textContent = `${month} Number of Customers`;
  }
}

function updateSummaryCardsForMonth() {
  if (!window.salesData) return;

  const month = document.getElementById('salesMonth').value;
  const year = window.currentYear || new Date().getFullYear();
  const data = window.salesData[year]?.[month];

  if (data && data.weeks.length > 0) {
    const currentWeek = data.weeks[0];

    // Update summary cards (Sales, Customers, Delivered)
    const salesCard = document.getElementById('summaryCardSales');
    const customersCard = document.getElementById('summaryCardCustomers');
    const deliveredCard = document.getElementById('summaryCardDelivered');
    const netWorthCard = document.getElementById('summaryCardNetWorth');

    if (salesCard) salesCard.textContent = `₱ ${currentWeek.sales.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    if (customersCard) customersCard.textContent = currentWeek.customers;
    // Use 'paid' (server provides the number of paid/delivered orders)
    if (deliveredCard) deliveredCard.textContent = currentWeek.paid ?? 0;

    // Use netWorth if provided by server; otherwise fallback to sales - weeklyExpenses when available
    let netDisplay = null;
    if (typeof currentWeek.netWorth !== 'undefined') {
      netDisplay = currentWeek.netWorth;
    } else if (typeof currentWeek.weeklyExpenses !== 'undefined') {
      netDisplay = currentWeek.sales - currentWeek.weeklyExpenses;
    } else {
      netDisplay = currentWeek.sales;
    }
    if (netWorthCard) netWorthCard.textContent = `₱ ${Number(netDisplay).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

    // Update week display
    const weekDisplay = document.getElementById('weekDisplay');
    if (weekDisplay) {
      weekDisplay.textContent = `${month} - Week 1`;
    }
  }
}

function updateDashboard() {
  updateSalesChart();
  updateCustomerChart();
}

// Format currency
function formatCurrency(amount) {
  return '₱ ' + parseFloat(amount).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// Get week of month from date
function getWeekOfMonth(date) {
  const day = date.getDate();
  if (day <= 7) return 1;
  if (day <= 14) return 2;
  if (day <= 21) return 3;
  if (day <= 28) return 4;
  return 5;
}