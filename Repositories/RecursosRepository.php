<?php

namespace App\Repositories;

use App\Models\Institucional\Recurso;
use App\Models\Institucional\RecursoBloqueo;
use App\Models\Institucional\RecursoReserva;
use App\Services\DataBaseService;
use App\Services\FuncionesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Dotenv\Exception\ValidationException;
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

                    // Donde 4=Institucional
                    if($id_nivel == 4 && $b['id_nivel_b'] != $id_nivel) {
                        $id_nivel_b = $b['id_nivel_b'];

                        $bloqueo = $nuevo_recurso->bloqueos()->create([
                            'Dia_Semana' => $dia,
                            'HI' => $hi,
                            'HF' => $hf,
                            'ID_Nivel' => $id_nivel_b,
                            'Causa' => $causa
                        ]);
                    }
                    else {
                        $bloqueo = $nuevo_recurso->bloqueos()->create([
                            'Dia_Semana' => $dia,
                            'HI' => $hi,
                            'HF' => $hf,
                            'ID_Nivel' => $id_nivel,
                            'Causa' => $causa
                        ]);
                    }
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

    public function agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa) {
        $id_institucion = $id;

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();
        DB::connection($conn_name)->beginTransaction();

        try {
            $consulta_recurso = Recurso::on($conn_name)->findOrFail($id_recurso);

            if($consulta_recurso->ID_Nivel != $id_nivel && $consulta_recurso->ID_Nivel != 4) {
                throw new ValidationException("El nivel ingresado no es valido para el bloqueo del recurso");
            }
            if($dia_semana < 1 || $dia_semana > 5) {
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

            $resultado = $consulta_recurso->bloqueos()->create([
                'Dia_Semana' => $dia_semana,
                'HI' => $hi,
                'HF' => $hf,
                'ID_Nivel' => $id_nivel,
                'Causa' => $causa
            ]);

            DB::connection($conn_name)->commit();

            return $resultado;
        }
        catch(Exception $e) {
            DB::connection($conn_name)->rollBack();
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

<<<<<<< HEAD
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

    public function ver_lista_recursos($id, $id_nivel, $id_tipo) {
        $id_institucion = $id;
        $per_page = 8;
        $resultado = [];

        if($id_tipo < 0 || $id_tipo > 3) {
            throw new InvalidArgumentException("El tipo de recurso ingresado no es valido");
        }

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        if($id_tipo == 0) {
            $consulta_recursos = Recurso::on($conn_name)
                ->select("ID", "Recurso", "Cantidad", "Descripcion", "ID_Tipo", "ID_Nivel")
                ->where("B", 0)
                ->where(function($q) use ($id_nivel) {
                    $q->where("ID_Nivel", $id_nivel)
                    ->orWhere("ID_Nivel" , 4);
                })
                ->with(["bloqueos" => function($q) use ($id_nivel) {
                    $q->select("ID", "ID_Recurso", "Dia_Semana", "HI", "HF", "ID_Nivel", "Causa")
                    ->where("B", 0)
                    ->where(function($q2) use ($id_nivel) {
                        $q2->where("ID_Nivel", $id_nivel)
                        ->orWhere("ID_Nivel" , 4);
                    });
                }])
                ->orderBy("ID_Tipo")
                ->orderBy("Recurso")
                ->paginate($per_page);
        }
        else {
            $consulta_recursos = Recurso::on($conn_name)
                ->select("ID", "Recurso", "Cantidad", "Descripcion", "ID_Tipo", "ID_Nivel")
                ->where("B", 0)
                ->where("ID_Tipo", $id_tipo)
                ->where(function($q) use ($id_nivel) {
                    $q->where("ID_Nivel", $id_nivel)
                    ->orWhere("ID_Nivel" , 4);
                })
                ->with(["bloqueos" => function($q) use ($id_nivel) {
                    $q->select("ID", "ID_Recurso", "Dia_Semana", "HI", "HF", "ID_Nivel", "Causa")
                    ->where("B", 0)
                    ->where(function($q2) use ($id_nivel) {
                        $q2->where("ID_Nivel", $id_nivel)
                        ->orWhere("ID_Nivel" , 4);
                    });
                }])
                ->orderBy("Recurso")
                ->paginate($per_page);
        }

        $data = collect($consulta_recursos->items())->map(function($r)  {
            $bloqueos = $r->bloqueos instanceof Collection ? $r->bloqueos : [];

            $list_bloqueos = $bloqueos->map(function($b)  {
                return [
                    "ID" => $b->ID,
                    "Dia_Semana_Nombre" => $b->dia_semana_nombre,
                    "HI" => $b->HI,
                    "HF" => $b->HF,
                    "ID_Nivel" => $b->ID_Nivel,
                    "Causa" => $b->Causa
                ];
            })->values()->all();

            return [
                "ID" => $r->ID,
                "Recurso" => $r->Recurso,
                "Cantidad" => $r->Cantidad,
                "Descripcion" => $r->Descripcion,
                "Tipo_Recurso" => $r->tipo_recurso_nombre,
                "ID_Nivel" => $r->ID_Nivel,
                "bloqueos" => $list_bloqueos
            ];
            })->values()->all();

        $resultado = [
            'items' => $data,
            'pagination' => [
                'current_page' => $consulta_recursos->currentPage(),
                'has_next' => $consulta_recursos->hasMorePages(),
                'has_prev' => $consulta_recursos->currentPage() > 1
            ],
        ];

        return $resultado;

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

    public function ver_listado_reservas_antiguas($id,$id_usuario, $id_nivel, $cant_por_pagina, $pagina){

        //verificar la cantidad de consultas a la base de datos
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
                    ->where('rr.B','=',1)
                    ->where('rr.ID_Nivel',$id_nivel)
                    ->orderBy('rr.Fecha_R','desc')
                    ->orderBy('rr.Hora_Inicio','desc');
            } elseif (in_array($rol->Tipo, ['PF', 'MI', 'MG'])) {
                $reservas = RecursoReserva::on($connection)
                    ->from('recursos_reservas as rr')
                    ->select('recursos.Recurso','rr.*')
                    ->join('recursos','recursos.ID','=','rr.ID_Recurso')
                    //se le podría agregar un join con materias para que traiga el nombre de la materia también
                    //->join('materias','materias.ID','=','rr.ID_Materia')
                    ->where('rr.B','=',1)
                    ->where('rr.ID_Nivel',$id_nivel)
                    ->where('rr.ID_Docente',$id_usuario)
                    ->orderBy('rr.Fecha_R','desc')
                    ->orderBy('rr.Hora_Inicio','desc');
            } else {
                throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas históricas.",403);
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

        }catch (InvalidArgumentException $e) {
            // Caso específico: rol sin permisos
            throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas históricas.",403);
        } catch(Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine());
            return $e->getMessage();
        }
    }
}
=======
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
>>>>>>> origin/seba
