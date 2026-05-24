<?php

namespace App\Models;

class MetodologiaInvestigacion extends RepositorioModel
{
    protected $table = 'metodologia_investigacions';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean'
    ];
}
