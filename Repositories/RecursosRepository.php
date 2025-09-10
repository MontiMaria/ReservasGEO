<?php
namespace App\Repositories;

use App\Models\Institucional\Recurso;
use \App\Models\Institucional\RecursoLectura;
use App\Models\Institucional\RecursoReserva;
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

    // Verifica si al reducir la cantidad de un recurso, existen reservas futuras que quedarían en conflicto.
    // Si no hay conflictos, actualiza la cantidad del recurso. Si hay conflictos, retorna las reservas afectadas y la cantidad solicitada.
    public function verificar_reservas($id_institucion, $id_recurso, $nuevaCantidad)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();

        try {
            if ($nuevaCantidad === null) {
                throw new InvalidArgumentException('La nueva cantidad no puede ser nula.');
            }
            // Buscar el recurso y obtener reservas futuras activas
            $recurso = Recurso::on($connName)->findOrFail($id_recurso);
            $cantidadDisponible = $nuevaCantidad;
            $hoy = Carbon::now()->format('Y-m-d');
            $reservas = RecursoReserva::on($connName)
                ->where('ID_Recurso', $id_recurso)
                ->where('B', 0)
                ->where('Fecha_R', '>=', $hoy)
                ->orderBy('Fecha_R', 'asc')
                ->orderBy('Hora_Inicio', 'asc')
                ->get();

            $reservasEnConflicto = collect();

            // Generar eventos de inicio y fin para cada reserva para simular la ocupación de recursos en el tiempo
            $eventos = $reservas->flatMap(function($reserva) {
                try {
                    $hi = Carbon::parse($reserva->Hora_Inicio);
                    $hf = Carbon::parse($reserva->Hora_Fin);
                } catch (\Exception $e) {
                    return [];
                }
                return [
                    ['hora' => $hi, 'tipo' => 'inicio', 'reserva' => $reserva],
                    ['hora' => $hf, 'tipo' => 'fin', 'reserva' => $reserva],
                ];
            });

            // Ordenar los eventos cronológicamente para simular la ocupación de recursos
            $eventos = $eventos->sort(function($a, $b) {
                if ($a['hora']->eq($b['hora'])) {
                    return $a['tipo'] === 'inicio' ? -1 : 1;
                }
                return $a['hora']->lt($b['hora']) ? -1 : 1;
            })->values();

            $recursosOcupados = 0;
            $reservasActivas = collect();

            // Recorrer los eventos y detectar si en algún momento se supera la cantidad disponible
            $eventos->each(function($evento) use (&$recursosOcupados, &$reservasActivas, $cantidadDisponible, &$reservasEnConflicto) {
                if ($evento['tipo'] === 'inicio') {
                    $recursosOcupados++;
                    $reservasActivas->push($evento['reserva']);
                    if ($recursosOcupados > $cantidadDisponible) {
                        $reservaConflicto = $reservasActivas->pop();
                        $reservasEnConflicto->push($reservaConflicto);
                        $recursosOcupados--;
                    }
                } else {
                    $recursosOcupados--;
                    $reservasActivas = $reservasActivas->filter(function($reserva) use ($evento) {
                        return $reserva->ID !== $evento['reserva']->ID;
                    })->values();
                }
            });

            // Si hay conflictos, retornar las reservas afectadas y la cantidad solicitada
            if (!$reservasEnConflicto->isEmpty()) {
                return [
                    'reservas_en_conflicto' => $reservasEnConflicto->all(),
                    'nueva_cantidad' => $nuevaCantidad
                ];
            }

            // Si no hay conflictos, actualizar la cantidad del recurso
            $recurso->Cantidad = $nuevaCantidad;
            $recurso->save();
            return "Cantidad actualizada sin conflictos.";
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Cancela reservas en conflicto y envia notificaciones (debe llamarse solo si el usuario confirma).
    // Cancela las reservas en conflicto y notifica a los usuarios afectados.
    // Luego, vuelve a intentar actualizar la cantidad del recurso.
    public function cancelar_reservas_en_conflicto($id_institucion, $reservasEnConflicto, $id_recurso, $nuevaCantidad)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();
        // Cancelar cada reserva en conflicto y registrar la notificación de la cancelación
        collect($reservasEnConflicto)->each(function($reserva) use ($id_institucion, $connName) {
            $this->cancelar_reserva(
                $id_institucion,
                $reserva->ID,
                'Conflicto de recursos por solapamiento horario',
                auth()->id()
            );
            $lectura = new RecursoLectura();
            $lectura->setConnection($connName);
            $lectura->ID_Reserva = $reserva->ID;
            $lectura->ID_Usuario = $reserva->ID_Usuario;
            $lectura->Fecha = Carbon::now()->format('Y-m-d');
            $lectura->Hora = Carbon::now()->format('H:i:s');
            $lectura->Leido = 0;
            $lectura->Fecha_Leido = null;
            $lectura->Hora_Leido = null;
            $lectura->save();
        });
        // Intentar nuevamente actualizar la cantidad del recurso después de cancelar los conflictos
        return $this->verificar_reservas($id_institucion, $id_recurso, $nuevaCantidad);
    }
    
    public function buscar_reservas($id_institucion, $id_recurso, $fecha)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();
        $diaSemana = Carbon::parse($fecha)->dayOfWeekIso; // 1=lunes ... 7=domingo
        if ($diaSemana == 7) $diaSemana = 0; // Para compatibilidad con JS (0=domingo)

        // Buscar bloqueos fijos para ese recurso y día
        $bloqueos = RecursoBloqueo::on($connName)
            ->where('ID_Recurso', $id_recurso)
            ->where('Dia_Semana', $diaSemana)
            ->where('B', 0)
            ->get(['HI', 'HF', 'Causa']);

        // Buscar reservas activas para ese recurso y fecha
        $recurso = Recurso::on($connName)->findOrFail($id_recurso);
        $cantidadMaxima = $recurso->Cantidad;
        $reservas = RecursoReserva::on($connName)
            ->where('ID_Recurso', $id_recurso)
            ->where('B', 0)
            ->where('Fecha_R', $fecha)
            ->orderBy('Hora_Inicio', 'asc')
            ->get(['Hora_Inicio', 'Hora_Fin']);

        // Calcular franjas horarias donde la cantidad de reservas alcanza el máximo
        $eventos = collect();
        $reservas->each(function($reserva) use (&$eventos) {
            $eventos->push(['hora' => $reserva->Hora_Inicio, 'tipo' => 'inicio']);
            $eventos->push(['hora' => $reserva->Hora_Fin, 'tipo' => 'fin']);
        });
        $eventos = $eventos->sortBy('hora')->values();

        // Usar reduce para calcular las franjas horarias donde la cantidad de reservas alcanza el máximo.
        // Recorre los eventos de inicio/fin y va sumando/restando la cantidad de recursos ocupados.
        // Cuando la cantidad de ocupados llega al máximo, marca el inicio de una franja ocupada.
        // Cuando baja del máximo, cierra la franja y la agrega al resultado.
        $result = $eventos->reduce(function($acumulador, $evento) use ($cantidadMaxima) {
            // Si es un evento de inicio de reserva, incrementa la cantidad de ocupados
            if ($evento['tipo'] === 'inicio') {
                $acumulador['ocupados']++;
                // Si justo al incrementar se alcanza el máximo, guardamos el inicio de la franja ocupada
                if ($acumulador['ocupados'] == $cantidadMaxima) {
                    // Aquí comienza una franja donde no hay más disponibilidad
                    $acumulador['inicioOcupado'] = $evento['hora'];
                }
            } else {
                // Si es un evento de fin de reserva
                // Antes de decrementar, si estábamos en ocupación máxima, cerramos la franja
                if ($acumulador['ocupados'] == $cantidadMaxima && $acumulador['inicioOcupado'] !== null) {
                    // Aquí termina la franja de ocupación máxima
                    $acumulador['franjasOcupadas'][] = [
                        'HI' => $acumulador['inicioOcupado'],
                        'HF' => $evento['hora'],
                        'Causa' => 'Ocupación máxima'
                    ];
                    $acumulador['inicioOcupado'] = null;
                }
                // Decrementa la cantidad de ocupados
                $acumulador['ocupados']--;
            }
            return $acumulador;
        }, [
            'ocupados' => 0,           // Cantidad de reservas activas en el momento
            'inicioOcupado' => null,   // Marca el inicio de una franja ocupada
            'franjasOcupadas' => []    // Acumula las franjas de ocupación máxima
        ]);
        $franjasOcupadas = $result['franjasOcupadas'];

        // Unir bloqueos fijos y franjas ocupadas
        $bloqueosTotales = $bloqueos->map(function($b) {
            return [
                'HI' => $b->HI,
                'HF' => $b->HF,
                'Causa' => $b->Causa
            ];
        })->toArray();
        $bloqueosTotales = array_merge($bloqueosTotales, $franjasOcupadas);

        // Devolver json/array con los horarios bloqueados
        return [
            'fecha' => $fecha,
            'id_recurso' => $id_recurso,
            'bloqueos' => $bloqueosTotales
        ];
    }
}