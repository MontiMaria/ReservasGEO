<?php

namespace App\Http\Controllers;

use App\Services\RecursosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use InvalidArgumentException;

class RecursosController extends Controller
{
    protected $RecursosService;

    public function __construct(RecursosService $RecursosService)
    {
        $this->RecursosService = $RecursosService;
    }

    public function crear_recurso($id, Request $request)
    {
        $data = $request->all();

        $bloqueos = $data['bloqueos'] ?? [];

        if(is_string($bloqueos)) {
            $bloqueos = json_decode($bloqueos, true) ?: [];
        }

        if(!is_array($bloqueos)) {
            $bloqueos = [];
        }

        try {
            $informe = $this->RecursosService->crear_recurso($id, $data['recurso'], $data['cantidad'], $data['descripcion'], $data['id_tipo'], $data['id_nivel'], $bloqueos);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        }
        catch(Exception $e) {
            Log::error("CONTROLLER ERROR: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la creacion del recurso'.$e->getMessage(),
            ], 500);
        }
    }

    public function modificar_cantidad($id, Request $request) {

        $data = $request->all();
        
        try {
            $informe = $this->RecursosService->modificar_cantidad($id, $data['id_recurso'], $data['cantidad']);
            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        }
        catch(Exception $e) {
            Log::error("CONTROLLER ERROR: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la modificación de la cantidad del recurso'.$e->getMessage(),
            ], 500);
        }
        
    }

    public function cancelar_reserva($id, Request $request) {

        $data = $request->all();

        try {
            $informe = $this->RecursosService->cancelar_reserva($id, $data['id_reserva'], $data['motivo']);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        }
        catch(Exception $e) {
            Log::error("CONTROLLER ERROR: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la cancelación de la reserva'.$e->getMessage(),
            ], 500);
        }
    }

    public function eliminar_bloqueo($id, Request $request) {

        $data = $request->all();

        try {
            $informe = $this->RecursosService->eliminar_bloqueo($id, $data['id_bloqueo'], $data['id_usuario']);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        }
        catch(Exception $e) {
            Log::error("CONTROLLER ERROR: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la eliminación del bloqueo'.$e->getMessage(),
            ], 500);
        }
    }

    public function actualizar_reservas_activas($id, Request $request)
    {
        $data = $request->all();
        try {
            $informe = $this->RecursosService->actualizar_reservas_activas($id, $data['id_usuario'], $data['id_nivel']);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        } catch (Exception $e) {
            Log::error("CONTROLLER ERROR: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la actualizacion de reservas activas' . $e->getMessage(),
            ], 500);
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

    public function ver_listado_reservas_antiguas($id, Request $request)
    {
        $data = $request->all();
        try {
            $informe = $this->RecursosService->ver_listado_reservas_antiguas($id, $data['id_usuario'], $data['id_nivel'], $data['cant_por_pagina'] ?? null, $data['pagina'] ?? null);

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
                'messages' => 'Error en la obtencion de reservas históricas' . $e->getMessage(),
            ], 500);
        }
    }

    public function traer_recursos($id, Request $request)
    {
        $data = $request->all();
        try {
            $informe = $this->RecursosService->traer_recursos($id, $data['id_usuario'], $data['id_nivel']);

            return response()->json([
                'success' => true,
                'data' => $informe,
                'messages' => '',
            ]);
        } catch (Exception $e) {
            Log::error("CONTROLLER ERROR: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la obtencion de recursos' . $e->getMessage(),
            ], 500);
        }
    }

}
