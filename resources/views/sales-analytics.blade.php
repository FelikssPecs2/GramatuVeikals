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
    const chartData = {
        date: {
            labels: @json($salesData->pluck('sale_date')),
            datasets: [{
                label: 'Pārdotā daudzums pēc datuma',
                data: @json($salesData->pluck('total_quantity')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                type: 'line'
            }]
        },
        genre: {
            labels: @json($genreData->pluck('label')),
            datasets: [{
                label: 'Pārdotā daudzums pēc žanra',
                data: @json($genreData->pluck('quantity')),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                type: 'bar'
            }]
        },
        author: {
            labels: @json($authorData->pluck('label')),
            datasets: [{
                label: 'Pārdotā daudzums pēc autora',
                data: @json($authorData->pluck('quantity')),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                type: 'bar'
            }]
        }
    };

    const ctx = document.getElementById('analyticsChart').getContext('2d');
    const chart = new Chart(ctx, {
        data: chartData.date,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
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
                    title: {
                        display: true,
                        text: 'Pārdotā daudzums'
                    }
                }
            }
        }
    });

    document.getElementById('chartSelector').addEventListener('change', function() {
        const selectedValue = this.value;
        chart.data = chartData[selectedValue];
        chart.update();
    });
</script>
@endsection