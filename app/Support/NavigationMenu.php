<?php

namespace App\Support;

use App\Models\User;
use App\Services\ProyectoGestionService;
use App\Services\UserRoleService;
use Illuminate\Support\Facades\Cache;

/**
 * Permisos de menú según rol activo en sesión (sin depender de tablas roles locales).
 */
class NavigationMenu
{
    protected array $cache = [];

    protected const CACHE_TTL = 300;

    public function __construct(
        protected UserRoleService $roles,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function flags(?User $user): array
    {
        if ($user === null) {
            return $this->emptyFlags();
        }

        $cacheKey = $user->usu_cedula . '_' . session($this->roles->sessionKey(), 'none');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $sessionKey = 'nav_flags_' . $cacheKey;
        $cached = Cache::get($sessionKey);
        if ($cached !== null) {
            $this->cache[$cacheKey] = $cached;
            return $cached;
        }

        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('coordinador');
        $isTeacher = $user->hasRole('profesor proyecto');
        $isStudent = $user->hasRole('estudiante');
        $studentWithTeam = $isStudent && $user->perteneceAEquipo();

        $flags = [
            'isAdmin'              => $isAdmin,
            'isCoordinator'        => $isCoordinator,
            'isTeacher'            => $isTeacher,
            'isStudent'            => $isStudent,
            'canViewAcademic'      => $isAdmin || $isCoordinator || $isTeacher || $studentWithTeam,
            'canViewComunes'       => $isAdmin || $isCoordinator || $isTeacher || $studentWithTeam,
            'canManageCatalogs'    => $isAdmin,
            'canManageComponents'  => $isAdmin || $isCoordinator,
            'canValidateProjects'  => app(ProyectoGestionService::class)->usuarioPuedeValidar($user),
            'canRegisterProject'   => $user->puedeRegistrarProyecto(),
            'canManageSystemConfig'=> $isAdmin || $isCoordinator,
            'canManageOrganizaciones' => trim((string) $user->usu_cedula) === '13354832', // Solo Gestionador (cédula 13354832)
        ];

        Cache::put($sessionKey, $flags, now()->addSeconds(self::CACHE_TTL));

        $this->cache[$cacheKey] = $flags;

        return $flags;
    }

    /**
     * @return array<string, bool>
     */
    protected function emptyFlags(): array
    {
        return array_fill_keys([
            'isAdmin', 'isCoordinator', 'isTeacher', 'isStudent',
            'canViewAcademic', 'canViewComunes', 'canManageCatalogs',
            'canManageComponents', 'canValidateProjects', 'canRegisterProject',
            'canManageSystemConfig', 'canManageCoordinators',
            'canManageOrganizaciones',
        ], false);
    }
}
