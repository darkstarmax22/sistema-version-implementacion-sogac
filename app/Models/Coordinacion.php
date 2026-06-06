<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coordinacion extends RepositorioModel
{
    use HasFactory;

    protected $table = 'coordinaciones';
    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];
}
