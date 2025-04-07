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
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment"></script>
<script>
    let chart = null;

    // Checkbox template
    const createCheckbox = (id, name) => `
        <div class="checkbox-item">
            <input type="checkbox" id="filter_${id}" name="specific_filters[]" value="${id}">
            <label for="filter_${id}" class="form-check-label">${name}</label>
        </div>
    `;

    // Initialize chart
    function initChart(data) {
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        
        if (chart) chart.destroy();
        
        chart = new Chart(ctx, {
            type: 'line',
            data: data,
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
                        bodyFont: { size: 12 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Pārdotā daudzums' },
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        ticks: { maxRotation: 45, minRotation: 45 }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initial empty chart
        initChart({ labels: [], datasets: [] });

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
                const labels = [...new Set(data.results.flatMap(d => Object.keys(d.data)))].sort();
                const colors = ['#4e73df','#1cc88a','#36b9cc','#f6c23e','#e74a3b'];
                
                const datasets = data.results.map((item, i) => ({
                    label: item.label,
                    data: labels.map(date => item.data[date] || 0),
                    borderColor: colors[i % colors.length],
                    backgroundColor: colors[i % colors.length] + '20',
                    tension: 0.3
                }));

                initChart({ labels, datasets });

            } catch(error) {
                alert('Kļūda: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Filtrēt';
            }
        });

        // Reset handler
        document.getElementById('resetFilters').addEventListener('click', () => {
            document.getElementById('filterForm').reset();
            document.getElementById('checkboxContainer').style.display = 'none';
            initChart({ labels: [], datasets: [] });
        });
    });
</script>
@endsection