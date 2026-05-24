<?php

namespace App\Models;

class TipoPublicacion extends RepositorioModel
{
    protected $table = 'tipo_publicacions';

    protected $fillable = [
        'nombre',
        'mencion_honorifica',
        'estado_logico'
    ];

    protected $casts = [
        'estado_logico' => 'boolean',
        'mencion_honorifica' => 'boolean'
    ];
}
