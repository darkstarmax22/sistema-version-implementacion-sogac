<style>
    /* Estilos derivados de la hoja provista (ul, li, a, hover) combinados con la estructura visual legacy */
    .legacy-nav {
        flex: 1;
        overflow-y: auto;
        padding: 0px; /* Pegado arriba */
    }
    
    .legacy-nav ul {
        list-style-type: none;
        list-style-position: outside;
        margin: 0px;
        padding: 0px;
        border-bottom: 0 none;
    }

    .legacy-nav li {
        list-style-type: none;
        margin-bottom: 0px; /* Pegados entre si */
    }

    /* Emulando el estilo clásico de botones/enlaces en el menú lateral de 2014 */
    .legacy-menu-item {
        display: block;
        padding: 5px 10px; /* Mas finos verticalmente */
        background-color: #e0e0e0; /* Gris mas oscuro */
        border: 1px solid #bcbcbc;
        border-radius: 0px; /* Cuadrados */
        color: #000000; /* Negro puro */
        font-family: "Verdana", Arial, sans-serif;
        font-size: 13px;
        font-weight: 900; /* Fuente mas gruesa */
        text-decoration: none;
        cursor: pointer;
        position: relative;
    }

    .legacy-menu-item:hover {
        background-color: #d0d0d0;
        text-decoration: none !important;
    }

    .legacy-menu-item.active {
        background-color: #cccccc;
        color: #000;
    }

    /* El triangulo verde para los menús desplegables */
    .arrow-icon {
        position: absolute;
        right: 10px;
        top: 50%;
        margin-top: -5px;
        width: 0; 
        height: 0; 
        border-top: 5px solid transparent;
        border-bottom: 5px solid transparent;
        border-left: 6px solid #86b970; /* Verde un poco mas oscuro */
        transition: transform 0.2s;
    }

    .legacy-submenu {
        background: #ffffff;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.2s ease-out;
    }

    /* Guiado por la imagen proporcionada pero con un azul mas oscuro y "muerto" */
    .legacy-submenu a {
        display: block;
        padding: 4px 10px 4px 25px;
        color: #2c4760; /* Azul mas oscuro y grisaceo (muerto) */
        font-family: inherit;
        font-size: 12px;
        text-decoration: underline;
        font-weight: 900; /* Fuente mas gruesa */
    }

    .legacy-submenu a:hover, .legacy-submenu a.active-sub {
        background: #f0f0f0;
        color: #000; /* Texto negro al pasar el mouse */
    }

    .legacy-nav::-webkit-scrollbar { width: 6px; }
    .legacy-nav::-webkit-scrollbar-track { background: transparent; }
    .legacy-nav::-webkit-scrollbar-thumb { background: #b0b0b0; border-radius: 3px; }
    .legacy-nav::-webkit-scrollbar-thumb:hover { background: #909090; }
</style>

<aside class="legacy-sidebar" id="menu_lateral">
    <nav class="legacy-nav">
        <ul>
            <!-- Inicio -->
            <li>
                <a href="{{ route('dashboard') }}" class="legacy-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Inicio
                </a>
            </li>

            <li>
                <a href="{{ route('acceso-rol.index') }}" class="legacy-menu-item {{ request()->routeIs('acceso-rol.index') ? 'active' : '' }}">
                    Simular Rol
                </a>
            </li>

            @php
                $canViewComunes = auth()->user()->hasRole('administrador', 'profesor proyecto', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER', 'coordinador') ||
                                  (auth()->user()->hasRole('estudiante') && auth()->user()->perteneceAEquipo());
            @endphp
            @if(auth()->user()->hasRole('administrador', 'coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER', 'profesor proyecto') || $canViewComunes)
            <li>
                <div class="legacy-menu-item has-submenu">
                    Gestión Académica
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    @if(auth()->user()->hasRole('administrador'))
                        <a href="{{ route('coordinaciones') }}" class="{{ request()->routeIs('coordinaciones') ? 'active-sub' : '' }}">Coordinaciones</a>
                        <a href="{{ route('lapsos-academicos') }}" class="{{ request()->routeIs('lapsos-academicos') ? 'active-sub' : '' }}">Lapsos Académicos</a>
                    @endif
                    
                    @if($canViewComunes)
                        <a href="{{ route('comunidades.index') }}" class="{{ request()->routeIs('comunidades.index') ? 'active-sub' : '' }}">Comunidades</a>
                        <a href="{{ route('grupos-proyecto.index') }}" class="{{ request()->routeIs('grupos-proyecto.index') ? 'active-sub' : '' }}">Configuración de Equipos</a>
                    @endif

                    @if(auth()->user()->hasRole('administrador'))
                        <a href="{{ route('lineas-investigacion') }}" class="{{ request()->routeIs('lineas-investigacion') ? 'active-sub' : '' }}">Líneas de Investigación</a>
                        <a href="{{ route('tipos-investigacion') }}" class="{{ request()->routeIs('tipos-investigacion') ? 'active-sub' : '' }}">Tipos de Investigación</a>
                        <a href="{{ route('metodologia-investigacion') }}" class="{{ request()->routeIs('metodologia-investigacion') ? 'active-sub' : '' }}">Metodologías de Investigación</a>
                        <a href="{{ route('tipos-publicacion') }}" class="{{ request()->routeIs('tipos-publicacion') ? 'active-sub' : '' }}">Tipos de Publicación</a>
                    @endif

                    @if(auth()->user()->hasRole('administrador', 'coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'))
                        <a href="{{ route('componentes.index') }}" class="{{ request()->routeIs('componentes.index') ? 'active-sub' : '' }}">Componentes</a>
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
                    <a href="{{ route('proyectos.buscar') }}" class="{{ request()->routeIs('proyectos.buscar') ? 'active-sub' : '' }}">Explorar Proyectos</a>
                    @php
                        $canRegisterProject = auth()->user()->puedeRegistrarProyecto();
                    @endphp
                    @if($canRegisterProject)
                        <a href="{{ route('proyectos.crear') }}" class="{{ request()->routeIs('proyectos.crear') ? 'active-sub' : '' }}">Registrar Proyecto</a>
                    @endif
                    @if(auth()->user()->hasRole('administrador', 'coordinador', 'profesor proyecto'))
                        <a href="{{ route('validaciones.index') }}" class="{{ request()->routeIs('validaciones.index') ? 'active-sub' : '' }}">Validaciones</a>
                    @endif
                </div>
            </li>

            @if(auth()->user()->hasRole('administrador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'))
            <li>
                <div class="legacy-menu-item has-submenu">
                    Configuración Sistema
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('profesores-proyecto.index') }}" class="{{ request()->routeIs('profesores-proyecto.index') ? 'active-sub' : '' }}">Profesores de Proyecto</a>
                    @if(auth()->user()->hasRole('administrador'))
                        <a href="{{ route('coordinadores.index') }}" class="{{ request()->routeIs('coordinadores.index') ? 'active-sub' : '' }}">Coordinadores</a>
                        <a href="{{ route('auditoria') }}" class="{{ request()->routeIs('auditoria') ? 'active-sub' : '' }}">Auditoría</a>
                    @endif
                </div>
            </li>
            @endif

            <li>
                <div class="legacy-menu-item has-submenu">
                    Configuración Personal
                    <div class="arrow-icon"></div>
                </div>
                <div class="legacy-submenu">
                    <a href="{{ route('configuracion') }}" class="{{ request()->routeIs('configuracion') ? 'active-sub' : '' }}">Mi Perfil</a>
                </div>
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}" style="margin:0; padding:0;">
                    @csrf
                    <button type="submit" class="legacy-menu-item" style="width: 100%; border: 1px solid #dcdcdc; text-align: left;">
                        Cerrar Sesión
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</aside>

