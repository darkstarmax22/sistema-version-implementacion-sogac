<?php

use App\Models\Comunidad;
use App\Services\GrupoProyectoService;
use App\Services\IntranetEquipoSeccionService;
use App\Services\IntranetProfessorService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public string $viewMode = 'list';

    public string $filterLapso = '';

    public string $filterPrograma = '';

    public string $filterSeccion = '';

    public string $filterTrayecto = '';

    public string $filterEquipo = '';

    public Collection $lapsos;

    public Collection $programas;

    public Collection $secciones;

    public Collection $trayectos;

    public Collection $comunidades;

    public ?int $editingGrpCodigo = null;

    public string $nombreGrupo = '';

    public string $comunidadId = '';

    public string $selectedCedula = '';

    public string $selectedRolId = '2';

    /** @var list<array{cedula: string, nombre: string, apellido: string, rol_id: int, rol_name: string}> */
    public array $miembrosSeleccionados = [];

    public function mount(IntranetProfessorService $profesores): void
    {
        $this->lapsos = $profesores->lapsosActivos();
        $this->comunidades = Comunidad::query()->orderBy('nombre')->get();

        $lap = $profesores->lapsoVigenteCodigo();
        if ($lap) {
            $this->filterLapso = (string) $lap;
        }

        $this->loadProgramas();
        $this->loadSecciones();
        $this->loadTrayectos();
    }

    public function crearGrupo(): void
    {
        $this->resetFormulario();
        $this->viewMode = 'form';
    }

    public function editarGrupo(int $grpCodigo, GrupoProyectoService $grupos): void
    {
        $g = $grupos->obtener($grpCodigo);
        if (!$g) {
            session()->flash('message_error', 'Grupo no encontrado.');

            return;
        }

        $this->editingGrpCodigo = $grpCodigo;
        $this->nombreGrupo = $g->nombre;
        $this->filterLapso = (string) $g->lap_codigo;
        $this->filterPrograma = $g->pro_codigo ? (string) $g->pro_codigo : '';
        $this->filterSeccion = (string) $g->sec_codigo;
        $this->comunidadId = $g->com_codigo ? (string) $g->com_codigo : '';
        $this->miembrosSeleccionados = array_map(
            fn($m) => [
                'cedula' => $m['cedula'],
                'nombre' => $m['nombre'] ?? '',
                'apellido' => $m['apellido'] ?? '',
                'rol_id' => (int) ($m['rol_id'] ?? 2),
                'rol_name' => match ((int) ($m['rol_id'] ?? 2)) {
                    1 => 'Líder',
                    2 => 'Autor',
                    default => 'Integrante',
                },
            ],
            $g->miembros,
        );
        $this->viewMode = 'form';
    }

    public function agregarIntegrante(): void
    {
        if ($this->selectedCedula === '') {
            session()->flash('message_error', 'Seleccione un estudiante de la sección.');

            return;
        }

        foreach ($this->miembrosSeleccionados as $m) {
            if ($m['cedula'] === $this->selectedCedula) {
                session()->flash('message_error', 'Ese estudiante ya está en el grupo.');

                return;
            }
        }

        $candidatos = $this->candidatosActuales();
        $est = $candidatos->firstWhere('cedula', $this->selectedCedula);
        if (!$est) {
            session()->flash('message_error', 'El estudiante no está inscrito en la sección elegida.');

            return;
        }

        $rolId = (int) $this->selectedRolId;
        if ($rolId === 1) {
            foreach ($this->miembrosSeleccionados as $m) {
                if ((int) $m['rol_id'] === 1) {
                    session()->flash('message_error', 'Solo puede haber un líder en el grupo.');

                    return;
                }
            }
        }

        $this->miembrosSeleccionados[] = [
            'cedula' => $this->selectedCedula,
            'nombre' => $est->nombre,
            'apellido' => $est->apellido,
            'rol_id' => $rolId,
            'rol_name' => $rolId === 1 ? 'Líder' : 'Autor',
        ];
        $this->selectedCedula = '';
    }

    public function quitarIntegrante(string $cedula): void
    {
        $this->miembrosSeleccionados = array_values(array_filter($this->miembrosSeleccionados, fn($m) => $m['cedula'] !== $cedula));
    }

    public function registrarGrupo(GrupoProyectoService $grupos): void
    {
        $this->validate(
            [
                'nombreGrupo' => 'required|min:2|max:120',
                'filterLapso' => 'required',
                'filterSeccion' => 'required',
            ],
            [
                'nombreGrupo.required' => 'Indique un nombre para el equipo/grupo.',
                'filterLapso.required' => 'Seleccione el lapso.',
                'filterSeccion.required' => 'Seleccione la sección del PNF.',
            ],
        );

        if (!$grupos->tablaDisponible()) {
            session()->flash('message_error', 'Ejecute la migración grupo_proyecto_modulo en repositorio (solo módulo).');

            return;
        }

        $user = auth()->user();
        $clave = $grupos->registrar($this->nombreGrupo, (int) $this->filterLapso, (int) $this->filterSeccion, $this->filterPrograma !== '' ? (int) $this->filterPrograma : null, $this->comunidadId !== '' ? (int) $this->comunidadId : null, $this->miembrosSeleccionados, trim((string) $user->usu_cedula), $this->editingGrpCodigo, $this->etiquetasContextoFormulario(app(IntranetEquipoSeccionService::class)));

        if (!$clave) {
            session()->flash('message_error', 'Debe incluir al menos un integrante y un líder.');

            return;
        }

        session()->flash('message', 'Grupo registrado. Clave: ' . $clave);
        $this->viewMode = 'list';
        $this->resetFormulario();
    }

    public function eliminarGrupo(int $grpCodigo, GrupoProyectoService $grupos): void
    {
        $grupos->eliminar($grpCodigo);
        session()->flash('message', 'Grupo eliminado.');
    }

    public function volver(): void
    {
        $this->viewMode = 'list';
        $this->resetFormulario();
    }

    protected function resetFormulario(): void
    {
        $this->editingGrpCodigo = null;
        $this->nombreGrupo = '';
        $this->comunidadId = '';
        $this->miembrosSeleccionados = [];
        $this->selectedCedula = '';
    }

    public function updatedFilterLapso(): void
    {
        $this->filterPrograma = '';
        $this->filterSeccion = '';
        $this->filterTrayecto = '';
        $this->loadProgramas();
        $this->loadSecciones();
        $this->loadTrayectos();
    }

    public function updatedFilterPrograma(): void
    {
        $this->filterSeccion = '';
        $this->filterTrayecto = '';
        $this->loadSecciones();
        $this->loadTrayectos();
    }

    protected function loadProgramas(): void
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $this->programas = app(IntranetEquipoSeccionService::class)->programasEnLapso($lapCodigo);
    }

    protected function loadSecciones(): void
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $programaCodigo = $this->filterPrograma !== '' ? (int) $this->filterPrograma : null;
        $this->secciones = app(IntranetEquipoSeccionService::class)->seccionesEnLapso($lapCodigo, $programaCodigo);
    }

    protected function loadTrayectos(): void
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $programaCodigo = $this->filterPrograma !== '' ? (int) $this->filterPrograma : null;
        $this->trayectos = app(IntranetEquipoSeccionService::class)->trayectosEnLapso($lapCodigo, $programaCodigo);
    }

    protected function candidatosActuales()
    {
        if ($this->filterLapso === '' || $this->filterSeccion === '') {
            return collect();
        }

        return app(GrupoProyectoService::class)->candidatosSeccion((int) $this->filterLapso, (int) $this->filterSeccion);
    }

    /**
     * @return array{lap_nombre: string, sec_nombre: string, pro_siglas: string, pro_nombre: string}
     */
    protected function etiquetasContextoFormulario(IntranetEquipoSeccionService $equipos): array
    {
        if ($this->filterLapso === '' || $this->filterSeccion === '') {
            return ['lap_nombre' => '', 'sec_nombre' => '', 'pro_siglas' => '', 'pro_nombre' => ''];
        }

        return $equipos->etiquetasContexto((int) $this->filterLapso, (int) $this->filterSeccion, $this->filterPrograma !== '' ? (int) $this->filterPrograma : null);
    }

    public function with(GrupoProyectoService $grupos, IntranetEquipoSeccionService $equipos, IntranetProfessorService $profesores)
    {
        $lapCodigo = $this->filterLapso !== '' ? (int) $this->filterLapso : null;
        $programaCodigo = $this->filterPrograma !== '' ? (int) $this->filterPrograma : null;
        $seccionCodigo = $this->filterSeccion !== '' ? (int) $this->filterSeccion : null;
        $trayectoCodigo = $this->filterTrayecto !== '' ? (int) $this->filterTrayecto : null;

        $lista = $grupos->tablaDisponible()
            ? $grupos->listar([
                'lapso' => $lapCodigo,
                'programa' => $programaCodigo,
                'seccion' => $seccionCodigo,
                'trayecto' => $trayectoCodigo,
                'equipo' => $this->filterEquipo !== '' ? $this->filterEquipo : null,
                'busqueda' => $this->search,
            ])
            : collect();

        $perPage = 10;
        $page = $this->getPage();
        $items = $lista->slice(($page - 1) * $perPage, $perPage)->values();
        $paginados = new \Illuminate\Pagination\LengthAwarePaginator($items, $lista->count(), $perPage, $page, ['path' => request()->url(), 'query' => request()->query()]);

        return [
            'gruposList' => $paginados,
            'lapsos' => $this->lapsos,
            'programas' => $this->programas,
            'secciones' => $this->secciones,
            'trayectos' => $this->trayectos,
            'candidatos' => $this->viewMode === 'form' ? $this->candidatosActuales() : collect(),
            'comunidades' => $this->comunidades,
            'tablaLista' => $grupos->tablaDisponible(),
        ];
    }
};
?>

