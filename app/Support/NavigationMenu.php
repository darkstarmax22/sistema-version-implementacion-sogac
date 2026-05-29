<?php

namespace App\Support;

use App\Models\User;
use App\Services\ProyectoGestionService;
use App\Services\UserRoleService;

/**
 * Permisos de menú según rol activo en sesión (sin depender de tablas roles locales).
 */
class NavigationMenu
{
    protected array $cache = [];

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

        $isAdmin = $user->hasRole('administrador');
        $isCoordinator = $user->hasRole('coordinador');
        $isTeacher = $user->hasRole('profesor proyecto');
        $isStudent = $user->hasRole('estudiante');
        $studentWithTeam = $isStudent && $user->perteneceAEquipo();

        return $this->cache[$cacheKey] = [
            'isAdmin' => $isAdmin,
            'isCoordinator' => $isCoordinator,
            'isTeacher' => $isTeacher,
            'isStudent' => $isStudent,
            'canViewAcademic' => $isAdmin || $isCoordinator || $isTeacher || $studentWithTeam,
            'canViewComunes' => $isAdmin || $isCoordinator || $isTeacher || $studentWithTeam,
            'canManageCatalogs' => $isAdmin,
            'canManageComponents' => $isAdmin || $isCoordinator,
            'canValidateProjects' => app(ProyectoGestionService::class)->usuarioPuedeValidar($user),
            'canRegisterProject' => $user->puedeRegistrarProyecto(),
            'canManageSystemConfig' => $isAdmin || $isCoordinator,
            'canManageCoordinators' => $isAdmin,
        ];
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
        ], false);
    }
}
