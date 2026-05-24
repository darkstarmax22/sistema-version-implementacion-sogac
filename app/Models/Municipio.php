<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipio extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'estado_id'];

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function comunidades()
    {
        return $this->hasMany(Comunidad::class);
    }

    public function direcciones()
    {
        return $this->hasMany(Direccion::class);
    }
}
