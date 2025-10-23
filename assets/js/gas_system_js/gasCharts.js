// gasCharts.js - All chart configurations for the POS system

document.addEventListener('DOMContentLoaded', function () {

  // Initialize Brands Pie Chart
  const brandsPieCtx = document.getElementById('brandsPieChart');
  if (brandsPieCtx) {

    // Check if dynamic data exists from PHP, otherwise use default
    const chartData = window.brandChartData || {
      labels: ['Petron', 'Econo', 'SeaGas'],
      data: [0, 0, 0] // Default
    };

    window.brandsPieChart = new Chart(brandsPieCtx.getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: chartData.labels,
        datasets: [{
          data: chartData.data,
          backgroundColor: [
            '#EF4444', // Red for Petron
            '#3B82F6', // Blue for Econo
            '#22C55E', // Green for SeaGas
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function (context) {
                return context.label + ': ' + context.parsed + ' units';
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
        labels: [],
        datasets: [{
          label: 'Sales',
          data: [],
          borderColor: 'rgba(220,38,38,1)',
          backgroundColor: 'rgba(220,38,38,0.1)',
          fill: true,
          tension: 0,
          pointRadius: 6,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Sales: ₱' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index'
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return '₱' + value.toLocaleString();
              }
            }
          }
        }
      }
    });
  }

  // Initialize Customer Bar Chart
  const customerCtx = document.getElementById('numCustomer');
  if (customerCtx) {
    window.customerChart = new Chart(customerCtx.getContext('2d'), {
      type: 'bar',
      data: {
        labels: [],
        datasets: [{
          label: 'Customers',
          data: [],
          borderColor: 'rgba(220,38,38,1)',
          backgroundColor: 'rgba(220,38,38,0.8)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return 'Customers: ' + context.parsed.y;
              }
            }
          }
        },
        interaction: {
          intersect: false,
          mode: 'index'
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 10,
              callback: function (value) {
                return value;
              }
            }
          }
        }
      }
    });
  }

});