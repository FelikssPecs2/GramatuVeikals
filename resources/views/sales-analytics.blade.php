@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Pārdošanas analīze</h1>
    
    <!-- Chart Selector -->
    <div class="mb-4">
        <select id="chartSelector" class="form-select">
            <option value="date">Pārdošanas pēc datuma</option>
            <option value="genre">Pārdošanas pēc žanra</option>
            <option value="author">Pārdošanas pēc autora</option>
        </select>
    </div>

    <!-- Chart Container -->
    <div class="chart-container" style="position: relative; height:60vh; width:100%">
        <canvas id="analyticsChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Prepare chart data with better visibility settings
    const prepareChartData = (rawData, label, chartType = 'bar') => {
        const colors = {
            date: { bg: 'rgba(54, 162, 235, 0.7)', border: 'rgba(54, 162, 235, 1)' },
            genre: { bg: 'rgba(255, 99, 132, 0.7)', border: 'rgba(255, 99, 132, 1)' },
            author: { bg: 'rgba(75, 192, 192, 0.7)', border: 'rgba(75, 192, 192, 1)' }
        };

        const typeColors = colors[chartType] || colors.date;
        
        return {
            labels: rawData.map(item => item.label || item.sale_date),
            datasets: [{
                label: label,
                data: rawData.map(item => Number(item.quantity || item.total_quantity || 0)),
                backgroundColor: typeColors.bg,
                borderColor: typeColors.border,
                borderWidth: chartType === 'date' ? 3 : 1, // Thicker line for date chart
                pointBackgroundColor: '#fff',
                pointBorderColor: typeColors.border,
                pointRadius: chartType === 'date' ? 4 : 3, // Larger points for line chart
                pointHoverRadius: 6,
                tension: chartType === 'date' ? 0.1 : 0, // Slight curve for line chart
                fill: chartType === 'date' ? true : false, // Area under line
                type: chartType === 'date' ? 'line' : 'bar'
            }]
        };
    };

    const chartData = {
        date: prepareChartData(@json($salesData), 'Pārdotā daudzums pēc datuma', 'date'),
        genre: prepareChartData(@json($genreData), 'Pārdotā daudzums pēc žanra', 'genre'),
        author: prepareChartData(@json($authorData), 'Pārdotā daudzums pēc autora', 'author')
    };

    let chart = null;

    function initChart(type) {
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        
        if (chart) chart.destroy();
        
        chart = new Chart(ctx, {
            data: chartData[type],
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
                            }
                        }
                    }
                },
                elements: {
                    line: {
                        borderWidth: 3 // Thicker line
                    }
                }
            }
        });
    }

    // Initialize with date chart
    initChart('date');

    // Handle chart type change
    document.getElementById('chartSelector').addEventListener('change', function() {
        initChart(this.value);
    });
</script>

@endsection