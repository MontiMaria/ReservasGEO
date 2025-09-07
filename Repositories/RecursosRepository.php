<?php

namespace App\Repositories;

use App\Models\Institucional\Recurso;
use App\Services\DataBaseService;
use App\Services\FuncionesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use InvalidArgumentException;
use DomainException;

class RecursosRepository
{
    private $dataBaseService;
    private $funcionesService;

    function __construct(DataBaseService $dataBaseService, FuncionesService $funcionesService)
    {
        $this->dataBaseService = $dataBaseService;
        $this->funcionesService = $funcionesService;
    }


    public function crear_recurso($id, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos) {
        $id_institucion = $id;
        $bloqueos_cargados = [];

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $nuevo_recurso = Recurso::on($conn_name)
                ->create([
                    'Recurso' => $recurso,
                    'Cantidad' => $cantidad,
                    'Descripcion' => $descripcion,
                    'ID_Tipo' => $id_tipo,
                    'ID_Nivel' => $id_nivel
                ]);

            // Si tiene bloqueos
            if(is_array($bloqueos) && count($bloqueos) > 0) {
                foreach($bloqueos as $b) {
                    $dia = $b['dia_semana'];
                    $hi = $b['hi'];
                    $hf = $b['hf'];
                    $causa = $b['causa'];

                    // Validaciones
                    if($dia < 1 || $dia > 5) {
                        throw new InvalidArgumentException("El valor ingresado se encuentra fuera del rango");
                    }
                    try {
                        $hi_p = Carbon::parse($hi);
                        $hf_p = Carbon::parse($hf);
                    }
                    catch (Exception $e) {
                        throw new InvalidArgumentException("Formato de hora inválido.");
                    }
                    if($hi_p >= $hf_p) {
                        throw new DomainException("La hora de incio es mayor o igual a la hora de finalizacion");
                    }

                    $bloqueo = $nuevo_recurso->bloqueos()->create([
                        'Dia_Semana' => $dia,
                        'HI' => $hi,
                        'HF' => $hf,
                        'ID_Nivel' => $id_nivel,
                        'Causa' => $causa
                    ]);

                    $bloqueos_cargados[] = $bloqueo->toArray();
                }
            }

            DB::connection($conn_name)->commit();

            return [
                'Recurso' => $nuevo_recurso,
                'Bloqueos' => $bloqueos_cargados
            ];
        }
        catch(Exception $e) {
            DB::connection($conn_name)->rollBack();
            throw $e;
        }
    }

    public function modificar_cantidad($id, $id_recurso, $cantidad) {
        $id_institucion = $id;

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $recurso = Recurso::on($conn_name)->findOrFail($id_recurso);

            $recurso->Cantidad = $cantidad;
            $recurso->save();

            DB::connection($conn_name)->commit();
            return $recurso;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function cancelar_reserva($id, $id_reserva, $motivo, $id_usuario) {
        $id_institucion = $id;

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $reserva = RecursoReserva::on($conn_name)->findOrFail($id_reserva);

            $reserva->B = 1;
            $reserva->B_Motivo = $motivo;
            $reserva->Fecha_B = now()->toDateString();
            $reserva->Hora_B = now()->toTimeString();
            $reserva->ID_Usuario_B = $id_usuario;
            $reserva->save();

            DB::connection($conn_name)->commit();
            return $reserva;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function eliminar_bloqueo($id, $id_bloqueo, $id_usuario) {
        $id_institucion = $id;

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $bloqueo = RecursoBloqueo::on($conn_name)->findOrFail($id_bloqueo);

            $bloqueo->B = 1;
            $bloqueo->Fecha_B = now()->toDateString();
            $bloqueo->Hora_B = now()->toTimeString();
            $bloqueo->ID_Usuario_B = $id_usuario;
            $bloqueo->save();

            DB::connection($conn_name)->commit();
            return $bloqueo;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function actualizar_reservas_activas($id,$id_usuario, $id_nivel){

        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();
            $fechaActual = Carbon::now()->format('Y-m-d');
            $horaActual = Carbon::now()->format('H:i:s');

            $reservas = RecursoReserva::on($connection)
                ->where('B','=',0)
                ->where(function($query) use ($fechaActual, $horaActual, $id_nivel){
                    $query->where('Fecha_R', '<', $fechaActual)
                        ->where('ID_Nivel',$id_nivel)
                        ->orWhere(function($q) use ($fechaActual, $horaActual){
                            $q->where('Fecha_R',$fechaActual)
                                ->where('Hora_Fin','<=', $horaActual);
                        });
                })
                ->update(['B' => '1', 'B_Motivo' => 'Reserva expirada']);

            return "Reservas actualizadas";

        } catch(Exception $e) {
            return $e->getMessage();
        }
    }

    public function ver_listado_reservas_activas($id,$id_usuario, $id_nivel, $cant_por_pagina, $pagina){

        $this->actualizar_reservas_activas($id,$id_usuario, $id_nivel);
        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();
            $rol = Personal::on($connection)
                ->select('Tipo')
                ->where('ID',$id_usuario)
                ->first();
            $cant_por_pagina = $cant_por_pagina ?? 15;
            $pagina = $pagina ?? 1;

            if($rol->Tipo == 'DI'){
                $reservas = RecursoReserva::on($connection)
                    ->from('recursos_reservas as rr')
                    ->select('recursos.Recurso','rr.*','personal.Nombre','personal.Apellido')
                    ->join('recursos','recursos.ID','=','rr.ID_Recurso')
                    ->join('personal','personal.ID','=','rr.ID_Docente')
                    //se le podría agregar un join con materias para que traiga el nombre de la materia también
                    //->join('materias','materias.ID','=','rr.ID_Materia')
                    ->where('rr.B','=',0)
                    ->where('rr.ID_Nivel',$id_nivel)
                    ->orderBy('rr.Fecha_R','asc')
                    ->orderBy('rr.Hora_Inicio','asc');
            } elseif (in_array($rol->Tipo, ['PF', 'MI', 'MG'])) {
                $reservas = RecursoReserva::on($connection)
                    ->from('recursos_reservas as rr')
                    ->select('recursos.Recurso','rr.*')
                    ->join('recursos','recursos.ID','=','rr.ID_Recurso')
                    //se le podría agregar un join con materias para que traiga el nombre de la materia también
                    //->join('materias','materias.ID','=','rr.ID_Materia')
                    ->where('rr.B','=',0)
                    ->where('rr.ID_Nivel',$id_nivel)
                    ->where('rr.ID_Docente',$id_usuario)
                    ->orderBy('rr.Fecha_R','asc')
                    ->orderBy('rr.Hora_Inicio','asc');
            } else {
                throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas activas.",403);
            }

            $resultado = $reservas->paginate($cant_por_pagina,['*'],'pagina', $pagina);

            $data = [
                'data' => $resultado->items(),
                'meta' => [
                    'total' => $resultado->total(),
                    'current_page' => $resultado->currentPage(),
                    'per_page' => $resultado->perPage(),
                    'last_page' => $resultado->lastPage(),
                ],
                'links' => [
                    'first' => $resultado->url(1),
                    'last' => $resultado->url($resultado->lastPage()),
                    'prev' => $resultado->previousPageUrl(),
                    'next' => $resultado->nextPageUrl(),
                ]
            ];
            return $data;
        } catch (InvalidArgumentException $e) {
            // Caso específico: rol sin permisos
            throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas activas.",403);
        } catch(Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine());
            return $e->getMessage();
        }
    }
}
