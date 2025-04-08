@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Pārdošanas analīze</h1>
    
    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="filterType" class="form-label">Filtrēt pēc:</label>
                        <select id="filterType" class="form-select" name="filter_type">
                            <option value="all">Visi dati</option>
                            <option value="genre">Žanrs</option>
                            <option value="author">Autors</option>
                            <option value="book">Grāmata</option>
                        </select>
                    </div>
                    
                    <div class="col-md-6" id="checkboxContainer" style="display:none;">
                        <div class="row g-3">
                            <!-- Checkboxes will be loaded here -->
                            <div id="checkboxesGroup" class="col-12"></div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="startDate" class="form-label">No datuma:</label>
                                <input type="date" id="startDate" class="form-control" name="start_date">
                            </div>
                            <div class="col-12">
                                <label for="endDate" class="form-label">Līdz datumam:</label>
                                <input type="date" id="endDate" class="form-control" name="end_date">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Filtrēt</button>
                    <button type="button" id="resetFilters" class="btn btn-outline-secondary">Atiestatīt</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="chart-container" style="position: relative; height:60vh; width:100%">
        <canvas id="analyticsChart"></canvas>
    </div>
</div>

<style>
    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.5rem;
        max-height: 200px;
        overflow-y: auto;
        padding: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.25rem;
    }
    
    .checkbox-item input[type="checkbox"] {
        width: 1.2em;
        height: 1.2em;
        margin: 0;
    }

    .btn-loading {
        position: relative;
        pointer-events: none;
    }
    .btn-loading:after {
        content: "";
        position: absolute;
        width: 16px;
        height: 16px;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: auto;
        border: 2px solid transparent;
        border-top-color: #ffffff;
        border-radius: 50%;
        animation: button-loading-spinner 1s ease infinite;
    }
    @keyframes button-loading-spinner {
        from { transform: rotate(0turn); }
        to { transform: rotate(1turn); }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
<script>
    let chart = null;

    // Initialize chart with empty data
    function initChart() {
        const ctx = document.getElementById('analyticsChart').getContext('2d');

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: []
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true, // Atkal iestatām uz true, lai rādītu 0
                        title: { display: true, text: 'Pārdotā daudzums' },
                        ticks: {
                            precision: 0,
                            callback: function(value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        }
                    },
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day',
                            tooltipFormat: 'YYYY-MM-DD',
                            displayFormats: {
                                day: 'MMM D'
                            }
                        },
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                },
                elements: {
                    point: {
                        radius: function(context) {
                            return context.raw.y > 0 ? 4 : 0; // Punktus rādām tikai ja vērtība > 0
                        },
                        hoverRadius: function(context) {
                            return context.raw.y > 0 ? 6 : 0;
                        }
                    },
                    line: {
                        borderWidth: 2 // Vienmēr rādām līniju, pat ja vērtība ir 0
                    }
                },
                layout: {
                    padding: {
                        top: 10,
                        right: 20,
                        bottom: 10,
                        left: 20
                    }
                }
            }
        });
    }

    // Function to load initial data
    async function loadInitialData() {
        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.classList.add('btn-loading');
        submitBtn.innerHTML = 'Ielādē...';

        try {
            const response = await fetch('/sales-analytics/filter', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    filter_type: 'all',
                    _token: document.querySelector('meta[name="csrf-token"]').content
                })
            });

            const data = await response.json();

            if(!data.success) throw new Error(data.message);

            // Prepare chart data
            const allDates = new Set();
            const datasets = [];
            const colors = ['#4e73df'];

            // Process results - NEKĀRTOSIM datus, lai saglabātu visus datumus
            data.results.forEach((result, index) => {
                const dates = Object.keys(result.data).sort();

                // Saglabājam visus datumus, pat tos kur pārdošana = 0
                dates.forEach(date => allDates.add(date));

                // Izveidojam datasetu ar VISIEM datumiem
                datasets.push({
                    label: result.label,
                    data: dates.map(date => ({
                        x: date,
                        y: result.data[date] || 0 // Ja nav datu, iestatām uz 0
                    })),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '20',
                    tension: 0.3,
                    fill: false,
                    spanGaps: true // Ļauj līnijai turpināties pāri datu spraugām
                });
            });

            // Atjauninām grafiku
            chart.data.labels = Array.from(allDates).sort();
            chart.data.datasets = datasets;
            chart.update();

        } catch(error) {
            console.error('Error:', error);
            alert('Kļūda ielādējot sākotnējos datus: ' + error.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.classList.remove('btn-loading');
            submitBtn.innerHTML = 'Filtrēt';
        }
    }

    // Checkbox template
    const createCheckbox = (id, name) => `
        <div class="checkbox-item">
            <input type="checkbox" id="filter_${id}" name="specific_filters[]" value="${id}">
            <label for="filter_${id}" class="form-check-label">${name}</label>
        </div>
    `;

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize empty chart
        initChart();

        // Load initial data
        loadInitialData();

        // Filter type change handler
        document.getElementById('filterType').addEventListener('change', async function() {
            const container = document.getElementById('checkboxContainer');
            const group = document.getElementById('checkboxesGroup');

            if(this.value === 'all') {
                container.style.display = 'none';
                group.innerHTML = '';
                return;
            }

            container.style.display = 'block';
            group.innerHTML = '<div class="spinner-border" role="status"></div>';

            try {
                const response = await fetch(`/get-list/${this.value}-list`);
                const data = await response.json();

                group.innerHTML = `
                    <div class="checkbox-group">
                        ${data.map(item => createCheckbox(item.id, item.name)).join('')}
                    </div>
                `;

            } catch(error) {
                group.innerHTML = '<div class="text-danger">Kļūda ielādējot datus</div>';
            }
        });

        // Form submit handler
        document.getElementById('filterForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.classList.add('btn-loading');
            submitBtn.innerHTML = 'Filtrē...';

            try {
                const response = await fetch('/sales-analytics/filter', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if(!data.success) throw new Error(data.message);

                // Prepare chart data
                const allDates = new Set();
                const datasets = [];
                const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'];

                // Process each result - saglabājam VISUS datumus
                data.results.forEach((result, index) => {
                    const dates = Object.keys(result.data).sort();

                    // Saglabājam visus datumus
                    dates.forEach(date => allDates.add(date));

                    // Izveidojam datasetu ar visiem datumiem
                    datasets.push({
                        label: result.label,
                        data: dates.map(date => ({
                            x: date,
                            y: result.data[date] || 0 // Ja nav datu, iestatām uz 0
                        })),
                        borderColor: colors[index % colors.length],
                        backgroundColor: colors[index % colors.length] + '20',
                        tension: 0.3,
                        fill: false,
                        spanGaps: true // Ļauj līnijai turpināties
                    });
                });

                // Atjauninām grafiku
                chart.data.labels = Array.from(allDates).sort();
                chart.data.datasets = datasets;
                chart.update();

            } catch(error) {
                console.error('Error:', error);
                alert('Kļūda: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.classList.remove('btn-loading');
                submitBtn.innerHTML = 'Filtrēt';
            }
        });

        // Reset handler
        document.getElementById('resetFilters').addEventListener('click', () => {
            document.getElementById('filterForm').reset();
            document.getElementById('checkboxContainer').style.display = 'none';
            loadInitialData();
        });
    });
</script>
@endsection