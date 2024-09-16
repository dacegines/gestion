@if($detalles->isEmpty())
    <p>No hay detalles disponibles para esta evidencia.</p>
@else
    <div class="header">
        <h5>Detalles de la Evidencia</h5>
    </div>
    <br>
    <div class="details-card">
        @foreach($detalles as $detalle)
            <div class="section-header bg-light-grey">
                <i class="fas fa-calendar"></i>
                <span>Fecha Límite:</span>
            </div>
            <p> <b>{{ \Carbon\Carbon::parse($detalle->fecha_limite_cumplimiento)->translatedFormat('d \d\e F \d\e Y') }}</b> </p>
            <!-- Agrega más detalles según necesites -->
        @endforeach
    </div>
@endif
