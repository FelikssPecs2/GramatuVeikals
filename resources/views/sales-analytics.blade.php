@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1>Pārdošanas grafiki</h1>

    <!-- Sales Chart (By Date) -->
    <div class="mb-5">
        <h3>Pārdošanas pēc datuma</h3>
        <canvas id="salesChart"></canvas>
    </div>

    <!-- Genre Sales Chart (By Genre) -->
    <div class="mb-5">
        <h3>Pārdošanas pēc žanra</h3>
        <canvas id="genreSalesChart"></canvas>
    </div>

    <!-- Author Sales Chart (By Author) -->
    <div class="mb-5">
        <h3>Pārdošanas pēc autora</h3>
        <canvas id="authorSalesChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sales Chart (By Date)
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: @json($salesData->pluck('sale_date')),
            datasets: [{
                label: 'Pārdotā daudzums',
                data: @json($salesData->pluck('total_quantity')),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // Genre Sales Chart (By Genre)
    const genreCtx = document.getElementById('genreSalesChart').getContext('2d');
    new Chart(genreCtx, {
        type: 'bar',
        data: {
            labels: @json($genreSalesData->pluck('genre_name')),
            datasets: [{
                label: 'Pārdotā daudzums',
                data: @json($genreSalesData->pluck('total_quantity')),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgb(255, 99, 132)',
                borderWidth: 1
            }]
        }
    });

    // Author Sales Chart (By Author)
    const authorCtx = document.getElementById('authorSalesChart').getContext('2d');
    new Chart(authorCtx, {
        type: 'bar',
        data: {
            labels: @json($authorSalesData->pluck('author_name')),
            datasets: [{
                label: 'Pārdotā daudzums',
                data: @json($authorSalesData->pluck('total_quantity')),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        }
    });
</script>
@endsection