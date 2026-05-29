<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coordinacion extends RepositorioModel
{
    use HasFactory;

    protected $table = 'coordinaciones'; // Asumiendo que la tabla se llama 'coordinaciones'
    protected $fillable = [
        'nombre',
        'activo', // Asumiendo que tiene un campo 'activo'
    ];

    // Puedes añadir relaciones o métodos adicionales aquí si son necesarios
}
