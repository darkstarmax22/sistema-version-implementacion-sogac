<?php

namespace App\Models;

class LineaInvestigacion extends RepositorioModel
{
    protected $table = 'linea_investigacions';

    protected $fillable = [
        'nombre_investigacion',
        'descripcion',
        'area_de_investigacion',
    ];

    public function getActivoAttribute(): bool
    {
        $estado = $this->attributes['lin_estado'] ?? null;

        return $estado === 'Activo' || $estado === 'activo';
    }
}
