<?php

namespace App\Models\Institucional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recurso extends Model
{
    //protected $connection = 'mysql2';
    protected $table = 'recursos';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'Recurso',
        'Cantidad',
        'Descripcion',
        'ID_Tipo',
        'ID_Nivel',
        'Estado',
        'B'
    ];

    protected $casts = [
        'Cantidad' => 'int',
        'ID_Tipo' => 'int',
        'ID_Nivel' => 'int',
        'B' => 'int'
    ];

    protected $hidden = [];
    protected $appends = [];

    public function getTipoRecursoNombreAttribute()
    {
        $tipos_de_recurso = [
            1 => "Tecnológico",
            2 => "Tradicional",
            3 => "Espacio Común"
        ];
        return $tipos_de_recurso[$this->ID_Tipo];
    }

    public function bloqueos(): HasMany
    {
        return $this->hasMany(RecursoBloqueo::class, 'ID_Recurso', 'ID');
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(RecursoTipo::class, 'ID_Tipo', 'ID'); //No exsiste lo deben haber agregado pero no pinto
    }

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'ID_Nivel', 'ID');
    }
}
