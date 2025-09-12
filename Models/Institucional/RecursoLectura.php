<?php

namespace App\Models\Institucional;

use App\Casts\DateCast;
use App\Casts\TimeCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecursoLectura extends Model
{
    protected $connection = 'mysql2';
    protected $table = 'recursos_lectura';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID_Reserva',
        'ID_Usuario',
        'Fecha',
        'Hora',
        'Leido',
        'Fecha_Leido',
        'Hora_Leido'
    ];

    protected $casts = [
        'ID_Reserva' => 'int',
        'ID_Usuario' => 'int',
        'Fecha' => DateCast::class,
        'Hora' => TimeCast::class,
        'Leido' => 'int',
        'Fecha_Leido' => DateCast::class,
        'Hora_Leido' => TimeCast::class
    ];

    protected $hidden = [];
    protected $appends = [];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(RecursoReserva::class, 'ID_Reserva', 'ID');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'ID_Usuario', 'ID');
    }
}
