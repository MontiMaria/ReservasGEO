<?php

namespace App\Models\Institucional;

use App\Casts\DateCast;
use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoReserva extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'recursos_reservas';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
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
        'Actividad',
        'B',
        'Fecha_B',
        'Hora_B',
        'ID_Usuario_B',
        'B_Motivo'
    ];

    protected $casts = [
        'Fecha' => DateCast::class,
        'Hora' => TimeCast::class,
        'ID_Recurso' => 'int',
        'Fecha_R' => DateCast::class,
        'Hora_Inicio' => TimeCast::class,
        'Hora_Fin' => TimeCast::class,
        'ID_Nivel' => 'int',
        'ID_Curso' => 'int',
        'ID_Materia' => 'int',
        'ID_Docente' => 'int',
        'B' => 'int',
        'Fecha_B' => DateCast::class,
        'Hora_B' => TimeCast::class,
        'ID_Usuario_B' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(Recurso::class, 'ID_Recurso', 'ID');
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'ID_Nivel', 'ID');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'ID_Curso', 'ID');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'ID_Materia', 'ID');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'ID_Docente', 'ID');
    }

    public function usuarioB(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'ID_Usuario_B', 'ID');
    }
}
