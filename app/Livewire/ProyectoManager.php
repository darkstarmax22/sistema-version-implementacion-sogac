<?php

namespace App\Livewire;

use App\Models\Proyecto;
use App\Services\IntranetEquipoSeccionService;
use App\Services\ProyectoGestionService;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ProyectoManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $listTab = 'gestion';

    public string $titulo = '';

    public string $resumen = '';

    public string $fecha_subida = '';

    public bool $asignacion_ct = false;

    public string $calificacion = '';

    public string $fecha_aprobacion = '';

    public string $linea_investigacion_id = '';

    public string $metodologia_id = '';

    public string $tipo_publicacion_id = '';

    public string $tipo_investigacion_id = '';

    public string $comunidad_id = '';

    public string $equipo_seccion_clave = '';

    public string $filterLapsoEquipo = '';

    public string $filterProgramaEquipo = '';

    public string $filterSeccionEquipo = '';

    public string $filterEstadoList = '';

    public string $filterComunidadList = '';

    public array $archivos_componentes = [];

    public array $archivos_actuales = [];

    public $archivo_proyecto = null;

    public string $archivo_actual = '';

    public string $search = '';

    public string $motivo_rechazo = '';

    public ?int $editingId = null;

    public ?int $selectedProjectId = null;

    public ?Proyecto $selectedProject = null;

    public string $viewMode = 'list';

    public function mount(ProyectoGestionService $gestion): void
    {
        $tab = request()->query('tab', 'gestion');
        if (in_array($tab, ['gestion', 'validar'], true)) {
            $this->listTab = $tab;
        }

        if (request()->boolean('registrar')) {
            $this->listTab = 'gestion';
            $this->iniciarRegistro();
        }
    }

    public function iniciarRegistro(): void
    {
        $gestion = app(ProyectoGestionService::class);
        $user = auth()->user();

        // Eliminamos la validación inicial para que siempre abra el formulario al hacer clic.
        // Las validaciones de registro se realizarán al intentar guardar el formulario.

        $this->listTab = 'gestion';
        $this->resetFormulario();
        $this->fecha_subida = now()->format('Y-m-d');

        if ($gestion->usuarioEsAdminEnSistema($user)) {
            $lapso = \App\Models\LapsoAcademico::vigente();
            if ($lapso) {
                $this->filterLapsoEquipo = (string) $lapso->lap_codigo;
            }
        }

        $this->viewMode = 'form';
        $this->aplicarSyncEquipo($gestion);
    }

    public function irAListado(string $tab = 'gestion'): void
    {
        $this->listTab = in_array($tab, ['gestion', 'validar'], true) ? $tab : 'gestion';
        $this->viewMode = 'list';
        $this->selectedProject = null;
        $this->selectedProjectId = null;
        $this->motivo_rechazo = '';
        $this->resetPage();
    }

    public function updatingListTab(): void
    {
        $this->resetPage();
    }

    protected function messages(): array
    {
        return [
            'titulo.required' => 'El titulo del proyecto es obligatorio.',
            'titulo.min' => 'El titulo debe tener al menos 5 caracteres.',
            'resumen.required' => 'El resumen es obligatorio.',
            'resumen.min' => 'El resumen debe tener al menos 10 caracteres.',
            'fecha_subida.required' => 'La fecha de subida es obligatoria.',
            'calificacion.required' => 'La calificacion es obligatoria.',
            'calificacion.integer' => 'La calificacion debe ser un numero entero.',
            'calificacion.min' => 'La calificacion minima es 1.',
            'calificacion.max' => 'La calificacion maxima es 20.',
            'fecha_aprobacion.required' => 'La fecha de aprobacion es obligatoria.',
            'linea_investigacion_id.required' => 'Debe seleccionar una linea de investigacion.',
            'metodologia_id.required' => 'Debe seleccionar una metodologia.',
            'tipo_publicacion_id.required' => 'Debe seleccionar un tipo de publicacion.',
            'tipo_investigacion_id.required' => 'Debe seleccionar un tipo de investigacion.',
            'lapso_academico_id.required' => 'Debe seleccionar un lapso academico.',
            'coordinacion_id.required' => 'Debe seleccionar una Coordinacion.',
            'equipo_seccion_clave.required' => 'Debe validar el equipo (seccion intranet).',
            'comunidad_id.required' => 'Debe seleccionar la comunidad del proyecto.',
            'trayecto.required' => 'El trayecto es obligatorio.',
            'motivo_rechazo.required' => 'Debe indicar el motivo de rechazo.',
            'motivo_rechazo.min' => 'El motivo debe tener al menos 10 caracteres.',
            'archivos_componentes.*.required' => 'El componente es de subida estrictamente obligatoria.',
            'archivos_componentes.*.max' => 'El archivo no debe exceder los 20MB permitidos.',
        ];
    }

    /** @deprecated use iniciarRegistro() desde la vista */
    public function create(): void
    {
        $this->iniciarRegistro();
    }

    public function updatedEquipoSeccionClave(IntranetEquipoSeccionService $equipos): void
    {
        $partes = $equipos->parsearClave($this->equipo_seccion_clave);
        if ($partes) {
            $this->filterLapsoEquipo = (string) $partes['lap_codigo'];
        }
    }

    public function updatingFilterLapsoEquipo(): void
    {
        $this->filterProgramaEquipo = '';
        $this->filterSeccionEquipo = '';
        $this->equipo_seccion_clave = '';
    }

    public function updatingFilterProgramaEquipo(): void
    {
        $this->filterSeccionEquipo = '';
        $this->equipo_seccion_clave = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterEstadoList(): void
    {
        $this->resetPage();
    }

    public function updatingFilterComunidadList(): void
    {
        $this->resetPage();
    }

    protected function aplicarSyncEquipo(ProyectoGestionService $gestion): void
    {
        if (! auth()->check() || app(ProyectoGestionService::class)->usuarioEsAdminEnSistema(auth()->user())) {
            return;
        }

        $lap = $this->filterLapsoEquipo !== '' ? (int) $this->filterLapsoEquipo : null;
        $sync = $gestion->sincronizarEquipoEstudiante(trim((string) auth()->user()->usu_cedula), $lap);

        if ($sync['equipo_seccion_clave']) {
            $this->equipo_seccion_clave = $sync['equipo_seccion_clave'];
        }
    }

    public function edit(int $id, ProyectoGestionService $gestion): void
    {
        $this->resetFormulario();
        $this->fill($gestion->cargarParaEdicion($id));
        $this->viewMode = 'form';
    }

    public function cancel(): void
    {
        $this->viewMode = 'list';
        $this->resetFormulario();
    }

    public function save(ProyectoGestionService $gestion): void
    {
        $user = auth()->user();
        $estado = $this->estadoFormulario();

        $this->validate(
            $gestion->reglasValidacion($estado, $this->archivos_actuales, $user, $this->editingId !== null),
            $this->messages()
        );

        $gestion->guardar(
            $this->editingId,
            $estado,
            $this->archivos_componentes,
            $this->archivos_actuales,
            $user,
            $this->archivo_proyecto
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Proyecto actualizado con exito.' : 'Proyecto registrado con exito.');
        $this->resetFormulario();
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->alternarEstado($id);
        session()->flash('message', 'Estado del proyecto actualizado.');
        $this->dispatch('refresh-icons');
    }

    public function delete(int $id, ProyectoGestionService $gestion): void
    {
        $gestion->eliminar($id);
        session()->flash('message', 'Proyecto eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function approve(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            session()->flash('message', 'Proyecto aprobado con exito.');
        } catch (AuthorizationException $e) {
            session()->flash('message_error', $e->getMessage());
        }
        $this->dispatch('refresh-icons');
    }

    public function openReject(int $id): void
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function openDetails(int $id, ProyectoGestionService $gestion): void
    {
        $this->selectedProject = $gestion->proyectoParaFicha($id);
        $this->viewMode = 'details';
        $this->dispatch('refresh-icons');
    }

    public function confirmReject(ProyectoGestionService $gestion): void
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10',
        ], $this->messages());

        try {
            $gestion->rechazar((int) $this->selectedProjectId, $this->motivo_rechazo);
            $this->irAListado($this->listTab);
            session()->flash('message', 'Proyecto rechazado.');
        } catch (AuthorizationException $e) {
            session()->flash('message_error', $e->getMessage());
        }
        $this->dispatch('refresh-icons');
    }

    public function approveFromDetails(int $id, ProyectoGestionService $gestion): void
    {
        try {
            $gestion->aprobar($id);
            $this->irAListado($this->listTab);
            session()->flash('message', 'Proyecto aprobado con exito.');
        } catch (AuthorizationException $e) {
            session()->flash('message_error', $e->getMessage());
        }
        $this->dispatch('refresh-icons');
    }

    public function rejectFromDetails(int $id): void
    {
        $this->openReject($id);
    }

    public function render(ProyectoGestionService $gestion)
    {
        $estado = $this->estadoFormulario();
        $page = $this->getPage();
        $user = auth()->user();

        $datos = match ($this->viewMode) {
            'list' => $gestion->datosVistaListado([
                'search' => $this->search,
                'estado' => $this->filterEstadoList,
                'comunidad' => $this->filterComunidadList,
            ], $page, $user, $this->listTab),
            'form' => $gestion->datosVistaFormulario($estado),
            default => ['comunidades' => $gestion->comunidadesOrdenadas()],
        };

        return view('livewire.proyecto-manager', array_merge($datos, [
            'viewMode' => $this->viewMode,
            'listTab' => $this->listTab,
            'editingId' => $this->editingId,
            'filterLapsoEquipo' => $this->filterLapsoEquipo,
            'archivos_actuales' => $this->archivos_actuales,
            'selectedProject' => $this->selectedProject,
            'canRegister' => $gestion->usuarioPuedeRegistrar($user),
            'esAdmin' => $gestion->usuarioEsAdminEnSistema($user),
        ]));
    }

    protected function resetFormulario(): void
    {
        $this->titulo = '';
        $this->resumen = '';
        $this->fecha_subida = '';
        $this->asignacion_ct = false;
        $this->calificacion = '';
        $this->fecha_aprobacion = '';
        $this->linea_investigacion_id = '';
        $this->metodologia_id = '';
        $this->tipo_publicacion_id = '';
        $this->tipo_investigacion_id = '';
        $this->comunidad_id = '';
        $this->equipo_seccion_clave = '';
        $this->filterLapsoEquipo = '';
        $this->filterProgramaEquipo = '';
        $this->filterSeccionEquipo = '';
        $this->archivos_componentes = [];
        $this->archivos_actuales = [];
        $this->archivo_proyecto = null;
        $this->archivo_actual = '';
        $this->editingId = null;
    }

    protected function estadoFormulario(): array
    {
        return [
            'search' => $this->search,
            'filterEstadoList' => $this->filterEstadoList,
            'filterComunidadList' => $this->filterComunidadList,
            'filterLapsoEquipo' => $this->filterLapsoEquipo,
            'filterProgramaEquipo' => $this->filterProgramaEquipo,
            'filterSeccionEquipo' => $this->filterSeccionEquipo,
            'equipo_seccion_clave' => $this->equipo_seccion_clave,
            'archivo_actual' => $this->archivo_actual,
            'titulo' => $this->titulo,
            'resumen' => $this->resumen,
            'fecha_subida' => $this->fecha_subida,
            'asignacion_ct' => $this->asignacion_ct,
            'calificacion' => $this->calificacion,
            'fecha_aprobacion' => $this->fecha_aprobacion,
            'linea_investigacion_id' => $this->linea_investigacion_id,
            'metodologia_id' => $this->metodologia_id,
            'tipo_publicacion_id' => $this->tipo_publicacion_id,
            'tipo_investigacion_id' => $this->tipo_investigacion_id,
            'comunidad_id' => $this->comunidad_id,
        ];
    }
}
