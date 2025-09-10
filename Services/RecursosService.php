<?php

namespace App\Services;

use App\Repositories\RecursosRepository;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\RecursoCanceladoNotification;
use Exception;
use Illuminate\Support\Facades\Notification;

class RecursosService
{
    private $RecursosRep;
    protected $dataBaseService;

    public function __construct(RecursosRepository $recursosRep, DataBaseService $dataBaseService)
    {
        $this->RecursosRep = $recursosRep;
        $this->dataBaseService = $dataBaseService;
    }

    public function crear_recurso($id, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos)
    {
        try {

            return $this->RecursosRep->crear_recurso($id, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function modificar_cantidad($id, $id_recurso, $cantidad) {
        try {

            return $this->RecursosRep->modificar_cantidad($id, $id_recurso, $cantidad);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function cancelar_reserva($id, $id_reserva, $motivo, $id_usuario) {
        try {

            return $this->RecursosRep->cancelar_reserva($id, $id_reserva, $motivo, $id_usuario);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function eliminar_bloqueo($id, $id_bloqueo, $id_usuario) {
        try {

            return $this->RecursosRep->eliminar_bloqueo($id, $id_bloqueo, $id_usuario);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function eliminar_recurso($id, $id_recurso, $id_usuario, $motivo) 
    {
        try {
            
            return $this->RecursosRep->eliminar_recurso($id, $id_recurso, $id_usuario, $motivo);

        } catch (Exception $e) {
            Log::error("ERROR al eliminar el recurso {$id_recurso}: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}
