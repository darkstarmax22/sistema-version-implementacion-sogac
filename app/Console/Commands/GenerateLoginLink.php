<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLoginLink extends Command
{
    protected $signature = 'app:generate-login-link';
    protected $description = 'Genera un enlace de acceso verificando usuario y contraseña con la BD externa.';

    /**
     * Clave secreta para encriptar el payload del enlace.
     */
    protected function getKey(): string
    {
        return base64_decode(config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));
    }

    /**
     * Encripta datos en formato compatible con Crypt de Laravel.
     */
    protected function encryptPayload(array $data): string
    {
        $key = $this->getKey();
        $iv = openssl_random_pseudo_bytes(16);

        $value = json_encode($data);
        $encryptedValue = openssl_encrypt($value, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        $iv_b64 = base64_encode($iv);
        $value_b64 = base64_encode($encryptedValue);

        $mac = hash_hmac('sha256', $iv_b64 . $value_b64, $key);

        $json_payload = json_encode([
            'iv' => $iv_b64,
            'value' => $value_b64,
            'mac' => $mac,
            'tag' => ''
        ]);

        return base64_encode($json_payload);
    }

    public function handle()
    {
        $this->info('--- Generador de Enlace de Acceso ---');
        $this->line('');

        $input = $this->ask('Ingrese su usuario o cédula');

        if (empty($input)) {
            $this->error('Debe ingresar un identificador válido.');
            return 1;
        }

        $password = $this->secret('Ingrese su contraseña');

        if (empty($password)) {
            $this->error('Debe ingresar una contraseña.');
            return 1;
        }

        $input = trim($input);
        $this->info('Verificando credenciales...');

        try {
            $connectionName = \App\Helpers\DbHelper::connection();

            $extUser = DB::connection($connectionName)
                ->table('usuario')
                ->leftJoin('persona', DB::raw('TRIM(usuario.usu_cedula)'), '=', DB::raw('TRIM(persona.per_cedula)'))
                ->where(function($q) use ($input) {
                    $q->where(DB::raw('TRIM(usuario.usu_nombre)'), trim($input))
                      ->orWhere(DB::raw('TRIM(usuario.usu_cedula)'), trim($input));
                })
                ->select([
                    'usuario.usu_cedula', 'usuario.usu_nombre', 'usuario.usu_clave',
                    'usuario.usu_cod_rol', 'persona.per_nombres', 'persona.per_apellidos'
                ])
                ->first();

            if (!$extUser) {
                $this->error('Usuario no encontrado.');
                return 1;
            }

            // Verificar contraseña con bcrypt + strtoupper (como SOGAC)
            $storedHash = trim($extUser->usu_clave ?? '');
            $passwordUpper = strtoupper($password);

            if (!password_verify($passwordUpper, $storedHash)) {
                $this->error('Contraseña incorrecta.');
                return 1;
            }

            $cedula = trim($extUser->usu_cedula);
            $nombre = mb_strtoupper(trim($extUser->per_nombres ?? '') . ' ' . trim($extUser->per_apellidos ?? ''));
            $this->info("✓ Credenciales válidas: {$nombre} (Cédula: {$cedula})");

            // Generar payload encriptado
            $timestamp = time();
            $seed = $cedula . $timestamp . config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M=');
            $firma = hash('sha256', $seed);

            $payload = [
                'cedula' => $cedula,
                'fecha_creacion' => $timestamp,
                'firma_validacion' => $firma
            ];

            $ticket = $this->encryptPayload($payload);
            $url = 'http://localhost:8000/login?payload=' . urlencode($ticket);

            $this->line('');
            $this->info('¡Enlace generado exitosamente!');
            $this->line('');
            $ttlHoras = (int) round((int) config('app.magic_link_ttl', 86400) / 3600);
            $this->comment("Copie y pegue este enlace en su navegador (válido por {$ttlHoras} horas; la sesión en el sistema dura mucho más):");
            $this->line('');
            $this->line('<fg=cyan>' . $url . '</>');
            $this->line('');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
