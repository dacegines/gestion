<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatosEvidenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nombre;
    public $evidencia;
    public $periodicidad;
    public $responsable;
    public $fecha_limite_cumplimiento;
    public $origen_obligacion;
    public $clausula_condicionante_articulo;

    public function __construct($nombre, $evidencia, $periodicidad, $responsable, $fecha_limite_cumplimiento, $origen_obligacion, $clausula_condicionante_articulo)
    {
        $this->nombre = $nombre;
        $this->evidencia = $evidencia;
        $this->periodicidad = $periodicidad;
        $this->responsable = $responsable;
        $this->fecha_limite_cumplimiento = $fecha_limite_cumplimiento;
        $this->origen_obligacion = $origen_obligacion;
        $this->clausula_condicionante_articulo = $clausula_condicionante_articulo;
    }

    public function build()
    {
        return $this->view('emails.datos_evidencia')
            ->subject('Nueva evidencia agregada.')
            ->from('noreply@tu-dominio.com', 'Sistema de Notificaciones TDC')
            ->priority(1)
            ->with([
                'nombre' => $this->nombre,
                'evidencia' => $this->evidencia,
                'periodicidad' => $this->periodicidad,
                'responsable' => $this->responsable,
                'fecha_limite_cumplimiento' => $this->fecha_limite_cumplimiento,
                'origen_obligacion' => $this->origen_obligacion,
                'clausula_condicionante_articulo' => $this->clausula_condicionante_articulo,
            ]);
    }
}


