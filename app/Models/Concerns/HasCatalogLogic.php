<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasCatalogLogic
{
    /**
     * Guarda o actualiza un registro del catálogo.
     */
    public static function guardar(array $datos, ?int $id = null): self
    {
        if ($id === null) {
            return self::create($datos);
        }

        $model = self::query()->whereKey($id)->first();

        if (! $model) {
            $model = new self();
            $model->setAttribute($model->getKeyName(), $id);
        }

        $model->fill($datos);
        $model->save();

        return $model;
    }

    /**
     * Alterna el estado del registro (activo o estado_logico).
     */
    public function alternarEstado(): bool
    {
        $columna = property_exists($this, 'statusColumn') ? $this->statusColumn : 'activo';
        
        // Si no existe la propiedad, intentamos detectar si es estado_logico
        if (!property_exists($this, 'statusColumn') && !isset($this->attributes['activo']) && isset($this->attributes['estado_logico'])) {
            $columna = 'estado_logico';
        }

        return $this->update([$columna => !$this->$columna]);
    }

    /**
     * Elimina el registro.
     */
    public function borrar(): ?bool
    {
        return $this->delete();
    }
}
