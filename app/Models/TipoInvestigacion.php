<?php

namespace App\Models;

class TipoInvestigacion extends RepositorioModel
{
    protected $table = 'tipo_investigacions';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
