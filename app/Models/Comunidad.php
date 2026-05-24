<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comunidad extends RepositorioModel
{
    use HasFactory;

    protected $table = 'comunidades';

    protected $fillable = [
        'nombre',
        'rif',
        'correo',
        'numero_telefono',
        'anio',
    ];

}
