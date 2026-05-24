<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Proyecto extends RepositorioModel
{
    protected $table = 'proyectos';

    protected $fillable = [
        'titulo',
        'resumen',
        'fecha_subida',
        'asignacion_ct',
        'calificacion',
        'fecha_aprobacion',
        'linea_investigacion_id',
        'metodologia_id',
        'tipo_publicacion_id',
        'tipo_investigacion_id',
        'estado_logico',
        'archivo_path',
        'estado_validacion',
        'motivo_rechazo',
        'comunidad_id',
    ];

    protected $casts = [
        'fecha_subida' => 'date',
        'fecha_aprobacion' => 'date',
        'estado_logico' => 'boolean',
        'asignacion_ct' => 'boolean',
        'calificacion' => 'integer',
    ];

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado_logico', true);
    }

    public function scopeAprobados(Builder $query): Builder
    {
        return $query->where('estado_validacion', 'aprobado');
    }

    public function scopeVisiblesPublico(Builder $query): Builder
    {
        return $query->activos()->aprobados();
    }

    public function linea_investigacion()
    {
        return $this->belongsTo(LineaInvestigacion::class, 'lin_codigo', 'lin_codigo');
    }

    public function metodologia()
    {
        return $this->belongsTo(MetodologiaInvestigacion::class, 'mei_codigo', 'mei_codigo');
    }

    public function tipo_publicacion()
    {
        return $this->belongsTo(TipoPublicacion::class, 'tpu_codigo', 'tpu_codigo');
    }

    public function tipo_investigacion()
    {
        return $this->belongsTo(TipoInvestigacion::class, 'tin_codigo', 'tin_codigo');
    }

    public function comunidad()
    {
        return $this->belongsTo(Comunidad::class, 'com_codigo', 'com_codigo');
    }
}
