function createDoughnutChart(modalId, chartId) {
    let modal = document.getElementById(modalId);
    if (modal) {
        modal.addEventListener('shown.bs.modal', function() {
            let ctx = document.getElementById(chartId).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Obligación 1.1', 'Obligación 1.2', 'Obligación 1.3', 'Obligación 1.4'],
                    datasets: [{
                        data: [25, 25, 25, 25],
                        backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#5DADE2']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    createDoughnutChart('modalConservacion', 'myDoughnutChartConservacion');
    createDoughnutChart('modalConservacion2', 'myDoughnutChartConservacion2');
    createDoughnutChart('modalRenovacion', 'myDoughnutChartRenovacion');
    createDoughnutChart('modalTarifas', 'myDoughnutChartTarifas');
    createDoughnutChart('modalPagos', 'myDoughnutChartPagos');
    createDoughnutChart('modalReportes', 'myDoughnutChartReportes');
    createDoughnutChart('modalReportes2', 'myDoughnutChartReportes2');
    createDoughnutChart('modalPagos2', 'myDoughnutChartPagos2');
    createDoughnutChart('modalPagos3', 'myDoughnutChartPagos3');
});
