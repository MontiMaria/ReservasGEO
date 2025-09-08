<?php

namespace App\Http\Controllers;

use App\Services\RecursosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

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
            $informe = $this->RecursosService->cancelar_reserva($id, $data['id_reserva'], $data['motivo'], $data['id_usuario']);

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

    public function eliminar_recurso(Request $request, $id) {
        try {
            $id_institucion = $id;
            $id_recurso = $request->input('id_recurso');
            $id_usuario = $request->input('id_usuario');
            $motivo = $request->input('motivo', 'Eliminación de recurso');

            if (!$id_recurso || !$id_usuario) {
                throw new Exception("Los parámetros id_recurso y id_usuario son requeridos.");
            }

            $resultado = $this->RecursosService->eliminar_recurso($id_institucion, $id_recurso, $id_usuario, $motivo);

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'messages' => 'El recurso y sus reservas asociadas fueron eliminados correctamente.',
            ], 200);

        } catch (Exception $e) {
            Log::error("CONTROLLER ERROR al eliminar recurso: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'data' => null,
                'messages' => 'Error en la eliminación del recurso: ' . $e->getMessage(),
            ], 500);
        }
    }
}