<style>
    .grp-btn {
        border: 1px solid #777;
        background: #fff;
        color: #222;
        padding: 0.65rem 1rem;
        border-radius: 0.45rem;
        font-size: 0.92rem;
        cursor: pointer;
        transition: all 0.18s ease;
        min-width: 120px;
    }

    .grp-btn:hover {
        background: #f3f3f3;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    .grp-btn-primary {
        background: #1f2937;
        color: #fff;
        border-color: #1f2937;
    }

    .grp-btn-primary:hover {
        background: #111827;
    }

    .grp-btn-secondary {
        background: #fafafa;
        color: #1f2937;
        border-color: #d1d5db;
    }

    .grp-btn-danger {
        background: #fee2e2;
        color: #991b1b;
        border-color: #fca5a5;
    }

    .grp-btn-small {
        font-size: 0.82rem;
        padding: 0.45rem 0.75rem;
        min-width: auto;
    }
</style>

<div>
    <h2 class="titulo" style="margin-bottom: 10px; font-weight: bolder;">Equipos de proyecto</h2>

    <p style="font-size: 11px; color: #444; margin-bottom: 12px;">
        Registre el <strong>grupo de proyecto</strong> eligiendo estudiantes de la <strong>sección del PNF</strong>.
        Queda identificado con la clave <code>EQGRP:…</code> para usarlo al registrar el expediente.
    </p>

    @if (session()->has('message'))
        <div style="background: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; font-size: 12px;">
            {{ session('message') }}</div>
    @endif
    @if (session()->has('message_error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; font-size: 12px;">
            {{ session('message_error') }}</div>
    @endif

    @if (!$tablaLista)
        <div style="background: #fff3cd; padding: 10px; font-size: 11px; margin-bottom: 12px;">
            Falta la tabla <code>grupo_proyecto_modulo</code> en MySQL repositorio (solo del módulo, no es intranet).
            Ejecute:
            <code>php artisan migrate
                --path=database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php</code>
        </div>
    @endif

    @if ($viewMode === 'list')
        <div style="margin-bottom: 10px; display: flex; gap: 8px; flex-wrap: wrap; font-size: 11px;">
            <select wire:model="filterLapso">
                <option value="">Lapso</option>
                @foreach ($lapsos as $l)
                    <option value="{{ $l->lap_codigo }}">{{ $l->lap_nombre }}</option>
                @endforeach
            </select>
            <select wire:model="filterPrograma" @if (!$filterLapso) disabled @endif>
                <option value="">PNF / Programa</option>
                @foreach ($programas as $p)
                    <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>
                @endforeach
            </select>
            <select wire:model="filterSeccion" @if (!$filterLapso) disabled @endif>
                <option value="">Sección</option>
                @foreach ($secciones as $s)
                    <option value="{{ $s->sec_codigo }}">{{ $s->sec_nombre }}</option>
                @endforeach
            </select>
            <select wire:model="filterTrayecto" @if (!$filterLapso || !$filterPrograma) disabled @endif>
                <option value="">Trayecto</option>
                @foreach ($trayectos as $t)
                    <option value="{{ $t->tra_codigo }}">{{ $t->tra_nombre }}</option>
                @endforeach
            </select>
            <input wire:model="filterEquipo" type="text" placeholder="Cédula integrante…" style="width: 160px;">
            <input wire:model.debounce.300ms="search" type="text" placeholder="Buscar nombre…" style="width: 160px;">
            <button type="button" class="grp-btn grp-btn-primary" wire:click="crearGrupo">Registrar nuevo
                grupo</button>
        </div>

        <fieldset style="border: 2px solid #8b0000; padding: 8px;">
            <legend style="font-weight: bold;">Grupos de proyecto registrados</legend>
            <table width="100%" border="1" cellpadding="4" style="font-size: 11px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #8bb2b7;">
                        <th>Nombre</th>
                        <th>PNF/Sec.</th>
                        <th>Integrantes</th>
                        <th>Clave</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gruposList as $g)
                        <tr>
                            <td><b>{{ $g->nombre }}</b></td>
                            <td>
                                <b>{{ $g->pro_siglas ?: ($g->pro_nombre ?: 'PNF') }}</b>
                                · {{ $g->sec_nombre ?: 'Sección ' . $g->sec_codigo }}
                                @if (!empty($g->lap_nombre))
                                    <span style="color:#666;font-size:9px;">({{ $g->lap_nombre }})</span>
                                @endif
                            </td>
                            <td align="center">{{ $g->integrantes }}</td>
                            <td><code style="font-size:9px;">{{ $g->clave }}</code></td>
                            <td align="center" nowrap>
                                <button type="button" class="grp-btn grp-btn-secondary grp-btn-small"
                                    wire:click="editarGrupo({{ $g->grp_codigo }})">Editar</button>
                                <button type="button" class="grp-btn grp-btn-danger grp-btn-small"
                                    wire:click="eliminarGrupo({{ $g->grp_codigo }})"
                                    wire:confirm="¿Eliminar este grupo?">Eliminar</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" align="center">No hay grupos registrados. Cree uno con integrantes de la
                                sección.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $gruposList->links() }}
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; padding: 10px;">
            <legend style="font-weight: bold;">{{ $editingGrpCodigo ? 'Editar grupo' : 'Nuevo grupo de proyecto' }}
            </legend>
            <table width="100%" style="font-size: 11px;">
                <tr>
                    <td width="50%"><b>Nombre del equipo:</b><br><input wire:model="nombreGrupo" type="text"
                            style="width:90%"></td>
                    <td><b>Comunidad (opcional):</b><br>
                        <select wire:model="comunidadId" style="width:90%">
                            <option value="">—</option>
                            @foreach ($comunidades as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-top:8px;">
                        <b>Contexto académico:</b>
                        <select wire:model.live="filterLapso" style="margin-left:4px;">
                            <option value="">Lapso</option>
                            @foreach ($lapsos as $l)
                                <option value="{{ $l->lap_codigo }}">{{ $l->lap_nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterPrograma" style="margin-left:4px;"
                            @if (!$filterLapso) disabled @endif>
                            <option value="">PNF</option>
                            @foreach ($programas as $p)
                                <option value="{{ $p->pro_codigo }}">{{ $p->pro_siglas }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterSeccion" style="margin-left:4px;"
                            @if (!$filterLapso) disabled @endif>
                            <option value="">Sección</option>
                            @foreach ($secciones as $s)
                                <option value="{{ $s->sec_codigo }}">{{ $s->sec_nombre }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            </table>

            @if ($filterSeccion !== '')
                <div style="margin-top: 12px; padding: 8px; background: #f5f5f5; border: 1px solid #ccc;">
                    <b>Agregar integrante (de la sección):</b><br>
                    <select wire:model="selectedCedula" style="width: 55%; margin-top: 4px;">
                        <option value="">Estudiante inscrito…</option>
                        @foreach ($candidatos as $c)
                            <option value="{{ $c->cedula }}">{{ $c->apellido }}, {{ $c->nombre }}
                                ({{ $c->cedula }})
                            </option>
                        @endforeach
                    </select>
                    <select wire:model="selectedRolId" style="width: 20%;">
                        <option value="1">Líder</option>
                        <option value="2">Autor</option>
                    </select>
                    <button type="button" class="grp-btn grp-btn-secondary grp-btn-small"
                        wire:click="agregarIntegrante">Agregar</button>
                </div>

                <table width="100%" border="1" cellpadding="4"
                    style="font-size: 11px; margin-top: 10px; border-collapse: collapse;">
                    <thead>
                        <tr style="background:#ddd;">
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Rol</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($miembrosSeleccionados as $m)
                            <tr>
                                <td>{{ $m['cedula'] }}</td>
                                <td>{{ $m['apellido'] }}, {{ $m['nombre'] }}</td>
                                <td>{{ $m['rol_name'] }}</td>
                                <td><a href="#"
                                        wire:click.prevent="quitarIntegrante({{ json_encode($m['cedula']) }})">Quitar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" align="center">Agregue al menos un líder y los autores del grupo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <p style="font-size: 11px; color: #856404;">Seleccione lapso y sección para ver estudiantes candidatos.
                </p>
            @endif

            <div style="margin-top: 14px;">
                <button type="button" class="grp-btn grp-btn-primary" wire:click="registrarGrupo">Registrar
                    grupo</button>
                <button type="button" class="grp-btn grp-btn-secondary" wire:click="volver">Cancelar</button>
            </div>
            <p style="font-size: 10px; color: #555; margin-top: 8px;">El registro del expediente del proyecto es un
                paso aparte; luego elija este grupo al crear el expediente.</p>
        </fieldset>
    @endif
</div>
