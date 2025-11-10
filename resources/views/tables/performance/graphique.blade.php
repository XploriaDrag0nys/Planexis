@extends('template')

@section('title', 'Performance mensuelle')

@section('content')

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>📊 Suivi mensuel de la performance – <strong>{{ $table->name }}</strong></h1>
            <a href="{{ route('table.show', $table->id) }}" class="btn btn-outline-secondary">⬅ Retour au tableau</a>
        </div>

        <div class="card p-4">
            <h5 class="mb-3">📈 Évolution de la conformité (12 derniers mois)</h5>
            <canvas id="performanceChart" height="200"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($chartData->pluck('month')) !!};
        const dataPoints = {!! json_encode($chartData->pluck('rate')) !!};
        const target = {{ $target }};

        // Calcul des couleurs selon performance
        const barColors = dataPoints.map(rate => {
            if (rate === null) return 'rgba(200,200,200,0.3)'; // gris si vide
            return rate < target ? 'rgba(255, 99, 132, 0.8)' : 'rgba(54, 162, 235, 0.8)';
        });

        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Conformité (%)',
                    data: dataPoints,
                    backgroundColor: barColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 25,
                            callback: value => value + '%'
                        },
                        title: { display: true, text: '%' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>

@endpush
<style>
    #performanceChart {
        max-height: 300px;
    }
</style>