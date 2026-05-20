<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Статистика посещений</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        .chart-container { max-width: 800px; margin: 30px auto; }
        canvas { max-height: 400px; }
        h2 { text-align: center; }
    </style>
</head>
<body>
<h2>Уникальные посещения по часам (последние 48 ч)</h2>
<div class="chart-container">
    <canvas id="hourlyChart"></canvas>
</div>

<h2>Посещения по городам (топ‑10)</h2>
<div class="chart-container">
    <canvas id="cityChart"></canvas>
</div>

<script>
    // Линейный график (часы / уникальные посещения)
    const ctxLine = document.getElementById('hourlyChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: {!! json_encode($labels) !!},
            datasets: [{
                label: 'Уникальные IP за час',
                data: {!! json_encode($data) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: {
                    title: { display: true, text: 'Час' },
                    ticks: {
                        maxRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 12
                    }
                },
                y: {
                    title: { display: true, text: 'Количество уникальных посещений' },
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,      // шаг 1
                        precision: 0,     // только целые числа
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null;
                        }
                    }
                }
            },
            plugins: {
                tooltip: { mode: 'index', intersect: false }
            }
        }
    });

    // Круговая диаграмма (города)
    const ctxPie = document.getElementById('cityChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'pie',
        data: {
            labels: {!! json_encode($cities) !!},
            datasets: [{
                data: {!! json_encode($cityTotals) !!},
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#8E5EA2', '#3D9970', '#F012BE', '#01FF70'],
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'right' } }
        }
    });
</script>
</body>
</html>
