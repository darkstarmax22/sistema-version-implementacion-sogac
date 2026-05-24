<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComunidadContacto extends Model
{
    use HasFactory;

    protected $fillable = ['comunidad_id', 'nombre', 'role_id', 'telefono', 'correo'];

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class);
    }

    public function cargo()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
