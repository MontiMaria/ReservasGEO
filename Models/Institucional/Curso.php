<?php

namespace App\Models\Institucional;

use App\Models\General\RegistroContactoInteres;
use App\Models\General\RegistroContactoSolicitud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curso extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'cursos';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Cursos',
        'ID_Modalidad',
        'ID_Nivel',
        'ID_Institucion',
        'ID_Preceptor',
        'Turno',
        'CC',
        'Division',
        'FC',
        'ID_Plan',
        'Orden_Plan',
        'ID_Pareja',
        'ID_Pareja2',
        'ID_Pareja3',
        'Mat'
    ];

    protected $casts = [
        'ID_Modalidad' => 'int',
        'ID_Nivel' => 'int',
        'ID_Institucion' => 'int',
        'ID_Preceptor' => 'int',
        'FC' => 'int',
        'ID_Plan' => 'int',
        'Orden_Plan' => 'int',
        'ID_Pareja' => 'int',
        'ID_Pareja2' => 'int',
        'ID_Pareja3' => 'int',
        'Mat' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(Modalidad::class, 'ID_Modalidad', 'ID');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'ID_Nivel', 'ID');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'ID_Institucion', 'ID');
    }

    public function preceptor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Preceptor', 'ID');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanEstudio::class, 'ID_Plan', 'ID');
    }

    public function pareja(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Pareja', 'ID');
    }

    public function pareja2(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Pareja2', 'ID');
    }

    public function pareja3(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Pareja3', 'ID');
    }

    public function registroContactoInteres(): HasMany
    {
        return $this->hasMany(RegistroContactoInteres::class, 'ID_Curso', 'ID');
    }

    public function registroContactoSolicitud(): HasMany
    {
        return $this->hasMany(RegistroContactoSolicitud::class, 'ID_Curso', 'ID');
    }

    public function agenda(): HasMany
    {
        return $this->hasMany(Agenda::class, 'ID_Curso', 'ID');
    }

    public function agendaComun(): HasMany
    {
        return $this->hasMany(AgendaComun::class, 'ID_Curso', 'ID');
    }

    public function agrupacion(): HasMany
    {
        return $this->hasMany(Agrupacion::class, 'ID_Curso', 'ID');
    }

    public function alumno(): HasMany
    {
        return $this->hasMany(Alumno::class, 'ID_Curso', 'ID');
    }

    public function alumnoCla(): HasMany
    {
        return $this->hasMany(AlumnoCla::class, 'ID_Curso', 'ID');
    }

    public function alumnosSc(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AlumnoSc::class, 'ID_Curso', 'ID');
    }

    public function asistencias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asistencia::class, 'ID_Cursos', 'ID');
    }

    public function asistenciaBio(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsistenciaBio::class, 'ID_Cursos', 'ID');
    }

    public function asistenciaCb(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsistenciaCb::class, 'ID_Cursos', 'ID');
    }

    public function asistenciaMateria(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AsistenciaMateria::class, 'ID_Cursos', 'ID');
    }

    public function boletinCalificacion()
    {
        return $this->hasMany(BoletinCalificacion::class, 'ID_Cursos', 'ID');
    }

    public function cambioCursoOrigen(): HasMany
    {
        return $this->hasMany(CambioCurso::class, 'ID_Curso_O', 'ID');
    }

    public function cambioCursoDestino(): HasMany
    {
        return $this->hasMany(CambioCurso::class, 'ID_Curso_D', 'ID');
    }

    public function chatDocenteGrupo(): HasMany
    {
        return $this->hasMany(ChatDocenteGrupo::class, 'ID_Curso', 'ID');
    }

    public function claseVirtual(): HasMany
    {
        return $this->hasMany(ClaseVirtual::class, 'ID_Curso', 'ID');
    }

    public function claseVirtualContenido(): HasMany
    {
        return $this->hasMany(ClaseVirtualContenido::class, 'ID_Curso', 'ID');
    }

    public function comunicado(): HasMany
    {
        return $this->hasMany(Comunicado::class, 'ID_Curso', 'ID');
    }

    public function cronogramaCurso(): HasMany
    {
        return $this->hasMany(CronogramaCurso::class, 'ID_Curso', 'ID');
    }

    public function cursoGrupo(): HasMany
    {
        return $this->hasMany(CursoGrupo::class, 'ID_Curso', 'ID');
    }

    public function egresado(): HasMany
    {
        return $this->hasMany(Egresado::class, 'ID_Curso', 'ID');
    }

    public function envioCuota(): HasMany
    {
        return $this->hasMany(EnvioCuota::class, 'ID_Curso', 'ID');
    }

    public function envioCuotaAlumno(): HasMany
    {
        return $this->hasMany(EnvioCuotaAlumno::class, 'ID_Curso', 'ID');
    }

    public function envioCuotaCurso(): HasMany
    {
        return $this->hasMany(EnvioCuotaCurso::class, 'ID_Curso', 'ID');
    }

    public function envioDocumentacion(): HasMany
    {
        return $this->hasMany(EnvioDocumentacion::class, 'ID_Curso', 'ID');
    }

    public function evaluacionColegiada(): HasMany
    {
        return $this->hasMany(EvaluacionColegiada::class, 'ID_Curso', 'ID');
    }

    public function evaluacionColegiadaAlumno(): HasMany
    {
        return $this->hasMany(EvaluacionColegiadaAlumno::class, 'ID_Curso', 'ID');
    }

    public function evaluacionColegiadaDetalleMateria(): HasMany
    {
        return $this->hasMany(EvaluacionColegiadaDetalleMateria::class, 'ID_Curso', 'ID');
    }

    public function evaluacionCualitativa(): HasMany
    {
        return $this->hasMany(EvaluacionCualitativa::class, 'ID_Curso', 'ID');
    }

    public function foro(): HasMany
    {
        return $this->hasMany(Foro::class, 'ID_Curso', 'ID');
    }

    public function horario(): HasMany
    {
        return $this->hasMany(Horario::class, 'ID_Curso', 'ID');
    }

    public function inasistenciaTmp(): HasMany
    {
        return $this->hasMany(InasistenciaTmp::class, 'ID_Curso', 'ID');
    }

    public function informeCualitativo(): HasMany
    {
        return $this->hasMany(InformeCualitativo::class, 'ID_Curso', 'ID');
    }

    public function materia(): HasMany
    {
        return $this->hasMany(Materia::class, 'ID_Curso', 'ID');
    }

    public function medicionAulica(): HasMany
    {
        return $this->hasMany(MedicionAulica::class, 'ID_Curso', 'ID');
    }

    public function mesaExamenInscripcion(): HasMany
    {
        return $this->hasMany(MesaExamenInscripcion::class, 'ID_Curso', 'ID');
    }

    public function notaOperacion(): HasMany
    {
        return $this->hasMany(NotaOperacion::class, 'ID_Curso', 'ID');
    }

    public function notaParcial(): HasMany
    {
        return $this->hasMany(NotaParcial::class, 'ID_Curso', 'ID');
    }

    public function notaTrimestral(): HasMany
    {
        return $this->hasMany(NotaTrimestral::class, 'ID_Curso', 'ID');
    }

    public function parte(): HasMany
    {
        return $this->hasMany(Parte::class, 'ID_Curso', 'ID');
    }

    public function parteMateria()
    {
        return $this->hasMany(ParteMateria::class, 'ID_Curso', 'ID');
    }

    public function permisoEncabezado(): HasMany
    {
        return $this->hasMany(PermisoEncabezado::class, 'ID_Curso', 'ID');
    }

    public function procesoValoracionAlumno(): HasMany
    {
        return $this->hasMany(ProcesoValoracionAlumno::class, 'ID_Curso', 'ID');
    }

    public function procesoValoracionDetalleIndicador(): HasMany
    {
        return $this->hasMany(ProcesoValoracionDetalleIndicador::class, 'ID_Curso', 'ID');
    }

    public function procesosValoracionDetalleMateria(): HasMany
    {
        return $this->hasMany(ProcesoValoracionDetalleMateria::class, 'ID_Curso', 'ID');
    }

    public function procesoValoracionMateria(): HasMany
    {
        return $this->hasMany(ProcesoValoracionMateria::class, 'ID_Curso', 'ID');
    }

    public function publicacion(): HasMany
    {
        return $this->hasMany(Publicacion::class, 'ID_Curso', 'ID');
    }

    public function reclamo(): HasMany
    {
        return $this->hasMany(Reclamo::class, 'ID_Curso', 'ID');
    }
}
