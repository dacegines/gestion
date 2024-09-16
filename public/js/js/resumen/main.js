document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('vencidasPorVencerCompletasChart').getContext('2d');

    const data = {
        labels: window.chartData.fechas, // Etiquetas de fechas
        datasets: [
            {
                label: 'Vencidas',
                data: window.chartData.vencidas, // Datos de vencidas
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: false,
                borderWidth: 2,
            },
            {
                label: 'Por Vencer',
                data: window.chartData.porVencer, // Datos de por vencer
                borderColor: 'rgba(255, 206, 86, 1)',
                backgroundColor: 'rgba(255, 206, 86, 0.2)',
                fill: false,
                borderWidth: 2,
            },
            {
                label: 'Completas',
                data: window.chartData.completas, // Datos de completas
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: false,
                borderWidth: 2,
            }
        ]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Fecha Límite'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Cantidad'
                    },
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // Define que el tamaño del paso sea 1
                        callback: function(value) {
                            if (Number.isInteger(value)) {
                                return value; // Mostrar solo números enteros
                            }
                        }
                    }
                }
            }
        }
    };

    new Chart(ctx, config);
});

