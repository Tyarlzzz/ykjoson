// Mock datas muna
const salesData = {
  2024: {
    September: {
      weeks: [
        { week: 1, sales: 2500, customers: 20, delivered: 19 },
        { week: 2, sales: 500, customers: 30, delivered: 24 },
        { week: 3, sales: 8000, customers: 22, delivered: 21 },
        { week: 4, sales: 1200, customers: 15, delivered: 23 },
        { week: 5, sales: 7600, customers: 24, delivered: 23 }
      ],
      monthly: { sales: 11600, customers: 111, delivered: 87 }
    },
    October: {
      weeks: [
        { week: 1, sales: 3450, customers: 27, delivered: 26 },
        { week: 2, sales: 9000, customers: 32, delivered: 30 },
        { week: 3, sales: 2200, customers: 29, delivered: 28 },
        { week: 4, sales: 900, customers: 31, delivered: 30 },
        { week: 5, sales: 8029, customers: 24, delivered: 23 }
      ],
      monthly: { sales: 15550, customers: 143, delivered: 114 }
    },
    November: {
      weeks: [
        { week: 1, sales: 3900, customers: 30, delivered: 29 },
        { week: 2, sales: 4500, customers: 15, delivered: 33 },
        { week: 3, sales: 4100, customers: 31, delivered: 30 },
        { week: 4, sales: 4300, customers: 50, delivered: 32 },
        { week: 5, sales: 3100, customers: 24, delivered: 23 }
      ],
      monthly: { sales: 16800, customers: 150, delivered: 124 }
    },
    December: {
      weeks: [
        { week: 1, sales: 100, customers: 15, delivered: 38 },
        { week: 2, sales: 9000, customers: 45, delivered: 43 },
        { week: 3, sales: 6000, customers: 20, delivered: 40 },
        { week: 4, sales: 15000, customers: 13, delivered: 41 },
        { week: 5, sales: 3100, customers: 24, delivered: 23 }
      ],
      monthly: { sales: 22000, customers: 117, delivered: 162 }
    }
  }
};

// Initialize Charts on DOM
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
    updateSummaryCards();
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
  if (!window.salesChart) return;

  const filterType = document.getElementById('salesFilterType').value;
  const month = document.getElementById('salesMonth').value;

  const data = salesData[2024][month];
  if (!data) return;

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

  // Update chart title (ex. September Sales Summary)
  const salesChartTitle = document.getElementById('salesChartTitle');
  if (salesChartTitle) {
    salesChartTitle.textContent = `${month} Sales Summary`;
  }
}

function updateCustomerChart() {
  if (!window.customerChart) return;

  const filterType = document.getElementById('customerFilterType').value;
  const month = document.getElementById('customerMonth').value;

  const data = salesData[2024][month];
  if (!data) return;

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

  // update chart title (ex. September Number of Customers)
  const customerChartTitle = document.getElementById('customerChartTitle');
  if (customerChartTitle) {
    customerChartTitle.textContent = `${month} Number of Customers`;
  }
}

function updateSummaryCards() {
  const month = document.getElementById('salesMonth').value;
  const data = salesData[2024][month];

  if (data && data.weeks.length > 0) {
    const currentWeek = data.weeks[0];

    // update summary cards (Sales, Customers, Delivered)
    const salesCard = document.getElementById('summaryCardSales');
    const customersCard = document.getElementById('summaryCardCustomers');
    const deliveredCard = document.getElementById('summaryCardDelivered');

    if (salesCard) salesCard.textContent = `₱ ${currentWeek.sales.toLocaleString()}`;
    if (customersCard) customersCard.textContent = currentWeek.customers;
    if (deliveredCard) deliveredCard.textContent = currentWeek.delivered;

    // update week display (ex. September - Week 1)
    const weekDisplay = document.getElementById('weekDisplay');
    if (weekDisplay) {
      weekDisplay.textContent = `${month} - Week 1`;
    }
  }
}

function updateDashboard() {
  updateSummaryCards();
  updateSalesChart();
  updateCustomerChart();
}