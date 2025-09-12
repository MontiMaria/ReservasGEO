<?php

namespace App\Models\Institucional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materia extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'materias';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Materia',
        'ID_Personal',
        'ID_Adjunto',
        'Mensajeria',
        'ID_Curso',
        'Grupal',
        'Curricular',
        'Integrador',
        'Nombre_Corto',
        'ID_Area',
        'ID_Nivel',
        'Horas',
        'Turno',
        'Orden',
        'ID_Plan',
        'Orden_Plan',
        'ID_Materia_Plan',
        'ID_Course_Moodle'
    ];

    protected $casts = [
        'ID_Personal' => 'int',
        'ID_Adjunto' => 'int',
        'Mensajeria' => 'int',
        'ID_Curso' => 'int',
        'ID_Area' => 'int',
        'ID_Nivel' => 'int',
        'Horas' => 'int',
        'Orden' => 'int',
        'ID_Plan' => 'int',
        'Orden_Plan' => 'int',
        'ID_Materia_Plan' => 'int',
        'ID_Course_Moodle' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Personal', 'ID');
    }

    public function adjunto(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Adjunto', 'ID');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'ID_Curso', 'ID');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'ID_Area', 'ID');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'ID_Nivel', 'ID');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'ID_Plan', 'ID');
    }

    public function materiaPlan(): BelongsTo
    {
        return $this->belongsTo(MateriaPlan::class, 'ID_Materia_Plan', 'ID');
    }

    public function cursoMoodle(): BelongsTo
    {
        return $this->belongsTo(CursoMoodle::class, 'ID_Curso_Moodle', 'ID');
    }

    public function acuerdoDetalle()
    {
        return $this->hasMany(AcuerdoDetalle::class, 'ID_Materia', 'ID');
    }

    public function agenda()
    {
        return $this->hasMany(Agenda::class, 'ID_Materia', 'ID');
    }

    public function agendaComun()
    {
        return $this->hasMany(AgendaComun::class, 'ID_Materia', 'ID');
    }

    public function asistenciaGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsistenciaGrupal::class, 'ID_Materia', 'ID');
    }

    public function asistenciaMateria(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsistenciaMateria::class, 'ID_Materia', 'ID');
    }

    public function calificacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Calificacion::class, 'ID_Materia', 'ID');
    }

    public function claseVirtual(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'ID_Materia', 'ID');
    }

    public function claseVirtualContenido(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ClaseVirtualContenido::class, 'ID_Materia', 'ID');
    }

    public function comunicado(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Comunicado::class, 'ID_Materia', 'ID');
    }

    public function cronogramaCursoDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CronogramaCursoDetalle::class, 'ID_Materia', 'ID');
    }

    public function estructuraDidacticaDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EstructuraDidacticaDetalle::class, 'ID_Materia', 'ID');
    }

    public function evaluacionColegiadaDetalleIndicador(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionColegiadaDetalleIndicador::class, 'ID_Materia', 'ID');
    }

    public function evaluacionColegiadaDetalleMateria(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionColegiadaDetalleMateria::class, 'ID_Materia', 'ID');
    }

    public function evaluacionColegiadaMateria(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionColegiadaMateria::class, 'ID_Materia', 'ID');
    }

    public function evaluacionCualitativaDetallePf(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionCualitativaDetallePf::class, 'ID_Materia', 'ID');
    }

    public function grupo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Grupo::class, 'ID_Materia_Gral', 'ID');
    }

    public function grupoMateriaB()
    {
        return $this->hasMany(Grupo::class, 'ID_Materia_B', 'ID');
    }

    public function horario(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Horario::class, 'ID_Materia', 'ID');
    }

    public function intensificacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Intensificacion::class, 'ID_Materia', 'ID');
    }

    public function intensificacionPeriodoDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(IntensificacionPeriodoDetalle::class, 'ID_Materia', 'ID');
    }

    public function libroTema(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LibroTema::class, 'ID_Materia', 'ID');
    }

    public function libroTemaGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LibroTemaGrupal::class, 'ID_Materia', 'ID');
    }

    public function materiaGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Materia', 'ID');
    }

    public function mesaExamen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamen::class, 'ID_Materia', 'ID');
    }

    public function mesaExamenCambio(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenCambio::class, 'ID_Materia', 'ID');
    }

    public function mesaExamenInscripcion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenInscripcion::class, 'ID_Materia', 'ID');
    }

    public function notaCursada(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaCursada::class, 'ID_Materia', 'ID');
    }

    public function noraMesaExamen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaMesaExamen::class, 'ID_Materia', 'ID');
    }

    public function notaOperacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaOperacion::class, 'ID_Materia', 'ID');
    }

    public function notaOperacionGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaOperacion::class, 'ID_Materia_Gral', 'ID');
    }

    public function notaParcial(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaParcial::class, 'ID_Materia', 'ID');
    }

    public function notaParcialGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaParcialGrupal::class, 'ID_Materia_Gral', 'ID');
    }

    public function notaTrimestral(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaTrimestral::class, 'ID_Materia', 'ID');
    }

    public function pActividad(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PActividad::class, 'ID_Materia', 'ID');
    }

    public function pCapacidad(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PCapacidad::class, 'ID_Materia', 'ID');
    }

    public function pContenido(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PContenido::class, 'ID_Materia', 'ID');
    }

    public function permisoDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PermisoDetalle::class, 'ID_Materia', 'ID');
    }

    public function pNucleoTematico(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PNucleoTematico::class, 'ID_Materia', 'ID');
    }

    public function pPlanificacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PPlanificacion::class, 'ID_Materia', 'ID');
    }

    public function procesoValoracionDetalleIndicador(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcesoValoracionDetalleIndicador::class, 'ID_Materia', 'ID');
    }

    public function procesoValoracionFinalAlumno(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcesoValoracionFinalAlumno::class, 'ID_Materia', 'ID');
    }

    public function procesoValoracionMateria(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcesoValoracionMateria::class, 'ID_Materia', 'ID');
    }

    public function reclamo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reclamo::class, 'ID_Materia', 'ID');
    }
}
