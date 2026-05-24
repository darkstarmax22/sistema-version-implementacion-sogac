<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Direccion extends RepositorioModel
{
    use HasFactory;
    
    protected $table = 'direcciones';

    protected $fillable = ['direccion_exacta', 'municipio_id'];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function comunidades()
    {
        return $this->hasMany(Comunidad::class);
    }
}
