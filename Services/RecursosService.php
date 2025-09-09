<?php

namespace App\Services;

use App\Repositories\RecursosRepository;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\RecursoCanceladoNotification;
use Exception;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

class RecursosService
{
    private $RecursosRep;
    protected $dataBaseService;

    public function __construct(RecursosRepository $recursosRep, DataBaseService $dataBaseService)
    {
        $this->RecursosRep = $recursosRep;
        $this->dataBaseService = $dataBaseService;
    }

    public function crear_recurso($id, $id_usuario, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos)
    {
        try {

            return $this->RecursosRep->crear_recurso($id, $id_usuario, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos);

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

    public function agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa, $id_usuario) {
        try {

            return $this->RecursosRep->agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa, $id_usuario);

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

    public function eliminar_recurso($id, $id_recurso, $id_usuario)
    {
        $id_institucion = $id;
        try {
            //$conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

            $userIds = $this->RecursosRep->eliminar_recurso($id, $id_recurso, $id_usuario);

            if ($userIds->isNotEmpty()) {
                $usuarios_a_notificar = User::find($userIds);
                Notification::send($usuarios_a_notificar, new RecursoCanceladoNotification());
            }

            return "El recurso y todas sus reservas han sido eliminados correctamente.";

        } catch (Exception $e) {
            Log::error("ERROR al eliminar el recurso {$id_recurso}: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function ver_lista_recursos($id, $id_nivel, $id_tipo) {
        try {

            return $this->RecursosRep->ver_lista_recursos($id, $id_nivel, $id_tipo);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function crear_reserva($id, $id_recurso, $id_usuario, $fecha_r, $hora_inicio, $hora_fin, $id_nivel, $id_curso, $id_materia, $actividad) {
        try {

            return $this->RecursosRep->crear_reserva($id, $id_recurso, $id_usuario, $fecha_r, $hora_inicio, $hora_fin, $id_nivel, $id_curso, $id_materia, $actividad);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public function actualizar_reservas_activas($id, $id_usuario, $id_nivel)
    {
        try {
            return $this->RecursosRep->actualizar_reservas_activas($id, $id_usuario, $id_nivel);
        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }

    public function ver_listado_reservas_activas($id, $id_usuario, $id_nivel, $cant_por_pagina, $pagina)
    {
         try {
            return $this->RecursosRep->ver_listado_reservas_activas($id, $id_usuario, $id_nivel, $cant_por_pagina, $pagina);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas activas.",403);

        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }

    public function ver_listado_reservas_antiguas($id, $id_usuario, $id_nivel, $cant_por_pagina = 15, $pagina = 1)
    {
        try {
            return $this->RecursosRep->ver_listado_reservas_antiguas($id, $id_usuario, $id_nivel, $cant_por_pagina, $pagina);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas históricas.",403);

        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }


    public function traer_recursos($id, $id_usuario, $id_nivel)
    {
        try {
            return $this->RecursosRep->traer_recursos($id, $id_usuario, $id_nivel);
        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }

    public function listar_materias($id, $id_usuario, $id_nivel)
    {
        try {
            return $this->RecursosRep->listar_materias($id, $id_usuario, $id_nivel);
        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }

    public function verificarReservas($id, $id_recurso, $nuevaCantidad)
    {
        try {

            return $this->RecursosRep->verificarReservas($id, $id_recurso, $nuevaCantidad);

        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }
}

