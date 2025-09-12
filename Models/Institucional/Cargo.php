<?php

namespace App\Models\Institucional;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cargo extends Model
{
    //protected $connection = 'mysql2';
    protected $table = 'cargo';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'ID',
        'Cargo',
        'Tipo',
        'New',
        'ID_Nivel',
        'Seguridad',
        'Carpeta'
    ];

    protected $casts = [
        'ID' => 'int',
        'New' => 'int',
        'ID_Nivel' => 'int'
    ];

    protected $hidden = [];

    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'ID_Nivel', 'ID');
    }

    public function personal(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Personal::class, 'ID_Cargo', 'ID');
    }
}
