<?php

namespace App\Repositories;

use App\Models\Institucional\Recurso;
use App\Services\DataBaseService;
use App\Services\FuncionesService;
use Carbon\Carbon;
use App\Models\Institucional\RecursoBloqueo;
use App\Models\Institucional\RecursoReserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
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
        $datetimeArg = Carbon::now('America/Argentina/Buenos_Aires');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $reserva = RecursoReserva::on($conn_name)->findOrFail($id_reserva);

            $reserva->B = 1;
            $reserva->B_Motivo = $motivo;
            $reserva->Fecha_B = $datetimeArg;
            $reserva->Hora_B = $datetimeArg;
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
        $datetimeArg = Carbon::now('America/Argentina/Buenos_Aires');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $bloqueo = RecursoBloqueo::on($conn_name)->findOrFail($id_bloqueo);

            $bloqueo->B = 1;
            $bloqueo->Fecha_B = $datetimeArg;
            $bloqueo->Hora_B = $datetimeArg;
            $bloqueo->ID_Usuario_B = $id_usuario;
            $bloqueo->save();

            DB::connection($conn_name)->commit();
            return $bloqueo;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function eliminar_recurso($id, $id_recurso, $id_usuario, $motivo): Collection {
        $id_institucion = $id;
        $datetimeArg = Carbon::now('America/Argentina/Buenos_Aires');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        DB::connection($conn_name)->beginTransaction();

        try {
            $userIds = RecursoReserva::on($conn_name)
                ->where('ID_Recurso', $id_recurso)
                ->where('B', 0)
                ->distinct()
                ->pluck('ID_Usuario_B');

            $recursoActualizado = Recurso::on($conn_name)
                ->where('ID', $id_recurso)
                ->update(['B' => 1]);

            if ($recursoActualizado === 0) {
                throw new Exception("No se encontró el recurso con ID {$id_recurso} para dar de baja.");
            }

            RecursoBloqueo::on($conn_name)
                ->where('ID_Recurso', $id_recurso)
                ->update([
                    'B' => 1,
                    'Fecha_B' => $datetimeArg,
                    'Hora_B' => $datetimeArg,
                    'ID_Usuario_B' => $id_usuario
                ]);

            RecursoReserva::on($conn_name)
                ->where('ID_Recurso', $id_recurso)
                ->update([
                    'B' => 1,
                    'Fecha_B' => $datetimeArg,
                    'Hora_B' => $datetimeArg,
                    'ID_Usuario_B' => $id_usuario,
                    'B_Motivo' => $motivo
                ]);

            DB::connection($conn_name)->commit();
            return $userIds;

        } catch (Exception $e) {
            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }
}
