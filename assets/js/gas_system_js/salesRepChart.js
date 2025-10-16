// Line Chart for Sales Data
document.addEventListener('DOMContentLoaded', function () {
	const ctx = document.getElementById('salesChart').getContext('2d');
	const salesChart = new Chart(ctx, {
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
			scales: {
			}
		}
	});
});

// Bar Chart for Number of Customers
document.addEventListener('DOMContentLoaded', function () {
	const ctx = document.getElementById('numCustomer').getContext('2d');
	const salesChart = new Chart(ctx, {
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
				label: 'Sales',
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
			scales: {
			}
		}
	});
});