<?php

namespace App\Database\Eloquent;

use App\Models\Concerns\MapsLegacyColumns;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin MapsLegacyColumns
 */
class LegacyColumnBuilder extends Builder
{
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure) {
            return parent::where($column, $operator, $value, $boolean);
        }

        if (is_array($column)) {
            return $this->whereNested(function ($query) use ($column) {
                foreach ($column as $key => $val) {
                    if (is_numeric($key)) {
                        $query->where($val);
                    } else {
                        $query->where($key, $val);
                    }
                }
            }, $boolean);
        }

        [$column, $operator, $value] = $this->model->qualifyLegacyWhere($column, $operator, $value);

        return parent::where($column, $operator, $value, $boolean);
    }

    public function orderBy($column, $direction = 'asc')
    {
        $column = $this->model->mapLegacyColumn($column);

        return parent::orderBy($column, $direction);
    }
}
