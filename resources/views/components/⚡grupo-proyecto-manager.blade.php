<?php

use App\Models\Comunidad;
use App\Models\Equipo;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $viewMode = 'list';
    
    public $selectedEquipoId = null;
    public $nombreEquipoInput = '';
    public $selectedComunidadEquipoId = ''; 

    public $selectedStudentId = '';
    public $selectedProjectRoleId = '';
    public $estudiantesSeleccionados = []; // list of ['persona_id', 'nombre_completo', 'role_id', 'role_name']

    public function updatingSearch() { $this->resetPage(); }

    public function createTeam()
    {
        $this->resetValidation();
        $this->selectedEquipoId = null;
        $this->nombreEquipoInput = '';
        $this->selectedComunidadEquipoId = '';
        $this->estudiantesSeleccionados = [];
        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function manage($id)
    {
        $this->resetValidation();
        $equipo = Equipo::with('estudiantes')->findOrFail($id);
        
        $this->selectedEquipoId = $equipo->id;
        $this->nombreEquipoInput = $equipo->nombre;
        $this->selectedComunidadEquipoId = $equipo->comunidad_id ?? '';
        
        $this->estudiantesSeleccionados = $equipo->estudiantes->map(function($e) {
            $rol = Role::find($e->pivot->role_id);
            return [
                'persona_id' => $e->id,
                'nombre_completo' => $e->nombre . ' ' . $e->apellido,
                'role_id' => $e->pivot->role_id,
                'role_name' => $rol ? $rol->tipo_de_rol : 'Desconocido'
            ];
        })->toArray();

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function addStudentToGroup()
    {
        $this->validate([
            'selectedStudentId' => 'required',
            'selectedProjectRoleId' => 'required'
        ], [
            'selectedStudentId.required' => 'Seleccione un estudiante.',
            'selectedProjectRoleId.required' => 'Seleccione un rol de proyecto.',
        ]);

        foreach($this->estudiantesSeleccionados as $es) {
            if ($es['persona_id'] == $this->selectedStudentId) {
                session()->flash('modal_error', 'Este estudiante ya fue agregado al equipo.');
                return;
            }
        }

        $estu = User::with(['equipos' => fn($q) => $q->where('estado_logico', true)])->find($this->selectedStudentId);
        
        if ($estu && $estu->equipos) {
            $otrasComunidades = $estu->equipos->filter(function($e) {
                return $e->id != $this->selectedEquipoId;
            });
            if ($otrasComunidades->count() > 0) {
                session()->flash('modal_error', 'Acción denegada: Este estudiante ya se encuentra oficialmente activo dentro de otro equipo de proyecto.');
                return;
            }
        }

        $rol = Role::find($this->selectedProjectRoleId);

        if($estu && $rol) {
            $this->estudiantesSeleccionados[] = [
                'persona_id' => $estu->id,
                'nombre_completo' => $estu->nombre . ' ' . $estu->apellido,
                'role_id' => $rol->id,
                'role_name' => $rol->tipo_de_rol
            ];
        }
        
        $this->reset(['selectedStudentId', 'selectedProjectRoleId']);
        $this->dispatch('refresh-icons');
    }

    public function removeStudent($persona_id)
    {
        $this->estudiantesSeleccionados = array_filter($this->estudiantesSeleccionados, function($es) use ($persona_id) {
            return $es['persona_id'] != $persona_id;
        });
    }

    public function save()
    {
        $this->validate([
            'nombreEquipoInput' => 'required',
            'selectedComunidadEquipoId' => 'required'
        ], [
            'nombreEquipoInput.required' => 'Debe asignarle un nombre o identificador al Equipo de Proyecto.',
            'selectedComunidadEquipoId.required' => 'Debe seleccionar una Comunidad Abordada para el Equipo.'
        ]);

        $seccion_final = null;
        $anio_final = null;

        if (auth()->user()->hasRole('profesor proyecto')) {
            $profRoleData = auth()->user()->roles->where('id', 3)->first();
            if ($profRoleData && $profRoleData->pivot && $profRoleData->pivot->anio && $profRoleData->pivot->seccion) {
                $anio_final = $profRoleData->pivot->anio;
                $seccion_final = $profRoleData->pivot->seccion;
            }
        }

        if ($this->selectedEquipoId) {
            $equipo = Equipo::findOrFail($this->selectedEquipoId);
            $equipo->nombre = $this->nombreEquipoInput;
            $equipo->comunidad_id = empty($this->selectedComunidadEquipoId) ? null : $this->selectedComunidadEquipoId;
            $equipo->save();
        } else {
            $equipo = Equipo::create([
                'nombre' => $this->nombreEquipoInput,
                'comunidad_id' => empty($this->selectedComunidadEquipoId) ? null : $this->selectedComunidadEquipoId,
                'anio' => $anio_final,
                'seccion' => $seccion_final,
            ]);
        }

        $syncData = [];
        foreach($this->estudiantesSeleccionados as $est) {
            $syncData[$est['persona_id']] = ['role_id' => $est['role_id']];
        }
        
        $equipo->estudiantes()->sync($syncData);

        session()->flash('message', 'Formación del equipo "'. $equipo->nombre .'" guardada exitosamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $equiposQuery = Equipo::with(['estudiantes', 'comunidad'])
            ->where(function($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            });

        // Filter Equipos list by Professor context if applicable
        if (auth()->user()->hasRole('profesor proyecto')) {
            $profRoleData = auth()->user()->roles->where('id', 3)->first();
            if ($profRoleData && $profRoleData->pivot && $profRoleData->pivot->anio && $profRoleData->pivot->seccion) {
                $equiposQuery->where('anio', $profRoleData->pivot->anio)
                             ->where('seccion', $profRoleData->pivot->seccion);
            }
        }
        
        // Ensure to fetch teams for admins correctly and properly paginate
        $equiposList = $equiposQuery->latest()->paginate(10);

        // Fetch available students 
        $queryStudents = User::whereHas('roles', function($q) { 
            $q->where('nombre', 'estudiante')
              ->where('estado_logico', true); 
        })->with(['equipos' => function($q) {
            $q->where('estado_logico', true);
        }]);

        if (auth()->user()->hasRole('profesor proyecto')) {
            $profRoleData = auth()->user()->roles->where('id', 3)->first();
            if ($profRoleData && $profRoleData->pivot && $profRoleData->pivot->anio && $profRoleData->pivot->seccion) {
                $queryStudents->whereHas('roles', function($q) use ($profRoleData) {
                    $q->where('nombre', 'estudiante')
                      ->where('detalle_rol.anio', $profRoleData->pivot->anio)
                      ->where('detalle_rol.seccion', $profRoleData->pivot->seccion);
                });
            }
        }

        $comunidadesDisponiblesQuery = Comunidad::where('activa', true);
        if (auth()->user()->hasRole('profesor proyecto') && !auth()->user()->hasRole('administrador')) {
            $comunidadesDisponiblesQuery->where('profesor_id', auth()->id());
        }

        return [
            'equiposList' => $equiposList,
            'availableStudents' => $queryStudents->get(),
            'projectRoles' => Role::whereIn('tipo_de_rol', ['lider', 'autor'])->get(), 
            'comunidadesDisponibles' => $comunidadesDisponiblesQuery->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Configuración de Equipos</h2>

    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Equipo:</b>
                <input wire:model.live="search" type="text" style="width: 250px; padding: 3px;" placeholder="...">
            </div>
            @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                <button wire:click="createTeam" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; white-space: nowrap;">
                    + Crear Nuevo Equipo
                </button>
            @endif
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Explorador de Equipos de Proyecto</legend>

            <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N°</th>
                        <th width="35%">Identificador de Equipo</th>
                        <th width="20%">Integrantes Actuales</th>
                        <th width="20%">Comunidad Abordada</th>
                        <th width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($equiposList as $index => $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="left">
                                <span style="font-weight: bold; font-size: 11px;">{{ mb_strtoupper($c->nombre) }}</span><br>
                                <span style="font-size:9px; color:#555;">Sección: {{ $c->seccion ?? 'Global' }} | Año: {{ $c->anio ?? 'Global' }}</span>
                            </td>
                            <td align="center">
                                <b>{{ $c->estudiantes->count() }}</b>
                            </td>
                            <td align="center">
                                @if($c->comunidad)
                                    {{ mb_strtoupper($c->comunidad->nombre) }}
                                @else
                                    <span style="color: #999; font-style: italic;">Sin anclar</span>
                                @endif
                            </td>
                            <td align="center">
                                @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                                    <a href="#" wire:click.prevent="manage({{ $c->id }})" style="color: #0000EE; text-decoration: none; font-weight: bold;">[Modificar Equipo]</a>
                                @else
                                    <span style="color: #888;">[Lectura]</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($equiposList->isEmpty())
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay equipos registrados bajo tus criterios.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $equiposList->links() }}</div>
        </fieldset>

    @else
        <!-- FORMULARIO DE GESTION DE GRUPO -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $selectedEquipoId ? 'Modificando Equipo: ' . mb_strtoupper($nombreEquipoInput) : 'Generación de Nuevo Equipo' }}
            </legend>

            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 15px;">
                <tr>
                    <td width="50%">
                        <b>Asignar un Nombre o Número al Equipo:</b><br>
                        <input type="text" wire:model="nombreEquipoInput" style="width: 80%; padding: 4px;" placeholder="Ej: Equipo Gema, Grupo 01, Alpha...">
                        @error('nombreEquipoInput') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="50%" align="right">
                        <b>Vincular a una Comunidad Abordada:</b> <span style="color:red; font-weight:bold;">*</span><br>
                        <select wire:model="selectedComunidadEquipoId" style="width: 80%; padding: 4px;">
                            <option value="">Seleccione Comunidad...</option>
                            @foreach($comunidadesDisponibles as $cm)
                                <option value="{{ $cm->id }}">{{ mb_strtoupper($cm->nombre) }}</option>
                            @endforeach
                        </select>
                        @error('selectedComunidadEquipoId') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
            </table>

            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Vincular Estudiantes (Integrantes)</legend>
                
                @if (session()->has('modal_error'))
                    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 5px; margin-bottom: 10px; font-size: 11px;">
                        {{ session('modal_error') }}
                    </div>
                @endif

                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px; background-color: #e9ecef; border: 1px solid #CCC; padding: 5px;">
                    <tr>
                        <td width="30%"><b>Elegir Estudiante:</b><br>
                            <select wire:model="selectedStudentId" style="width: 95%;">
                                <option value="">Seleccione uno...</option>
                                @foreach($availableStudents as $as)
                                    @php 
                                        $yaPerteneceAOTRO = false;
                                        $yaPerteneceAESTE = collect($estudiantesSeleccionados)->contains('persona_id', $as->id);
                                        
                                        if (!$yaPerteneceAESTE && $as->equipos) {
                                            $otrasComunidades = $as->equipos->filter(function($e) {
                                                return $e->id != $this->selectedEquipoId;
                                            });
                                            if ($otrasComunidades->count() > 0) {
                                                $yaPerteneceAOTRO = true;
                                            }
                                        }
                                    @endphp
                                    @if(!$yaPerteneceAESTE)
                                        <option value="{{ $as->id }}" {{ $yaPerteneceAOTRO ? 'disabled' : '' }} style="{{ $yaPerteneceAOTRO ? 'color: red;' : '' }}">
                                            {{ mb_strtoupper($as->nombre . ' ' . $as->apellido) }} {{ $yaPerteneceAOTRO ? '(Ya pertenece a un equipo)' : '' }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('selectedStudentId') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="30%"><b>Rol en el Proyecto:</b><br>
                            <select wire:model="selectedProjectRoleId" style="width: 95%;">
                                <option value="">Seleccione uno...</option>
                                @foreach($projectRoles as $pr)
                                    <option value="{{ $pr->id }}">{{ ucfirst($pr->tipo_de_rol) }}</option>
                                @endforeach
                            </select>
                            @error('selectedProjectRoleId') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="40%" valign="bottom">
                            <button wire:click="addStudentToGroup" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 15px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 26px; margin-bottom: 2px;">
                                + Agregar Integrante
                            </button>
                        </td>
                    </tr>
                </table>

                <table width="100%" border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; margin-top: 10px; font-size: 11px;">
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold; text-align: center;">
                        <td width="50%">Estudiante</td>
                        <td width="30%">Rol de Proyecto</td>
                        <td width="20%">Acción</td>
                    </tr>
                    @foreach($estudiantesSeleccionados as $est)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};">
                            <td align="left" style="padding-left:10px; font-weight:bold;">{{ mb_strtoupper($est['nombre_completo']) }}</td>
                            <td align="center">
                                <span style="font-weight: bold;">{{ mb_strtoupper($est['role_name']) }}</span>
                            </td>
                            <td align="center">
                                <a href="#" wire:click.prevent="removeStudent('{{ $est['persona_id'] }}')" style="color: #FF0000; text-decoration: none;">[Quitar]</a>
                            </td>
                        </tr>
                    @endforeach
                    @if(empty($estudiantesSeleccionados))
                        <tr>
                            <td colspan="3" align="center" style="padding: 10px; background-color: #FFFFFF;">
                                No hay integrantes asignados a este equipo.
                            </td>
                        </tr>
                    @endif
                </table>
            </fieldset>

            <br>
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
                <tr>
                    <td align="center">
                        <button wire:click="save" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 20px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 30px;">
                            {{ $selectedEquipoId ? 'Guardar Cambios del Equipo' : 'Crear Equipo y Guardar' }}
                        </button>
                        <button wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 30px; margin-left: 10px;">
                            Terminar Sin Guardar
                        </button>
                    </td>
                </tr>
            </table>
        </fieldset>
    @endif
</div>
