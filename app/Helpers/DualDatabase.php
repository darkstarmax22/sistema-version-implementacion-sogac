<?php

namespace App\Helpers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DualDatabase
{
    public static function academicConnection(): string
    {
        return DbHelper::connection();
    }

    public static function repositorioConnection(): string
    {
        return (string) config('dual_database.repositorio_connection', 'mysql');
    }

    public static function isIntranetTable(string $table): bool
    {
        return in_array($table, config('dual_database.intranet_tables', []), true);
    }

    public static function isRepositorioTable(string $table): bool
    {
        return in_array($table, config('dual_database.repositorio_tables', []), true);
    }

    public static function hasTable(string $connection, string $table): bool
    {
        try {
            return Schema::connection($connection)->hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Doble consulta académica: intranet (o simulación) y, si no hay tabla/datos, repositorio local.
     */
    public static function table(string $table): Builder
    {
        $localTable = config("dual_database.local_aliases.{$table}", $table);
        $academic = self::academicConnection();

        if (self::hasTable($academic, $table)) {
            return DB::connection($academic)->table($table);
        }

        $repo = self::repositorioConnection();
        if (self::hasTable($repo, $localTable)) {
            return DB::connection($repo)->table($localTable);
        }

        throw new RuntimeException("La tabla académica [{$table}] no existe en intranet/simulación ni en repositorio.");
    }

    /**
     * Tablas propias del módulo repositorio (siempre MySQL repositorio).
     */
    public static function repositorioTable(string $table): Builder
    {
        $repo = self::repositorioConnection();

        if (! self::hasTable($repo, $table)) {
            throw new RuntimeException("La tabla [{$table}] no existe en la base repositorio.");
        }

        return DB::connection($repo)->table($table);
    }

    /**
     * @return array<int, object>
     */
    public static function get(string $table, ?string $orderBy = null): array
    {
        $query = self::table($table);
        if ($orderBy) {
            $query->orderBy($orderBy);
        }

        return $query->get()->all();
    }

    /**
     * @return object|null
     */
    public static function firstWhere(string $table, string $column, mixed $value): ?object
    {
        return self::table($table)->where($column, $value)->first();
    }
}