<script>
    function initSidebarAccordion() {
        const headers = document.querySelectorAll('.has-submenu');
        
        headers.forEach(header => {
            const newHeader = header.cloneNode(true);
            header.parentNode.replaceChild(newHeader, header);
            
            newHeader.addEventListener('click', function() {
                const body = this.nextElementSibling;
                const arrow = this.querySelector('.arrow-icon');
                
                if (body.style.maxHeight && body.style.maxHeight !== '0px') {
                    body.style.maxHeight = '0px';
                    arrow.style.transform = 'rotate(0deg)';
                } else {
                    document.querySelectorAll('.legacy-submenu').forEach(b => {
                        b.style.maxHeight = '0px';
                        if (b.previousElementSibling && b.previousElementSibling.querySelector('.arrow-icon')) {
                            b.previousElementSibling.querySelector('.arrow-icon').style.transform = 'rotate(0deg)';
                        }
                    });
                    
                    body.style.maxHeight = body.scrollHeight + 'px';
                    arrow.style.transform = 'rotate(90deg)';
                }
            });
        });
        
        // Auto-open active menu
        const activeLink = document.querySelector('.active-sub');
        if (activeLink) {
            const body = activeLink.closest('.legacy-submenu');
            if (body) {
                const header = body.previousElementSibling;
                if (header && header.classList.contains('has-submenu')) {
                    const arrow = header.querySelector('.arrow-icon');
                    body.style.maxHeight = body.scrollHeight + 'px';
                    if (arrow) arrow.style.transform = 'rotate(90deg)';
                }
            }
        }
    }

    // Usar DOMContentLoaded y también livewire events
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarAccordion);
    } else {
        initSidebarAccordion();
    }
    document.addEventListener('livewire:navigated', initSidebarAccordion);
</script>

