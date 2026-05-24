<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estado extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function comunidades()
    {
        return $this->hasMany(Comunidad::class);
    }
}
