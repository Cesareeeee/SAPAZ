document.addEventListener('DOMContentLoaded', function () {
    // 1. Cargar Estadísticas Generales
    loadDashboardStats();

    // 2. Setup Filters for Income Chart and Load Initial Data
    setupIncomeFilters();
});

let incomeChartInstance = null;
let consumptionChartInstance = null;

function loadDashboardStats() {
    fetch('../controladores/dashboard.php?action=get_stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update Cards
                animateValue(document.getElementById('totalClientes'), 0, data.data.total_clientes, 1500);
                animateValue(document.getElementById('lecturasMes'), 0, data.data.lecturas_mes, 1500);

                // Formatted Currency
                document.getElementById('montoCobrado').textContent = formatCurrency(data.data.monto_cobrado);
                document.getElementById('deudaHistorica').textContent = formatCurrency(data.data.deuda_total); // Showing current month uncollected as relevant metric or total historical if needed? Controller sends 'deuda_total' (month) and 'deuda_historica'

                // If user wants Total Historical Debt on the card instead:
                if (data.data.deuda_historica) {
                    document.getElementById('deudaHistorica').textContent = formatCurrency(data.data.deuda_historica);
                }

                // Initialize Consumption Chart (Bottom Left)
                initConsumptionChart(data.data.chart_data_consumo);

                // Update Table (Bottom Right)
                updateRecentTable(data.data.recientes);
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

function setupIncomeFilters() {
    const btn = document.getElementById('applyFiltersBtn');

    // Initial Load (Current Year, All Months)
    loadIncomeChart();

    btn.addEventListener('click', () => {
        loadIncomeChart();
    });
}

function loadIncomeChart() {
    const year = document.getElementById('yearFilter').value;
    const month = document.getElementById('monthFilter').value;

    // Determine filter type based on inputs
    let filterType = 'year';
    if (month !== 'all') {
        filterType = 'month';
    }

    const url = `../controladores/dashboard.php?action=get_income_data&filter=${filterType}&year=${year}&month=${month}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update Title
                document.getElementById('incomeChartTitle').textContent = data.title;
                // Render Chart
                renderIncomeChart(data.labels, data.data, filterType);
            }
        })
        .catch(err => console.error(err));
}

function renderIncomeChart(labels, dataValues, type) {
    const ctx = document.getElementById('incomeChart').getContext('2d');

    if (incomeChartInstance) {
        incomeChartInstance.destroy();
    }

    // Gradient
    let gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.5)'); // Greenish
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    const config = {
        type: 'bar', // Bar chart feels solid for money
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: dataValues,
                backgroundColor: gradient,
                borderColor: '#10b981',
                borderWidth: 2,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '$' + value;
                        },
                        font: { family: "'Inter', sans-serif" }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: "'Inter', sans-serif" } }
                }
            }
        }
    };

    // If viewing by "Month" (daily data), a line chart might be better if many days?
    // User requested "Charts" (plural). Let's stick to Bar for consistency but maybe Line for daily trend?
    // Let's us Line for daily to differentiate.
    if (type === 'month') {
        config.type = 'line';
        config.data.datasets[0].fill = true;
        config.data.datasets[0].tension = 0.3;
        config.data.datasets[0].pointRadius = 4;
    }

    incomeChartInstance = new Chart(ctx, config);
}

function initConsumptionChart(monthlyData) {
    const ctx = document.getElementById('consumptionChart').getContext('2d');

    // Gradient
    let gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(67, 97, 238, 0.4)');
    gradient.addColorStop(1, 'rgba(67, 97, 238, 0.0)');

    consumptionChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            datasets: [{
                label: 'Consumo (m³)',
                data: monthlyData,
                borderColor: '#4361ee',
                backgroundColor: gradient,
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4361ee'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }, // Minimalist
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                x: { grid: { display: false } }
            }
        }
    });
}

// Helper: Animate Numbers
function animateValue(obj, start, end, duration) {
    if (!end) end = 0;
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        obj.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.textContent = obj.textContent; // Ensure plain text at end
        }
    };
    window.requestAnimationFrame(step);
}

// Helper: Format Currency
function formatCurrency(number) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(number);
}

function updateRecentTable(readings) {
    const tbody = document.getElementById('recentReadingsTable');
    tbody.innerHTML = '';

    if (!readings || readings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center">No hay datos recientes</td></tr>';
        return;
    }

    readings.forEach(r => {
        const row = document.createElement('tr');

        // Simple status logic for styling
        let badgeClass = 'status-pending';
        if (r.estado === 'Pagado') badgeClass = 'status-paid';
        if (r.estado === 'Vencido') badgeClass = 'status-overdue';

        row.innerHTML = `
            <td>
                <div style="font-weight:700; color:#334155">${r.medidor}</div>
            </td>
            <td>${r.nombre}</td>
            <td style="font-weight:600">${r.consumo} m³</td>
            <td><span class="status-badge ${badgeClass}">${r.estado}</span></td>
        `;
        tbody.appendChild(row);
    });
}
