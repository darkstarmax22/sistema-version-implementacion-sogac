<?php

namespace App\Providers;

use App\Services\UserRoleService;
use App\Support\NavigationMenu;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UserRoleService::class);
        $this->app->singleton(NavigationMenu::class);
    }

    public function boot(): void
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            URL::forceRootUrl($scheme . $_SERVER['HTTP_HOST']);
        }

        // Listen to all model saving events to automatically uppercase string attributes
        \Illuminate\Support\Facades\Event::listen('eloquent.saving: *', function ($event, $models) {
            foreach ($models as $model) {
                // Ignore system role models to prevent breaking role-based permission checks
                $class = get_class($model);
                if ($class === 'App\Models\Role' || $class === 'App\Models\Rol' || $class === 'App\Models\DetalleRol') {
                    continue;
                }

                // List of columns to exclude from being automatically uppercased
                $except = [
                    'password',
                    'email',
                    'correo',
                    'remember_token',
                    'archivo_path',
                    'estado_validacion',
                    'motivo_rechazo',
                    'estado_lapso',
                    'estado_logico',
                    'activa',
                    'activo',
                    'token',
                    'verification_token',
                    'api_token',
                    'guard_name',
                    'id',
                    'uuid'
                ];

                foreach ($model->getAttributes() as $key => $value) {
                    if (is_string($value) && 
                        !in_array($key, $except) && 
                        !str_ends_with($key, '_id') && 
                        !str_ends_with($key, '_at') &&
                        !str_ends_with($key, '_date')
                    ) {
                        // Avoid modifying raw JSON strings (like casted arrays/objects)
                        if ((str_starts_with($value, '{') && str_ends_with($value, '}')) ||
                            (str_starts_with($value, '[') && str_ends_with($value, ']'))
                        ) {
                            continue;
                        }

                        // Convert to uppercase with UTF-8 multi-byte support (handling Spanish accents and ñ)
                        $model->setAttribute($key, mb_strtoupper($value, 'UTF-8'));
                    }
                }
            }
        });
    }
}
