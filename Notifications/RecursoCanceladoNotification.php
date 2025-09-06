<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class RecursoCanceladoNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Aquí podrías pasar datos si quisieras, como el nombre del recurso.
        // public function __construct($recurso)
    }

    /**
     * 
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * 
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        // Este es el array de datos que se guardará como un JSON en la tabla de notificaciones.
        return [
            'titulo' => 'Reserva Cancelada',
            'mensaje' => 'Una de tus reservas ha sido cancelada debido a que el recurso ya no está disponible.'
            // 'recurso_id' => $this->recurso->ID // Ejemplo si pasaras el recurso en el constructor
        ];
    }
}