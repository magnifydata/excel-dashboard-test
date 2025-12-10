function switchTab(tabName) {
    // Hide all tab sections
    document.querySelectorAll('.tab-section').forEach(section => {
        section.classList.remove('active');
    });
    
    // Remove active class from buttons
    document.querySelectorAll('.nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab and highlight button
    document.getElementById(tabName).classList.add('active');
    document.getElementById('btn-' + tabName).classList.add('active');
}

document.addEventListener("DOMContentLoaded", function() {
    
    // --- Bar Chart ---
    const ctxBar = document.getElementById('barChart');
    if(ctxBar && window.chartData) {
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Business', 'Computing'],
                datasets: [{
                    label: 'Average Score',
                    // These names must match the PHP output exactly
                    data: [window.chartData.avgBusiness, window.chartData.avgComputing],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, max: 100 } }
            }
        });
    }

    // --- Pie Chart ---
    const ctxPie = document.getElementById('pieChart');
    if(ctxPie && window.chartData) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [window.chartData.male, window.chartData.female],
                    backgroundColor: ['#3b82f6', '#ec4899'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%'
            }
        });
    }
});