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
        date: @json($salesData ?? []),
        genre: @json($genreData ?? []),
        author: @json($authorData ?? [])
    };

    let chart = null;

    // Prepare chart data - all as line charts now
    const prepareChartData = (rawData, label, chartType = 'line') => {
        const colors = {
            date: { border: 'rgba(54, 162, 235, 1)', hover: 'rgba(54, 162, 235, 0.8)' },
            genre: { border: 'rgba(255, 99, 132, 1)', hover: 'rgba(255, 99, 132, 0.8)' },
            author: { border: 'rgba(75, 192, 192, 1)', hover: 'rgba(75, 192, 192, 0.8)' },
            book: { border: 'rgba(153, 102, 255, 1)', hover: 'rgba(153, 102, 255, 0.8)' }
        };

        const typeColors = colors[chartType] || colors.date;
        
        // Format labels - handle both date-based and category-based data
        const formattedLabels = rawData.map(item => {
            // For date-based data
            const dateStr = item.label || item.sale_date;
            if (dateStr) {
                const parts = dateStr.split('-');
                if (parts.length === 3) {
                    return `${parts[2]}.${parts[1]}.${parts[0]}`;
                }
                return dateStr;
            }
            // For category-based data (genres/authors/books)
            return item.name || item.title || '';
        });
        
        return {
            labels: formattedLabels,
            datasets: [{
                label: label,
                data: rawData.map(item => Math.round(Number(item.quantity || item.total_quantity || 0))),
                backgroundColor: 'transparent',
                borderColor: typeColors.border,
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: typeColors.border,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: typeColors.hover,
                pointHoverBorderColor: '#fff',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                tension: 0.1,
                fill: false,
                type: 'line' // Force all charts to be line charts
            }]
        };
    };

    // Initialize chart - all as line charts
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
                            },
                            padding: 20,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.85)',
                        titleFont: { 
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: { 
                            size: 12 
                        },
                        padding: 12,
                        displayColors: false,
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
                            color: 'rgba(0,0,0,0.05)',
                            drawBorder: false
                        },
                        title: {
                            display: true,
                            text: 'Pārdotā daudzums',
                            font: {
                                size: 14,
                                weight: 'bold'
                            },
                            padding: { top: 10, bottom: 20 }
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            stepSize: 1,
                            padding: 5
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            },
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                            padding: 10
                        }
                    }
                },
                elements: {
                    line: {
                        borderWidth: 2,
                        tension: 0.1
                    },
                    point: {
                        radius: 4,
                        hoverRadius: 6
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
                        specificFilter.innerHTML = data.length 
                            ? data.map(item => `<option value="${item.id}">${item.name}</option>`).join('')
                            : '<option value="">Nav datu</option>';
                    })
                    .catch(() => {
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
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Unknown error');
                initChart(
                    data.results, 
                    formData.get('filter_type'), // Keep the type for color coding
                    formData.get('filter_type') === 'all' 
                        ? 'Pārdotā daudzums' 
                        : `Pārdotā daudzums (${data.label || formData.get('filter_type')})`
                );
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
        if (document.getElementById('filterType').value !== 'all') {
            document.getElementById('filterType').dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection