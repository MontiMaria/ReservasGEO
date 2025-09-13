<?php

namespace App\Repositories;

use App\Models\Institucional\Materia;
use App\Models\Institucional\Personal;
use App\Models\Institucional\Recurso;
use App\Services\DataBaseService;
use App\Services\FuncionesService;
use Carbon\Carbon;
use App\Models\Institucional\RecursoBloqueo;
use App\Models\Institucional\RecursoLectura;
use App\Models\Institucional\RecursoReserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Exception;
use InvalidArgumentException;
use DomainException;
use Dotenv\Exception\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Log;

class RecursosRepository
{
    private $dataBaseService;
    private $funcionesService;

    function __construct(DataBaseService $dataBaseService, FuncionesService $funcionesService)
    {
        $this->dataBaseService = $dataBaseService;
        $this->funcionesService = $funcionesService;
    }


    public function crear_recurso($id, $id_usuario, $recurso, $cantidad, $descripcion, $id_tipo, $id_nivel, $bloqueos) {
        $id_institucion = $id;
        $bloqueos_cargados = [];

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la creacion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

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
                        throw new DomainException("El valor ingresado se encuentra fuera del rango");
                    }
                    try {
                        $hi_p = Carbon::parse($hi);
                        $hf_p = Carbon::parse($hf);

                        $hi_min = Carbon::createFromTime(7, 0, 0);
                        $hi_max = Carbon::createFromTime(16, 0, 0);
                        $hf_min = Carbon::createFromTime(8, 0, 0);
                        $hf_max = Carbon::createFromTime(17, 0, 0);
                    }
                    catch (Exception $e) {
                        throw new InvalidArgumentException("Formato de hora inválido.");
                    }
                    if($hi_p >= $hf_p) {
                        throw new DomainException("La hora de incio es mayor o igual a la hora de finalizacion");
                    }
                    if((!$hi_p->between($hi_min, $hi_max)) || (!$hf_p->between($hf_min, $hf_max))) {
                        throw new DomainException("El valor ingresado se encuentra fuera del rango horario de las reservas");
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

            // Verificamos la cantidad
            if($cantidad < 0) {
                throw new InvalidArgumentException("La cantidad ingresada es menor a 0");
            }
            /* Se podria agregar una verificacion para los recursos de tipo Espacio comun
            if($recurso->ID_Nivel == 3 && ($cantidad >= 0 && $cantidad <= 1)) {
                throw new Exception("No se puede aumentar la cantidad de las aulas en mas de 1");
            }
            */

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
        $now = Carbon::now('America/Argentina/Buenos_Aires');
        $Fecha_Actual = $now->format('Y-m-d');
        $Hora_Actual = $now->format('H:i:s');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la creacion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['PF', 'MG', 'MI'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

        DB::connection($conn_name)->beginTransaction();

        try {
            $reserva = RecursoReserva::on($conn_name)->findOrFail($id_reserva);

            $reserva->B = 1;
            $reserva->B_Motivo = $motivo;
            $reserva->Fecha_B = $Fecha_Actual;
            $reserva->Hora_B = $Hora_Actual;
            $reserva->ID_Usuario_B = $id_usuario;
            $reserva->save();

            DB::connection($conn_name)->commit();
            return $reserva;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function agregar_bloqueo($id, $id_recurso, $dia_semana, $hi, $hf, $id_nivel, $causa, $id_usuario) {
        $id_institucion = $id;

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la accion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

        DB::connection($conn_name)->beginTransaction();

        try {
            $consulta_recurso = Recurso::on($conn_name)->findOrFail($id_recurso);

            if($consulta_recurso->ID_Nivel != $id_nivel && $consulta_recurso->ID_Nivel != 0) {
                throw new ValidationException("El nivel ingresado no es valido para el bloqueo del recurso");
            }
            if($dia_semana < 1 || $dia_semana > 5) {
                throw new InvalidArgumentException("El valor ingresado se encuentra fuera del rango");
            }
            try {
                $hi_p = Carbon::parse($hi);
                $hf_p = Carbon::parse($hf);

                $hi_min = Carbon::createFromTime(7, 0, 0);
                $hi_max = Carbon::createFromTime(16, 0, 0);
                $hf_min = Carbon::createFromTime(8, 0, 0);
                $hf_max = Carbon::createFromTime(17, 0, 0);
            }
            catch (Exception $e) {
                throw new InvalidArgumentException("Formato de hora inválido.");
            }
            if($hi_p >= $hf_p) {
                throw new DomainException("La hora de incio es mayor o igual a la hora de finalizacion");
            }
            if((!$hi_p->between($hi_min, $hi_max)) || (!$hf_p->between($hf_min, $hf_max))) {
                throw new InvalidArgumentException("El valor ingresado se encuentra fuera del rango horario de las reservas");
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
        $now = Carbon::now('America/Argentina/Buenos_Aires');
        $Fecha_Actual = $now->format('Y-m-d');
        $Hora_Actual = $now->format('H:i:s');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la accion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

        DB::connection($conn_name)->beginTransaction();

        try {
            $bloqueo = RecursoBloqueo::on($conn_name)->findOrFail($id_bloqueo);

            $bloqueo->B = 1;
            $bloqueo->Fecha_B = $Fecha_Actual;
            $bloqueo->Hora_B = $Hora_Actual;
            $bloqueo->ID_Usuario_B = $id_usuario;
            $bloqueo->save();

            DB::connection($conn_name)->commit();
            return $bloqueo;

        } catch (Exception $e) {

            DB::connection($conn_name)->rollback();
            throw $e;
        }
    }

    public function eliminar_recurso($id, $id_recurso, $id_usuario, $motivo)
    {
        $id_institucion = $id;
        $now = Carbon::now('America/Argentina/Buenos_Aires');
        $Fecha_Actual = $now->format('Y-m-d');
        $Hora_Actual = $now->format('H:i:s');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la accion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

        DB::connection($conn_name)->beginTransaction();

        try {
            $recursoActualizado = Recurso::on($conn_name)
                ->where('ID', $id_recurso)
                ->update(['B' => 1]);

            if ($recursoActualizado === 0) {
                throw new Exception("No se encontro el recurso a dar de baja.");
            }

            RecursoBloqueo::on($conn_name)
                ->where('ID_Recurso', $id_recurso)
                ->update([
                    'B' => 1,
                    'Fecha_B' => $Fecha_Actual,
                    'Hora_B' => $Hora_Actual,
                    'ID_Usuario_B' => $id_usuario
                ]);

            $reservasActivas = RecursoReserva::on($conn_name)
                ->where('ID_Recurso', $id_recurso)
                ->where('B', 0)
                ->get();

            foreach ($reservasActivas as $reserva) {
                $reserva->update([
                    'B' => 1,
                    'Fecha_B' => $Fecha_Actual,
                    'Hora_B' => $Hora_Actual,
                    'ID_Usuario_B' => $id_usuario,
                    'B_Motivo' => $motivo
                ]);

                RecursoLectura::on($conn_name)
                    ->create([
                        'ID_Recurso' => $reserva->ID_Recurso,
                        'ID_Usuario' => $reserva->ID_Usuario,
                        'Fecha' => $Fecha_Actual,
                        'Hora' => $Hora_Actual
                    ]);
            }
            DB::connection($conn_name)->commit();

        } catch (Exception $e) {
            DB::connection($conn_name)->rollback();
            throw $e;
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
                    ->orWhere("ID_Nivel" , 0);
                })
                ->with(["bloqueos" => function($q) use ($id_nivel) {
                    $q->select("ID", "ID_Recurso", "Dia_Semana", "HI", "HF", "ID_Nivel", "Causa")
                    ->where("B", 0)
                    ->where(function($q2) use ($id_nivel) {
                        $q2->where("ID_Nivel", $id_nivel)
                        ->orWhere("ID_Nivel" , 0);
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
                    ->orWhere("ID_Nivel" , 0);
                })
                ->with(["bloqueos" => function($q) use ($id_nivel) {
                    $q->select("ID", "ID_Recurso", "Dia_Semana", "HI", "HF", "ID_Nivel", "Causa")
                    ->where("B", 0)
                    ->where(function($q2) use ($id_nivel) {
                        $q2->where("ID_Nivel", $id_nivel)
                        ->orWhere("ID_Nivel" , 0);
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

    public function crear_reserva($id, $id_recurso, $id_usuario, $fecha_r, $hora_inicio, $hora_fin, $id_nivel, $id_curso, $id_materia, $actividad) {
        $id_institucion = $id;
        $now = Carbon::now('America/Argentina/Buenos_Aires');
        $Fecha_Actual = $now->format('Y-m-d');
        $Hora_Actual = $now->format('H:i:s');

        $conn_name = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la creacion sea de un tipo adecuado
        $personal = Personal::on($conn_name)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['PF', 'MG', 'MI'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }
        try {
            // Parseo y valido las horas
            $hi = Carbon::createFromFormat('H:i:s', $hora_inicio);
            $hf = Carbon::createFromFormat('H:i:s', $hora_fin);
            $hi_min = Carbon::createFromTime(7, 0, 0);
            $hf_max = Carbon::createFromTime(17, 0, 0);

            $minutes = $hi->diffInMinutes($hf);
        }
        catch(Exception $e) {
            throw new InvalidArgumentException("Formato de fecha/hora inválido: " . $e->getMessage());
        }
        if($hi->gte($hf)) {
            throw new DomainException("La hora de inicio debe ser anterior a la hora de fin de la reserva.");
        }
        if($minutes < 40 || $minutes > 240) {
            throw new DomainException("Tiempo de duracion de la reserva invalido.");
        }
        if($hi->lt($hi_min) || $hf->gt($hf_max)) {
            throw new DomainException("Reserva fuera del rango disponible.");
        }

        DB::connection($conn_name)->beginTransaction();

        try {
            // Verifico que exista ese Personal
            Personal::on($conn_name)->findOrFail($id_usuario);

            // Verifico que el recurso sea o de el nivel de el usuario o de la institucion
            Recurso::on($conn_name)
                ->where('ID', $id_recurso)
                ->whereIn('ID_Nivel', [$id_nivel, 0]) // 0 = nivel general
                ->firstOrFail();

            $reserva = RecursoReserva::on($conn_name)
                ->create([
                    'Fecha' => $Fecha_Actual,
                    'Hora' => $Hora_Actual,
                    'ID_Recurso' => $id_recurso,
                    'Fecha_R' => $fecha_r,
                    'Hora_Inicio' => $hora_inicio,
                    'Hora_Fin' => $hora_fin,
                    'ID_Nivel' => $id_nivel,
                    'ID_Curso' => $id_curso,
                    'ID_Materia' => $id_materia,
                    'ID_Docente' => $id_usuario,
                    'Actividad' => $actividad,
                    'ID_Usuario_B' => 0,
                    'B_Motivo' => ''
                ]);

            DB::connection($conn_name)->commit();

            return $reserva->only([
                'Fecha',
                'Hora',
                'ID_Recurso',
                'Fecha_R',
                'Hora_Inicio',
                'Hora_Fin',
                'ID_Nivel',
                'ID_Curso',
                'ID_Materia',
                'ID_Docente',
                'Actividad'
            ]);
        }
        catch(Exception $e) {
            DB::connection($conn_name)->rollBack();
            throw $e;
        }
    }

    public function actualizar_reservas_activas($id, $id_usuario, $id_nivel){
        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();
            $fechaActual = Carbon::now('America/Argentina/Buenos_Aires')->format('Y-m-d');
            $horaActual = Carbon::now('America/Argentina/Buenos_Aires')->format('H:i:s');

            $reservas = RecursoReserva::on($connection)
                ->where('B','=',0)
                ->where(function($query) use ($fechaActual, $horaActual, $id_nivel){
                    $query->where('Fecha_R', '<', $fechaActual)
                        ->where('ID_Nivel', $id_nivel) // Por que?
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
        $this->actualizar_reservas_activas($id, $id_usuario, $id_nivel);
        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();
            $rol = Personal::on($connection)
                ->select('Tipo')
                ->where('ID',$id_usuario)
                ->firstOrFail();
            $cant_por_pagina = $cant_por_pagina ?? 15;
            $pagina = $pagina ?? 1;

            if(in_array($rol->Tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)){
                $reservas = RecursoReserva::on($connection)
                    ->from('recursos_reservas as rr')
                    ->select('recursos.Recurso','rr.*','personal.Nombre','personal.Apellido')
                    ->join('recursos','recursos.ID','=','rr.ID_Recurso')
                    ->join('personal','personal.ID','=','rr.ID_Docente')
                    //se le podría agregar un join con materias para que traiga el nombre de la materia también
                    //->join('materias','materias.ID','=','rr.ID_Materia')
                    ->where('rr.B','=',0)
                    ->where(function($q) use ($id_nivel) {
                        $q->where("rr.ID_Nivel", $id_nivel)
                        ->orWhere("rr.ID_Nivel" , 0);
                    })
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
                    ->where(function($q) use ($id_nivel) {
                        $q->where("rr.ID_Nivel", $id_nivel)
                        ->orWhere("rr.ID_Nivel" , 0);
                    })
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
                ->firstOrFail();
            $cant_por_pagina = $cant_por_pagina ?? 15;
            $pagina = $pagina ?? 1;

            if(in_array($rol->Tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)){
                $reservas = RecursoReserva::on($connection)
                    ->from('recursos_reservas as rr')
                    ->select('recursos.Recurso','rr.*','personal.Nombre','personal.Apellido')
                    ->join('recursos','recursos.ID','=','rr.ID_Recurso')
                    ->join('personal','personal.ID','=','rr.ID_Docente')
                    //se le podría agregar un join con materias para que traiga el nombre de la materia también
                    //->join('materias','materias.ID','=','rr.ID_Materia')
                    ->where('rr.B','=',1)
                    ->where(function($q) use ($id_nivel) {
                        $q->where("rr.ID_Nivel", $id_nivel)
                        ->orWhere("rr.ID_Nivel" , 0);
                    })
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
                    ->where(function($q) use ($id_nivel) {
                        $q->where("rr.ID_Nivel", $id_nivel)
                        ->orWhere("rr.ID_Nivel" , 0);
                    })
                    ->where('rr.ID_Docente',$id_usuario)
                    ->orderBy('rr.Fecha_R','desc')
                    ->orderBy('rr.Hora_Inicio','desc');
            } else {
                throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas históricas.", 403);
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
            throw new InvalidArgumentException("El rol no tiene permisos para ver las reservas históricas.",403);
        } catch(Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine());
            return $e->getMessage();
        }
    }

    public function traer_recursos($id, $id_usuario, $id_nivel){
        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();
            $recursos = Recurso::on($connection)
                ->select('ID','Recurso','Descripcion','ID_Tipo','ID_Nivel')
                ->where(function($q) use ($id_nivel) {
                    $q->where("ID_Nivel", $id_nivel)
                    ->orWhere("ID_Nivel" , 0);
                })
                ->where('Estado','=','H')
                ->where('B','=',0)
                ->get();

            $recursos = $recursos->toArray();
            return $recursos;

        } catch(Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine());
            return $e->getMessage();
        }

    }

    public function listar_materias($id, $id_usuario, $id_nivel){
        $id_institucion = $id;
        try{
            $connection = $this->dataBaseService->selectConexion($id_institucion)->getName();

            $lista = Materia::on($connection)
                ->from('materias as m')
                ->select('m.ID','m.Materia','m.ID_Curso','c.Cursos')
                ->join('cursos as c','c.ID','=','m.ID_Curso')
                ->where('m.ID_Nivel',$id_nivel)
                ->where('c.ID_Nivel',$id_nivel)
                ->where(function($q) use ($id_usuario){
                    $q->where('m.ID_Personal',$id_usuario)
                      //a considerar si el adjunto también puede reservar recursos
                      ->orWhere('m.ID_Adjunto',$id_usuario);
                })
                ->get();
            if(empty($lista) || $lista->isEmpty()) {
                return ['Materias' => []];
            }else{

                $resultado = [
                    'Materias' => $lista->map(function($item) {
                        return [
                            'ID_Materia' => $item->ID,
                            'ID_Curso' => $item->ID_Curso,
                            'Materia' => $item->Materia,
                            'Curso' => $item->Cursos
                        ];
                    })->values()->toArray()
                ];

                return $resultado;
            }

        } catch(Exception $e) {
            Log::error("ERROR: " . $e->getMessage() . " - linea " . $e->getLine());
            return $e->getMessage();
        }
    }

    // Verifica si al reducir la cantidad de un recurso, existen reservas futuras que quedarían en conflicto.
    // Si no hay conflictos, actualiza la cantidad del recurso. Si hay conflictos, retorna las reservas afectadas y la cantidad solicitada.
    public function verificar_reservas($id_institucion, $id_recurso, $nuevaCantidad)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();

        try {
            if ($nuevaCantidad === null || $nuevaCantidad < 0) {
                throw new InvalidArgumentException('Cantidad ingresada invalida.');
            }
            // Buscar el recurso y obtener reservas futuras activas
            $recurso = Recurso::on($connName)->findOrFail($id_recurso);
            $cantidadDisponible = $nuevaCantidad;
            $hoy = Carbon::now("America/Argentina/Buenos_Aires")->format('Y-m-d');
            $reservas = RecursoReserva::on($connName)
                ->where('ID_Recurso', $id_recurso)
                ->where('B', 0)
                ->where('Fecha_R', '>=', $hoy)
                ->orderBy('Fecha_R', 'asc')
                ->orderBy('Hora_Inicio', 'asc')
                ->get();

            // Si no hay reservas entonces
            if($reservas->isEmpty()) {
                $recurso->Cantidad = $nuevaCantidad;
                $recurso->save();
                return [
                    'message' => "Cantidad actualizada sin conflictos.",
                    'conflicto' => false
                ];
            }
            // Evitamos recorrer toda las validaciones
            if($nuevaCantidad == 0) {
                return [
                    'reservas_en_conflicto' => $reservas->map(function($r){
                        try {
                            $fecha = Carbon::parse($r->Fecha_R)->format('Y-m-d');
                            $hi = Carbon::parse($r->Hora_Inicio)->format('H:i:s');
                            $hf = Carbon::parse($r->Hora_Fin)->format('H:i:s');
                        } catch (Exception $e) {
                            return [];
                        }

                        return [
                            'id' => $r->ID,
                            'fecha' => $fecha,
                            'hora_inicio' => $hi,
                            'hora_fin' => $hf,
                        ];
                    })->values()->all(),
                    'nueva_cantidad' => $nuevaCantidad,
                    'conflicto' => true
                ];
            }

            $reservasEnConflicto = collect();

            // Generar eventos de inicio y fin para cada reserva para simular la ocupación de recursos en el tiempo
            $eventos = $reservas->flatMap(function($reserva) {
                try {
                    $hi = Carbon::parse($reserva->Hora_Inicio);
                    $hf = Carbon::parse($reserva->Hora_Fin);
                } catch (Exception $e) {
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
                        $reservaConflicto = $reservasActivas->sortByDesc(fn($r) => ($r->Fecha . ' ' . ($r->Hora ?? '00:00:00')))->first(); // Filtro LIFO

                        $reservasActivas = $reservasActivas->filter(function($r) use ($reservaConflicto) {
                            return $r->ID !== $reservaConflicto->ID;
                        })->values();

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
                    'nueva_cantidad' => $nuevaCantidad,
                    'conflicto' => true
                ];
            }

            // Si no hay conflictos, actualizar la cantidad del recurso
            $recurso->Cantidad = $nuevaCantidad;
            $recurso->save();
            return [
                'message' => "Cantidad actualizada sin conflictos.",
                'conflicto' => false
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Cancela reservas en conflicto y envia notificaciones (debe llamarse solo si el usuario confirma).
    // Cancela las reservas en conflicto y notifica a los usuarios afectados.
    // Luego, vuelve a intentar actualizar la cantidad del recurso.
    public function cancelar_reservas_en_conflicto($id_institucion, $reservasEnConflicto, $id_recurso, $nuevaCantidad, $id_usuario, $motivo)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();

        // Verifico que el usuario que realiza la accion sea de un tipo adecuado
        $personal = Personal::on($connName)->with('cargo')->findOrFail($id_usuario);
        $tipo = $personal->cargo->Tipo ?? null;

        // Agregar los cargos que sean necesarios
        if(!in_array($tipo, ['EM', 'DI', 'VD', 'PR', 'SC', 'AM'], true)) {
            throw new AuthorizationException('El cargo del usuario no esta autorizado para realizar esta accion');
        }

        DB::connection($connName)->beginTransaction();

        try {
            // Cancelar cada reserva en conflicto y registrar la notificación de la cancelación
            collect($reservasEnConflicto)->each(function($reserva) use ($connName, $motivo, $id_usuario) {
                // Damos de baja la reserva
                $r = RecursoReserva::on($connName)->findOrFail($reserva->ID);
                $r->B = 1;
                $r->B_Motivo = $motivo;
                $r->Fecha_B = Carbon::now("America/Argentina/Buenos_Aires")->format('Y-m-d');
                $r->Hora_B = Carbon::now("America/Argentina/Buenos_Aires")->format('H:i:s');
                $r->ID_Usuario_B = $id_usuario;
                $reserva->save();

                // Creamos la notificacion
                $lectura = new RecursoLectura();
                $lectura->setConnection($connName);
                $lectura->ID_Reserva = $reserva->ID;
                $lectura->ID_Usuario = $reserva->ID_Usuario;
                $lectura->Fecha = Carbon::now("America/Argentina/Buenos_Aires")->format('Y-m-d');
                $lectura->Hora = Carbon::now("America/Argentina/Buenos_Aires")->format('H:i:s');
                $lectura->save();
            });

            DB::connection($connName)->commit();

            // Intentar nuevamente actualizar la cantidad del recurso después de cancelar los conflictos
            return $this->verificar_reservas($id_institucion, $id_recurso, $nuevaCantidad);
        }
        catch (Exception $e) {
            DB::connection($connName)->rollback();
            throw $e;
        }
    }

    public function buscar_reservas($id_institucion, $id_recurso, $fecha)
    {
        $connName = $this->dataBaseService->selectConexion($id_institucion)->getName();
        $diaSemana = Carbon::parse($fecha)->dayOfWeekIso; // 1=lunes ... 7=domingo

        if($diaSemana < 1 || $diaSemana > 5) {
            throw new DomainException("La fecha ingresada debe ser un día entre lunes y viernes.");
        }
        if($fecha->lt(Carbon::today('America/Argentina/Buenos_Aires'))) {
            throw new DomainException("La fecha de reserva no puede ser anterior a hoy.");
        }

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
