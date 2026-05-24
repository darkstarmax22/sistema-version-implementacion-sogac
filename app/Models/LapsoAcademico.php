<?php

namespace App\Models;

class LapsoAcademico extends IntranetModel
{
    protected $table = 'lapso_academico';
    protected $primaryKey = 'lap_codigo';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'lap_codigo',
        'lap_nombre',
        'lap_fecha_inicio',
        'lap_fecha_fin',
        'lap_cod_tipo_lapso',
        'lap_cod_universidad',
        'lap_condicion',
        'lap_estatus',
        'lap_cerrado',
        'lap_nota',
    ];

    protected $casts = [
        'lap_fecha_inicio' => 'date',
        'lap_fecha_fin' => 'date',
    ];

    public function getIdAttribute()
    {
        return $this->lap_codigo;
    }

    public function getNombreAttribute()
    {
        return $this->lap_nombre;
    }

    public function getFechaInicioAttribute()
    {
        return $this->lap_fecha_inicio;
    }

    public function getFechaFinAttribute()
    {
        return $this->lap_fecha_fin;
    }

    public function getEstadoLapsoAttribute()
    {
        return $this->lap_estatus === 'A';
    }
}
