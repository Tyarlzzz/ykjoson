// Charts.js - All chart configurations for the POS system

document.addEventListener('DOMContentLoaded', function () {

  // Initialize Brands Pie Chart
  const brandsPieCtx = document.getElementById('brandsPieChart');
  if (brandsPieCtx) {
    window.brandsPieChart = new Chart(brandsPieCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Petron', 'Econo', 'SeaGas'],
        datasets: [{
          data: [60, 25, 15],
          backgroundColor: [
            '#EF4444', // Red for Petron
            '#3B82F6', // Blue for Econo
            '#22C55E'  // Green for SeaGas
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false // Hide default legend since we have custom legend
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.label + ': ' + context.parsed + '%';
              }
            }
          }
        }
      }
    });
  }

  // Initialize Sales Line Chart
  const salesCtx = document.getElementById('salesChart');
  if (salesCtx) {
    window.salesChart = new Chart(salesCtx.getContext('2d'), {
      type: 'line',
      data: {
        labels: [
          '',
          'Week 1 - Oct 1-7',
          'Week 2 - Oct 8-14',
          'Week 3 - Oct 15-21',
          'Week 4 - Oct 22-28',
          'Week 5 - Oct 29-31'
        ],
        datasets: [{
          label: 'Sales',
          data: [0, 4000, 6000, 12000, 3000, 9000],
          borderColor: 'rgba(220,38,38,1)',
          backgroundColor: 'rgba(220,38,38,1)',
          fill: false,
          tension: null
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
        },
        interaction: {
          intersect: false,
        },
        scales: {}
      }
    });
  }

  // Initialize Customer Bar Chart
  const customerCtx = document.getElementById('numCustomer');
  if (customerCtx) {
    window.customerChart = new Chart(customerCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: [
          'Week 1 - Oct 1-7',
          'Week 2 - Oct 8-14',
          'Week 3 - Oct 15-21',
          'Week 4 - Oct 22-28',
          'Week 5 - Oct 29-31'
        ],
        datasets: [{
          label: 'Customers',
          data: [40, 80, 130, 100, 60],
          borderColor: 'rgba(220,38,38,1)',
          backgroundColor: 'rgba(220,38,38,1)',
          fill: false,
          tension: null
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
        },
        interaction: {
          intersect: false,
        },
        scales: {}
      }
    });
  }

});