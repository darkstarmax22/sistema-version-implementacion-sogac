<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class UserRoleService
{
    /** @var array<string, string>|null */
    protected ?array $cachedAvailableRoles = null;

    protected ?string $cachedCedula = null;

    public function sessionKey(): string
    {
        return config('roles.session_key', 'active_role');
    }

    public function allowsFreeSessionRoles(): bool
    {
        return (bool) config('roles.allow_free_session_roles', true);
    }

    /**
     * @return list<string>
     */
    public function allowedSessionSlugs(): array
    {
        return array_values(array_map(
            fn (array $meta) => $meta['slug'],
            config('roles.module_buttons', [])
        ));
    }

    public function clearCache(): void
    {
        $this->cachedAvailableRoles = null;
        $this->cachedCedula = null;
    }

    /**
     * Roles detectados en la BD externa (solo referencia administrativa).
     *
     * @return array<string, string> slug => etiqueta
     */
    public function detectAvailableRoles(User $user): array
    {
        $cedula = trim((string) $user->usu_cedula);

        if ($this->cachedAvailableRoles !== null && $this->cachedCedula === $cedula) {
            return $this->cachedAvailableRoles;
        }

        $conn = \App\Helpers\DualDatabase::academicConnection();
        $roles = [];

        $userExt = DB::connection($conn)
            ->table('usuario')
            ->whereRaw('TRIM(usu_cedula) = ?', [$cedula])
            ->first();

        if ($userExt) {
            $nombre = trim((string) ($userExt->usu_nombre ?? ''));
            if ($nombre === 'PROGRAMADOR' || $nombre === 'admin') {
                $roles['administrador'] = $this->label('administrador');
            }

            $codRol = $userExt->usu_cod_rol ?? null;
            $mapped = config('roles.usu_cod_rol_map', []);
            if ($codRol !== null && isset($mapped[(int) $codRol])) {
                $slug = $mapped[(int) $codRol];
                $roles[$slug] = $this->label($slug);
            }
        }

        if (DB::connection($conn)->table('estudiante')->whereRaw('TRIM(est_cedula) = ?', [$cedula])->exists()) {
            $roles['estudiante'] = $this->label('estudiante');
        }

        if (DB::connection($conn)->table('seccion_unidad_docente')->whereRaw('TRIM(sud_ced_docente) = ?', [$cedula])->exists()) {
            $roles['profesor proyecto'] = $this->label('profesor proyecto');
        }

        $this->cachedAvailableRoles = $roles;
        $this->cachedCedula = $cedula;

        return $roles;
    }

    public function getActiveRole(User $user): ?string
    {
        $active = Session::get($this->sessionKey());
        if (! is_string($active) || $active === '') {
            return null;
        }

        $active = strtolower(trim($active));

        if ($this->allowsFreeSessionRoles()) {
            return in_array($active, $this->allowedSessionSlugs(), true) ? $active : null;
        }

        $available = $this->detectAvailableRoles($user);
        if (! array_key_exists($active, $available)) {
            Session::forget($this->sessionKey());

            return null;
        }

        return $active;
    }

    public function setActiveRole(User $user, string $role): bool
    {
        $role = strtolower(trim($role));

        if ($this->allowsFreeSessionRoles()) {
            if (! in_array($role, $this->allowedSessionSlugs(), true)) {
                return false;
            }

            Session::put($this->sessionKey(), $role);
            $this->clearCache();

            return true;
        }

        $available = $this->detectAvailableRoles($user);
        if (! array_key_exists($role, $available)) {
            return false;
        }

        Session::put($this->sessionKey(), $role);
        $this->clearCache();

        return true;
    }

    public function clearActiveRole(): void
    {
        Session::forget($this->sessionKey());
        $this->clearCache();
    }

    public function bootstrapSessionRole(User $user): void
    {
        if ($this->getActiveRole($user) !== null) {
            return;
        }

        if ($this->allowsFreeSessionRoles()) {
            return;
        }

        $available = $this->detectAvailableRoles($user);
        if ($available === [] || count($available) !== 1) {
            return;
        }

        Session::put($this->sessionKey(), array_key_first($available));
    }

    public function userHasRole(User $user, string ...$requestedRoles): bool
    {
        $active = $this->getActiveRole($user);

        if ($active !== null) {
            foreach ($requestedRoles as $requested) {
                if ($this->roleMatches($requested, $active)) {
                    return true;
                }
            }

            return false;
        }

        if ($this->allowsFreeSessionRoles()) {
            return false;
        }

        $available = array_keys($this->detectAvailableRoles($user));

        if (in_array('administrador', $available, true)) {
            return true;
        }

        foreach ($requestedRoles as $requested) {
            foreach ($available as $owned) {
                if ($this->roleMatches($requested, $owned)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function roleMatches(string $requested, string $ownedOrActive): bool
    {
        $requested = strtolower(trim($requested));
        $ownedOrActive = strtolower(trim($ownedOrActive));

        if ($requested === $ownedOrActive) {
            return true;
        }

        $aliases = config('roles.aliases', []);
        foreach ($aliases as $canonical => $list) {
            $normalized = array_map('strtolower', $list);
            if (in_array($requested, $normalized, true) && $ownedOrActive === strtolower($canonical)) {
                return true;
            }
            if (in_array($ownedOrActive, $normalized, true) && $requested === strtolower($canonical)) {
                return true;
            }
        }

        return false;
    }

    public function activeRoleLabel(User $user): ?string
    {
        $active = $this->getActiveRole($user);

        return $active ? $this->label($active) : null;
    }

    /**
     * @return list<array{key: string, label: string, slug: string, enabled: bool, active: bool}>
     */
    public function moduleRoleButtons(User $user): array
    {
        $active = $this->getActiveRole($user);
        $buttons = [];

        foreach (config('roles.module_buttons', []) as $key => $meta) {
            $slug = $meta['slug'];
            $buttons[] = [
                'key' => $key,
                'label' => $meta['label'],
                'slug' => $slug,
                'enabled' => true,
                'active' => $active === $slug,
            ];
        }

        return $buttons;
    }

    public function setActiveRoleByModuleKey(User $user, string $moduleKey): bool
    {
        $buttons = config('roles.module_buttons', []);
        if (! isset($buttons[$moduleKey])) {
            return false;
        }

        return $this->setActiveRole($user, $buttons[$moduleKey]['slug']);
    }

    protected function label(string $slug): string
    {
        return config('roles.labels.' . $slug, ucfirst($slug));
    }

}
