<?php

namespace App\Models\Institucional;

use App\Casts\DateCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Personal extends Model
{
    //protected $connection = 'mysql2';
    protected $table = 'personal';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Apellido',
        'DNI',
        'Fecha_de_nacimiento',
        'ID_Cargo',
        'Direccion',
        'Telefono',
        'Usuario',
        'Contrasenia',
        'Tipo',
        'Mail',
        'Nivel',
        'PIC',
        'Estado',
        'Firma'
    ];

    protected $casts = [
        'Fecha_de_nacimiento' => DateCast::class,
        'ID_Cargo' => 'int',
        'Nivel' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'ID_Cargo', 'ID');
    }

    public function agenda(): BelongsTo
    {
        return $this->belongsTo(Agenda::class, 'ID_Docente', 'ID');
    }

    public function agendaComun(): BelongsTo
    {
        return $this->belongsTo(AgendaComun::class, 'ID_Docente', 'ID');
    }

    public function alertasPf()
    {
        return $this->hasMany(AlertaPf::class, 'ID_Personal', 'ID');
    }

    public function alertasPr()
    {
        return $this->hasMany(AlertaPr::class, 'ID_Personal', 'ID');
    }

    public function ausenciaDocente(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AvisoAusenciaDocente::class, 'ID_Docente', 'ID');
    }

    public function avisoAusenciaDocente(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AvisoAusenciaDocente::class, 'ID_Docente', 'ID');
    }

    public function calificacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Calificacion::class, 'ID_Docente', 'ID');
    }

    public function chatCodigoConversacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ChatCodigoConversacion::class, 'ID_Docente', 'ID');
    }

    public function codigoAuth(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CodigoAuth::class, 'ID_Personal', 'ID');
    }

    public function cronogramaCurso(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CronogramaCurso::class, 'ID_Docente', 'ID');
    }

    public function cronogramaCursoSupervision(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CronogramaCurso::class, 'ID_Supervision', 'ID');
    }

    public function cronogramaCursoComentario(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CronogramaCursoComentario::class, 'ID_Supervisor', 'ID');
    }

    public function cronogramaCursoDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CronogramaCursoDetalle::class, 'ID_Docente', 'ID');
    }

    public function cursoPreceptor(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Curso::class, 'ID_Preceptor', 'ID');
    }

    public function cursoPareja(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Curso::class, 'ID_Pareja', 'ID');
    }

    public function cursoPareja2(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Curso::class, 'ID_Pareja2', 'ID');
    }

    public function cursoPareja3(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Curso::class, 'ID_Pareja3', 'ID');
    }

    public function ecIndicadores(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EcIndicadores::class, 'ID_Autor', 'ID');
    }

    public function envioCuota(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EnvioCuota::class, 'ID_Autor', 'ID');
    }

    public function envioDocumentacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EnvioDocumentacion::class, 'ID_Autor', 'ID');
    }

    public function estructuraDidactica(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EstructuraDidactica::class, 'ID_Autor', 'ID');
    }

    public function estructuraDidacticaDetalle(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EstructuraDidacticaDetalle::class, 'ID_Autor', 'ID');
    }

    public function estructuraDidacticaDetallePersonal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EstructuraDidacticaDetalle::class, 'ID_Personal', 'ID');
    }

    public function evaluacionColegiada(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionColegiada::class, 'ID_Coordinador', 'ID');
    }

    public function evaluacionCualitativa(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EvaluacionCualitativa::class, 'ID_Docente', 'ID');
    }

    public function informeCualitativo(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InformeCualitativo::class, 'ID_Autor', 'ID');
    }

    public function materia(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Materia::class, 'ID_Personal', 'ID');
    }

    public function materiaAdjunto(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Materia::class, 'ID_Adjunto', 'ID');
    }

    public function materiaGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Personal', 'ID');
    }

    public function materiaGrupalAdjunto(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Adjunto', 'ID');
    }

    public function materiaGrupalAdjunto2(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Adjunto2', 'ID');
    }

    public function materiaGrupalAdjunto3(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Adjunto3', 'ID');
    }

    public function materiaGrupalAdjunto4(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Adjunto4', 'ID');
    }

    public function materiaGrupalAdjunto5(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MateriaGrupal::class, 'ID_Adjunto5', 'ID');
    }

    public function mesaExamenTitular(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamen::class, 'ID_Titular', 'ID');
    }

    public function mesaExamenVocal1(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamen::class, 'ID_Vocal1', 'ID');
    }

    public function mesaExamenVocal2(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamen::class, 'ID_Vocal2', 'ID');
    }

    public function mesaExamenCambio(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenCambio::class, 'ID_Docente', 'ID');
    }

    public function mesaExamenConvocatoriaTitular(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenConvocatoria::class, 'ID_Titular', 'ID');
    }

    public function mesaExamenConvocatoriaVocal1(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenConvocatoria::class, 'ID_Vocal1', 'ID');
    }

    public function mesaExamenInscripcion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MesaExamenInscripcion::class, 'ID_Titular', 'ID');
    }

    public function notaMesaExamen(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaMesaExamen::class, 'ID_Docente', 'ID');
    }

    public function notaParcial(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaParcial::class, 'ID_Profesor', 'ID');
    }

    public function notaParcialGrupal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaParcialGrupal::class, 'ID_Profesor', 'ID');
    }

    public function notaTrimestral(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotaTrimestral::class, 'ID_Profesor', 'ID');
    }

    public function notificacionEnviada(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(NotificacionEnviada::class, 'ID_Personal', 'ID');
    }

    public function pActividad(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PActividad::class, 'ID_Docente', 'ID');
    }

    public function pase(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Pase::class, 'ID_Personal', 'ID');
    }

    public function pCapacidad(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PCapacidad::class, 'ID_Docente', 'ID');
    }

    public function pContenido(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PContenido::class, 'ID_Docente', 'ID');
    }

    public function pPlanificacion(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PPlanificacion::class, 'ID_Docente', 'ID');
    }

    public function procesoValoracionFinalAlumno(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcesoValoracionFinalAlumno::class, 'ID_Docente', 'ID');
    }

    public function procesoValoracionNarrado(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcesoValoracionNarrado::class, 'ID_Docente', 'ID');
    }
}
