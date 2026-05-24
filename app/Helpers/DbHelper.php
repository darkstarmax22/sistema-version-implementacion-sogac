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

    /**
     * Retorna el nombre de la conexión activa. Si la intranet está caída, retorna 'simulacion' como fallback.
     */
    public static function connection()
    {
        if (self::$resolved && self::$connectionName !== null) {
            return self::$connectionName;
        }

        try {
            // Intentar obtener el objeto PDO de la conexión intranet.
            // Si la conexión está caída, fallará y lanzará una excepción.
            DB::connection('intranet')->getPdo();
            self::$connectionName = 'intranet';
        } catch (\Exception $e) {
            // Fallback elegante a la base de datos de simulación local
            Log::warning("La base de datos externa (intranet) no está disponible. Usando base de datos de simulación como fallback. Error: " . $e->getMessage());
            self::$connectionName = 'simulacion';
        }

        self::$resolved = true;

        return self::$connectionName;
    }

    public static function reset(): void
    {
        self::$connectionName = null;
        self::$resolved = false;
    }
}
