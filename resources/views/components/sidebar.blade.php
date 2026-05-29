@php
    $nav = app(\App\Support\NavigationMenu::class)->flags(auth()->user());
@endphp

<link rel="stylesheet" href="{{ asset('css/legacy-sidebar.css') }}">

<aside class="legacy-sidebar" id="menu_lateral">
    <nav class="legacy-nav">
        <ul>
            <li>
                <a href="{{ route('dashboard') }}"
                    class="legacy-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Inicio
                </a>
            </li>

            <li>
                <a href="{{ route('acceso-rol.index') }}"
                    class="legacy-menu-item {{ request()->routeIs('acceso-rol.index') ? 'active' : '' }}">
                    Simular rol
                </a>
            </li>

            @if ($nav['canViewAcademic'])
                <li>
                    <div class="legacy-menu-item has-submenu">
                        Gestión académica
                        <div class="arrow-icon"></div>
                    </div>
                    <div class="legacy-submenu">
                        @if ($nav['canViewComunes'])
                            <a href="{{ route('comunidades.index') }}"
                                class="{{ request()->routeIs('comunidades.index') ? 'active-sub' : '' }}">Comunidades</a>
                            <a href="{{ route('grupos-proyecto.index') }}"
                                class="{{ request()->routeIs('grupos-proyecto.index') ? 'active-sub' : '' }}">Equipos de
                                proyecto</a>
                        @endif

                        @if ($nav['canManageCatalogs'])
                            <a href="{{ route('lineas-investigacion') }}"
                                class="{{ request()->routeIs('lineas-investigacion') ? 'active-sub' : '' }}">Líneas de
                                investigación</a>
                            <a href="{{ route('tipos-investigacion') }}"
                                class="{{ request()->routeIs('tipos-investigacion') ? 'active-sub' : '' }}">Tipos de
                                investigación</a>
                            <a href="{{ route('metodologia-investigacion') }}"
                                class="{{ request()->routeIs('metodologia-investigacion') ? 'active-sub' : '' }}">Metodologías</a>
                            <a href="{{ route('tipos-publicacion') }}"
                                class="{{ request()->routeIs('tipos-publicacion') ? 'active-sub' : '' }}">Tipos de
                                publicación</a>
                        @endif

                        @if ($nav['canManageComponents'])
                            <a href="{{ route('componentes.index') }}"
                                class="{{ request()->routeIs('componentes.index') ? 'active-sub' : '' }}">Componentes</a>
                        @endif
                    </div>
                </li>
            @endif

            <li>
                <div class="legacy-menu-item has-submenu">
                    Proyectos
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('proyectos.buscar') }}"
                        class="{{ request()->routeIs('proyectos.buscar') ? 'active-sub' : '' }}">Explorar proyectos</a>
                    @if ($nav['canRegisterProject'] || $nav['canValidateProjects'])
                        <a href="{{ route('proyectos.gestion') }}"
                            class="{{ request()->routeIs('proyectos.gestion', 'proyectos.crear', 'validaciones.index') ? 'active-sub' : '' }}">Gestión
                            de proyectos</a>
                    @endif
                </div>
            </li>

            @if ($nav['canManageSystemConfig'])
                <li>
                    <div class="legacy-menu-item has-submenu">
                        Configuración
                        <div class="arrow-icon"></div>
                    </div>
                    <div class="legacy-submenu">
                        <a href="{{ route('profesores-proyecto.index') }}"
                            class="{{ request()->routeIs('profesores-proyecto.index') ? 'active-sub' : '' }}">Profesores
                            de proyecto</a>
                        @if ($nav['canManageCoordinators'])
                            <a href="{{ route('coordinadores.index') }}"
                                class="{{ request()->routeIs('coordinadores.index') ? 'active-sub' : '' }}">Coordinadores</a>
                        @endif
                    </div>
                </li>
            @endif

            <li>
                <div class="legacy-menu-item has-submenu">
                    Mi cuenta
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('configuracion') }}"
                        class="{{ request()->routeIs('configuracion') ? 'active-sub' : '' }}">Perfil</a>
                </div>
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                    @csrf
                    <button type="submit" class="legacy-menu-item" style="width: 100%; text-align: left;">
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>

<script>
    function initSidebarAccordion() {
        document.querySelectorAll('.has-submenu').forEach(header => {
            const clone = header.cloneNode(true);
            header.parentNode.replaceChild(clone, header);
            clone.addEventListener('click', function() {
                const body = this.nextElementSibling;
                const arrow = this.querySelector('.arrow-icon');
                const open = body.style.maxHeight && body.style.maxHeight !== '0px';
                document.querySelectorAll('.legacy-submenu').forEach(b => {
                    b.style.maxHeight = '0px';
                    const a = b.previousElementSibling?.querySelector('.arrow-icon');
                    if (a) a.style.transform = 'rotate(0deg)';
                });
                if (!open) {
                    body.style.maxHeight = body.scrollHeight + 'px';
                    if (arrow) arrow.style.transform = 'rotate(90deg)';
                }
            });
        });
        const active = document.querySelector('.active-sub');
        if (active) {
            const body = active.closest('.legacy-submenu');
            const header = body?.previousElementSibling;
            if (header?.classList.contains('has-submenu')) {
                body.style.maxHeight = body.scrollHeight + 'px';
                header.querySelector('.arrow-icon')?.style.setProperty('transform', 'rotate(90deg)');
            }
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarAccordion);
    } else {
        initSidebarAccordion();
    }
    document.addEventListener('livewire:navigated', initSidebarAccordion);
</script>
