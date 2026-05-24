<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Componente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'coordinacion_id',
        'anio',
        'es_obligatorio',
        'estado_logico'
    ];

    public function coordinacion()
    {
        return $this->belongsTo(Coordinacion::class);
    }
}
