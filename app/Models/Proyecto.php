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
        'documentos',
        'estado_validacion',
        'motivo_rechazo',
        'comunidad_id',
        'equipo_ref',
    ];

    public function getEquipoResumenAttribute(): string
    {
        return app(\App\Services\IntranetEquipoSeccionService::class)
            ->resumenEquipo($this->equipo_ref);
    }

    protected $casts = [
        'fecha_subida' => 'date',
        'fecha_aprobacion' => 'date',
        'pry_fecha_subida' => 'date',
        'pry_fecha_aprobacion' => 'date',
        'pry_documentos' => 'array',
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

    public function scopeBusquedaPublica(Builder $query, ?string $search = null, ?int $coordinacionId = null, ?string $lapso = null): Builder
    {
        $query->visiblesPublico()->with(['tipo_publicacion', 'linea_investigacion', 'comunidad']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', '%'.$search.'%')
                    ->orWhere('resumen', 'like', '%'.$search.'%');
            });
        }

        if ($coordinacionId) {
            $query->whereHas('linea_investigacion', function ($q) use ($coordinacionId) {
                $q->where('coordinacion_id', $coordinacionId);
            });
        }

        // El filtro de lapso depende de cómo se guarde en equipo_ref o similar
        // Por ahora lo dejamos así si no hay una columna directa de lapso en proyectos
        
        return $query;
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

    /**
     * Aprueba el proyecto.
     */
    public function aprobar(): bool
    {
        return $this->update([
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ]);
    }

    /**
     * Rechaza el proyecto.
     */
    public function rechazar(string $motivo): bool
    {
        return $this->update([
            'estado_validacion' => 'rechazado',
            'motivo_rechazo' => $motivo,
            'estado_logico' => false,
        ]);
    }

    /**
     * Obtiene proyectos pendientes para validación.
     */
    public static function pendientes(?string $search = null)
    {
        $query = self::with(['tipo_publicacion', 'linea_investigacion', 'comunidad'])
            ->where('estado_validacion', 'pendiente');

        if ($search) {
            $query->where('titulo', 'like', '%'.$search.'%');
        }

        return $query;
    }
}
