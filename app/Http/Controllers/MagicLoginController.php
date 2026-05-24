<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\UserRoleService;

class MagicLoginController extends Controller
{
    /**
     * Clave secreta para desencriptar el payload.
     */
    protected function getKey(): string
    {
        return base64_decode(config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M='));
    }

    /**
     * Desencripta el payload del enlace.
     */
    protected function decryptPayload(string $ticket): ?array
    {
        $key = $this->getKey();

        $json_payload = base64_decode($ticket);
        if (!$json_payload) return null;

        $envelope = json_decode($json_payload, true);
        if (!$envelope || !isset($envelope['iv'], $envelope['value'], $envelope['mac'])) return null;

        // Verificar MAC (integridad)
        $expectedMac = hash_hmac('sha256', $envelope['iv'] . $envelope['value'], $key);
        if (!hash_equals($expectedMac, $envelope['mac'])) return null;

        // Desencriptar
        $iv = base64_decode($envelope['iv']);
        $encryptedValue = base64_decode($envelope['value']);
        $decrypted = openssl_decrypt($encryptedValue, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) return null;

        $data = json_decode($decrypted, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Procesa el login mediante enlace encriptado.
     */
    public function login(Request $request)
    {
        $ticket = $request->query('payload');

        if (empty($ticket)) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Acceso Denegado</h1>
                <p>No se proporcionó un enlace de acceso válido.</p>
            </body></html>', 403);
        }

        // 1. Desencriptar payload
        $payload = $this->decryptPayload($ticket);

        if (!$payload || !isset($payload['cedula'], $payload['fecha_creacion'], $payload['firma_validacion'])) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Inválido</h1>
                <p>El enlace de acceso no es válido o fue manipulado.</p>
            </body></html>', 403);
        }

        // 2. Verificar firma
        $keyB64 = config('app.sogac_key', 'RXN0ZUVzVW5TZWNyZXRvRGUzMkJ5dGVzRXhhY3Rvc3M=');
        $seed = $payload['cedula'] . $payload['fecha_creacion'] . $keyB64;
        $firmaEsperada = hash('sha256', $seed);

        if (!hash_equals($firmaEsperada, $payload['firma_validacion'])) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Inválido</h1>
                <p>La firma del enlace no es válida.</p>
            </body></html>', 403);
        }

        // 3. Verificar expiración del enlace (por defecto 1 día; la sesión web es independiente y más larga)
        $ttl = (int) config('app.magic_link_ttl', 86400);
        $elapsed = time() - (int) $payload['fecha_creacion'];
        if ($elapsed > $ttl || $elapsed < 0) {
            $horas = (int) round($ttl / 3600);
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Enlace Expirado</h1>
                <p>Este enlace de acceso ha expirado (válido por ' . $horas . ' horas). Genere uno nuevo desde la terminal.</p>
            </body></html>', 403);
        }

        $cedula = trim($payload['cedula']);

        try {
            // 4. Buscar usuario en BD externa
            $user = User::whereRaw('TRIM(usu_cedula) = ?', [$cedula])->first();

            if (!$user) {
                return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                    <h1 style="color:#c00;">Error</h1>
                    <p>El usuario no fue encontrado en la base de datos.</p>
                </body></html>', 404);
            }

            // 5. Login
            Auth::login($user);

            // 6. Regenerar sesión
            $request->session()->regenerate();

            $roleService = app(UserRoleService::class);
            $roleService->bootstrapSessionRole($user);

            if ($roleService->getActiveRole($user) === null) {
                return redirect()->route('acceso-rol.index');
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
                <h1 style="color:#c00;">Error de Conexión</h1>
                <p>' . $e->getMessage() . '</p>
            </body></html>', 500);
        }
    }
}
