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
            $reserva->Fecha_B = Carbon::now()->format('Y-m-d');
            $reserva->Hora_B = Carbon::now()->format('H:i:s');
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
            $bloqueo->Fecha_B = Carbon::now()->format('Y-m-d');
            $bloqueo->Hora_B = Carbon::now()->format('H:i:s');
            $bloqueo->ID_Usuario_B = $id_usuario;
            $bloqueo->save();

            DB::connection($conn_name)->commit();
            return $bloqueo;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function verificar_reserva($id_institucion, $id_recurso)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();

        try {
            // Buscar el recurso y su cantidad disponible
            $recurso = Recurso::on($connName)->findOrFail($id_recurso);
            $cantidadDisponible = $recurso->Cantidad ?? 1;

            // Traer solo reservas activas posteriores o iguales a la fecha actual, ordenadas por fecha y hora de inicio
            $hoy = Carbon::now()->format('Y-m-d');
            $reservas = collect(DB::connection($connName)->table('recursos_reservas')
                ->where('ID_Recurso', $id_recurso)
                ->where('B', 0)
                ->where('Fecha_R', '>=', $hoy)
                ->orderBy('Fecha_R', 'asc')
                ->orderBy('Hora_Inicio', 'asc')
                ->get());

            $reservasCanceladas = collect();

            // Crear eventos de inicio y fin para cada reserva
            $eventos = $reservas->flatMap(function($reserva) {
                try {
                    $hi = Carbon::parse($reserva->Hora_Inicio);
                    $hf = Carbon::parse($reserva->Hora_Fin);
                } catch (\Exception $e) {
                    // Si hay error de parseo, ignorar esta reserva
                    return [];
                }

                return [
                    ['hora' => $hi, 'tipo' => 'inicio', 'reserva' => $reserva],
                    ['hora' => $hf, 'tipo' => 'fin', 'reserva' => $reserva],
                ];
            });

            // Ordenar eventos: primero por hora, luego 'inicio' antes que 'fin' si coinciden
            $eventos = $eventos->sort(function($a, $b) {
                if ($a['hora']->eq($b['hora'])) {
                    return $a['tipo'] === 'inicio' ? -1 : 1;
                }
                return $a['hora']->lt($b['hora']) ? -1 : 1;
            })->values();

            $recursosOcupados = 0;
            $reservasActivas = collect(); // Reservas actualmente activas

            // Procesar la línea de tiempo de eventos
            $eventos->each(function($evento) use (&$recursosOcupados, &$reservasActivas, $cantidadDisponible, $id_institucion, &$reservasCanceladas) {
                if ($evento['tipo'] === 'inicio') {
                    $recursosOcupados++;
                    $reservasActivas->push($evento['reserva']);

                    // Si se supera la cantidad disponible, cancelar la última reserva activa
                    if ($recursosOcupados > $cantidadDisponible) {
                        $reservaCancelada = $reservasActivas->pop();
                        // revisar si el mensaje de cancelación es el que se espera
                        $this->cancelar_reserva(
                            $id_institucion,
                            $reservaCancelada->ID,
                            'Conflicto de recursos por solapamiento horario',
                            auth()->id()
                        );
                        $reservasCanceladas->push($reservaCancelada);
                        $recursosOcupados--;
                    }
                } else { // Evento de fin
                    $recursosOcupados--;
                    // Eliminar la reserva de las activas usando filter
                    $reservasActivas = $reservasActivas->filter(function($reserva) use ($evento) {
                        return $reserva->ID !== $evento['reserva']->ID;
                    })->values();
                }
            });

            // Retornar las reservas que fueron canceladas por conflicto (sirve para notificar al usuario)
            return $reservasCanceladas->all();

        } catch (Exception $e) {
            throw $e;
        }
    }


}