<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DbHelper
{
    /**
     * Cache dinámicamente el nombre de la conexión activa para evitar múltiples reintentos de timeout en la misma petición.
     */
    protected static ?string $connectionName = null;

    /** Evita repetir el ping a intranet en la misma petición HTTP. */
    protected static bool $resolved = false;

    /** true si en esta petición la conexión académica activa es intranet (no simulación). */
    protected static bool $usingIntranet = false;

    /**
     * Retorna el nombre de la conexión activa. Si la intranet está caída, retorna 'simulacion' como fallback.
     */
    public static function connection()
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        try {
            // Reintento rápido para no bloquear la petición si la intranet está caída
            // Usamos un timeout corto de 2 segundos para la detección inicial
            DB::connection('intranet')->getPdo();
            self::$connectionName = 'intranet';
            self::$usingIntranet = true;
        } catch (\Exception $e) {
            Log::warning('Intranet no disponible; usando simulación automáticamente. '.$e->getMessage());
            self::$connectionName = 'simulacion';
            self::$usingIntranet = false;
        }

        self::$resolved = true;

        return self::$connectionName;
    }

    public static function isUsingIntranet(): bool
    {
        self::connection();

        return self::$usingIntranet;
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
        self::$usingIntranet = false;
    }
}
