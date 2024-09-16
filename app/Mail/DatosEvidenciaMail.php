<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DatosEvidenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $datos;

    public function __construct($datos)
    {
        $this->datos = $datos;
    }

    public function build()
    {
        return $this->view('emails.datos_evidencia')
        ->subject('Nueva evidencia agregada.')
        ->from('noreply@tu-dominio.com', 'Sistema de Notificaciones TDC')
        //->replyTo('soporte@tu-dominio.com', 'Soporte Técnico')
        //->cc('manager@tu-dominio.com', 'Manager')
        //->bcc('auditor@tu-dominio.com', 'Auditor')
        ->priority(1)
        /*->attach(storage_path('app/public/reporte.pdf'), [
            'as' => 'ReporteAnual.pdf',
            'mime' => 'application/pdf',
        ])*/
                    ->with('datos', $this->datos);
    }
}


