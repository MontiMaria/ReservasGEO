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

    public function agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa) {
        try {

            return $this->RecursosRep->agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa);

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

<<<<<<< HEAD
    public function ver_lista_recursos($id, $id_nivel, $id_tipo) {
        try {

            return $this->RecursosRep->ver_lista_recursos($id, $id_nivel, $id_tipo);

=======
    public function verificar_reserva($id, $id_recurso) {
        try {

            return $this->RecursosRep->verificar_reserva($id, $id_recurso);
>>>>>>> origin/seba
        }
        catch(Exception $e) {
            Log::error("ERROR: ".$e->getMessage(), ['exception' => $e]);
            throw $e;
<<<<<<< HEAD

            
     public function actualizar_reservas_activas($id, $id_usuario, $id_nivel)
    {
        try {
            return $this->RecursosRep->actualizar_reservas_activas($id, $id_usuario, $id_nivel);
        } catch (Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine(), ['exception' => $e]);
            return $e;
        }
    }

    public function ver_listado_reservas_activas($id, Request $request)
    {
        $data = $request->all();
        try {
            $informe = $this->RecursosService->ver_listado_reservas_activas($id, $data['id_usuario'], $data['id_nivel'], $data['cant_por_pagina'] ?? null, $data['pagina'] ?? null);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        }catch (InvalidArgumentException $e) {
            // Caso específico: rol sin permisos
            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => $e->getMessage(),
            ], 403);
        }catch (Exception $e) {
            Log::error("CONTROLLER ERROR: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la obtencion de reservas activas' . $e->getMessage(),
            ], 500);
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
    
}
=======
        }
    }
}
>>>>>>> origin/seba
