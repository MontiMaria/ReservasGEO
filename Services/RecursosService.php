<?php

namespace App\Services;

use App\Repositories\RecursosRepository;
use Illuminate\Support\Facades\Log;
use Exception;

class RecursosService
{
    private $RecursosRep;

    function __construct(RecursosRepository $RecursosRep)
    {
        $this->RecursosRep = $RecursosRep;
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

    public function verificar_reserva($id, $id_recurso) {
        try {

            return $this->RecursosRep->verificar_reserva($id, $id_recurso);
        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}