<?php

namespace App\Models;

class Coordinacion extends RepositorioModel
{
    protected $table = 'coordinaciones';

    protected $fillable = ['nombre', 'descripcion', 'activo', 'alertar_comunidades'];

    protected $casts = [
        'activo' => 'boolean',
        'alertar_comunidades' => 'boolean',
    ];
}
