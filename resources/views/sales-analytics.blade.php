@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Pārdošanas analīze</h1>
    
    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label for="filterType" class="form-label">Filtrēt pēc:</label>
                        <select id="filterType" class="form-select" name="filter_type">
                            <option value="all">Visi dati</option>
                            <option value="genre">Žanrs</option>
                            <option value="author">Autors</option>
                            <option value="book">Grāmata</option>
                        </select>
                    </div>
                    <div class="col-md-3" id="specificFilterContainer" style="display:none;">
                        <label for="specificFilter" class="form-label" id="specificFilterLabel">Izvēlieties:</label>
                        <select id="specificFilter" class="form-select" name="specific_filter" disabled>
                            <option value="">Ielādē...</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="startDate" class="form-label">No datuma:</label>
                        <input type="date" id="startDate" class="form-control" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="endDate" class="form-label">Līdz datumam:</label>
                        <input type="date" id="endDate" class="form-control" name="end_date">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Filtrēt</button>
                <button type="button" id="resetFilters" class="btn btn-outline-secondary mt-3">Atiestatīt</button>
            </form>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="chart-container" style="position: relative; height:60vh; width:100%">
        <canvas id="analyticsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
<script>
    // Initialize with default data
    const initialData = {
        date: @json($salesData),
        genre: @json($genreData),
        author: @json($authorData)
    };

    let chart = null;

    // Prepare chart data
    const prepareChartData = (rawData, label, chartType = 'bar') => {
        const colors = {
            date: { bg: 'rgba(54, 162, 235, 0.7)', border: 'rgba(54, 162, 235, 1)' },
            genre: { bg: 'rgba(255, 99, 132, 0.7)', border: 'rgba(255, 99, 132, 1)' },
            author: { bg: 'rgba(75, 192, 192, 0.7)', border: 'rgba(75, 192, 192, 1)' },
            book: { bg: 'rgba(153, 102, 255, 0.7)', border: 'rgba(153, 102, 255, 1)' }
        };

        const typeColors = colors[chartType] || colors.date;
        
        // Format dates for labels
        const formattedLabels = rawData.map(item => {
            const dateStr = item.label || item.sale_date;
            if (!dateStr) return '';
            
            // Parse date string (assuming format is YYYY-MM-DD)
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}.${parts[1]}.${parts[0]}`; // DD.MM.YYYY format
            }
            return dateStr;
        });
        
        return {
            labels: formattedLabels,
            datasets: [{
                label: label,
                data: rawData.map(item => Math.round(Number(item.quantity || item.total_quantity || 0))),
                backgroundColor: typeColors.bg,
                borderColor: typeColors.border,
                borderWidth: chartType === 'date' ? 3 : 1,
                pointBackgroundColor: '#fff',
                pointBorderColor: typeColors.border,
                pointRadius: chartType === 'date' ? 4 : 3,
                pointHoverRadius: 6,
                tension: chartType === 'date' ? 0.1 : 0,
                fill: chartType === 'date' ? true : false,
                type: chartType === 'date' ? 'line' : 'bar'
            }]
        };
    };

    // Initialize chart
    function initChart(data, type, label) {
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        
        if (chart) chart.destroy();
        
        chart = new Chart(ctx, {
            data: prepareChartData(data, label, type),
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 14 },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        title: {
                            display: true,
                            text: 'Pārdotā daudzums',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            stepSize: 1,
                            callback: function(value) {
                                if (value % 1 === 0) {
                                    return value;
                                }
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                },
                elements: {
                    line: {
                        borderWidth: 3
                    }
                }
            }
        });
    }

    // Initialize with default data
    document.addEventListener('DOMContentLoaded', function() {
        initChart(initialData.date, 'date', 'Pārdotā daudzums pēc datuma');

        // Handle filter type change
        document.getElementById('filterType').addEventListener('change', function() {
            const specificFilterContainer = document.getElementById('specificFilterContainer');
            const specificFilter = document.getElementById('specificFilter');
            
            if (this.value === 'all') {
                specificFilterContainer.style.display = 'none';
                specificFilter.disabled = true;
            } else {
                specificFilterContainer.style.display = 'block';
                specificFilter.disabled = false;
                
                // Update label
                const labels = {
                    genre: 'Izvēlieties žanru:',
                    author: 'Izvēlieties autoru:',
                    book: 'Izvēlieties grāmatu:'
                };
                document.getElementById('specificFilterLabel').textContent = labels[this.value];
                
                // Clear previous options
                specificFilter.innerHTML = '<option value="">Ielādē...</option>';
                
                // Load appropriate options
                fetch(`/get-list/${this.value}-list`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            specificFilter.innerHTML = '<option value="">Nav datu</option>';
                            return;
                        }
                        specificFilter.innerHTML = data.map(item => 
                            `<option value="${item.id}">${item.name}</option>`
                        ).join('');
                    })
                    .catch(error => {
                        console.error('Error loading filter options:', error);
                        specificFilter.innerHTML = '<option value="">Kļūda ielādējot</option>';
                    });
            }
        });

        // Handle form submission
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitButton = e.target.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';

            fetch(`/sales-analytics/filter`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Unknown error occurred');
                    }

                    let label = 'Pārdotā daudzums';
                    let chartType = 'date';
                    
                    const filterType = formData.get('filter_type');
                    if (filterType !== 'all') {
                        label = `Pārdotā daudzums (${data.label || filterType})`;
                        chartType = filterType;
                    }
                    
                    initChart(data.results, chartType, label);
                })
                .catch(error => {
                    console.error('Filter error:', error);
                    alert('Filtrēšanas kļūda: ' + error.message);
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.textContent = 'Filtrēt';
                });
        });

        // Reset filters
        document.getElementById('resetFilters').addEventListener('click', function() {
            document.getElementById('filterForm').reset();
            document.getElementById('specificFilterContainer').style.display = 'none';
            document.getElementById('specificFilter').disabled = true;
            initChart(initialData.date, 'date', 'Pārdotā daudzums pēc datuma');
        });

        // Trigger initial filter type change if not 'all'
        const initialFilterType = document.getElementById('filterType').value;
        if (initialFilterType !== 'all') {
            document.getElementById('filterType').dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection 