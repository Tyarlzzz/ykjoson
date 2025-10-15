  const ctx = document.getElementById('brandsPieChart').getContext('2d');
  const brandsPieChart = new Chart(ctx, {
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