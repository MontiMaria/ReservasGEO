<?php

namespace App\Models\Institucional;

use App\Casts\DateCast;
use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoBloqueo extends Model
{
    //protected $connection = 'mysql2';
    protected $table = 'recursos_bloqueos';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_Recurso',
        'Dia_Semana',
        'HI',
        'HF',
        'ID_Nivel',
        //'ID_Curso',
        //'ID_Materia',
        //'Tipo_Materia',
        'Causa',
        'B',
        'Fecha_B',
        'Hora_B',
        'ID_Usuario_B'
    ];

    protected $casts = [
        'ID_Recurso' => 'int',
        'Dia_Semana' => 'int',
        'HI' => TimeCast::class,
        'HF' => TimeCast::class,
        'ID_Nivel' => 'int',
        //'ID_Curso' => 'int', //?
        //'ID_Materia' => 'int', //?
        //'Tipo_Materia' => 'int' //?
        'B' => 'int',
        'Fecha_B' => DateCast::class,
        'Hora_B' => TimeCast::class,
        'ID_Usuario_B' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function getDiaSemanaNombreAttribute()
    {
        $dias = [
            1 => "Lunes",
            2 => "Martes",
            3 => "Miércoles",
            4 => "Jueves",
            5 => "Viernes"
        ];
        return $dias[$this->Dia_Semana] ?? null;
    }

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
}
