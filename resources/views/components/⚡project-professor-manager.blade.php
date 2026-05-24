<?php

use App\Models\User;
use App\Models\Coordinacion;
use App\Models\Rol;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedYear = [];
    public $selectedSection = [];
    public $activeAdminCoordinacion = null;

    public function mount()
    {
        if (auth()->check()) {
            $currentUser = auth()->user();
            if ($currentUser->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
                $coordRole = $currentUser->roles->where('id', 2)->first();
                if($coordRole && $coordRole->pivot) {
                    $this->activeAdminCoordinacion = $coordRole->pivot->coordinacion_id;
                }
            }
        }

        // Cargar años pre-asignados a profesores de proyecto
        $professors = User::whereHas('roles', function($q) {
            $q->where('rol.id', 3);
        })->with(['roles' => function($q) {
            $q->where('rol.id', 3);
        }])->get();

        foreach($professors as $prof) {
            $pivotInfo = $prof->roles->first()->pivot;
            if($pivotInfo) {
                if($pivotInfo->anio) {
                    $this->selectedYear[$prof->id] = $pivotInfo->anio;
                }
                if($pivotInfo->seccion) {
                    $this->selectedSection[$prof->id] = $pivotInfo->seccion;
                }
            }
        }
    }

    public function toggleProjectProfessor($userId)
    {
        $user = User::find($userId);
        $projectProfessorRoleId = 3; // profesor proyecto

        if ($user->roles->contains('id', $projectProfessorRoleId)) {
            // Verificación de Jurisdicción para Revocar
            $roleData = $user->roles->where('id', $projectProfessorRoleId)->first();
            if ($roleData && $roleData->pivot->coordinacion_id && !auth()->user()->hasRole('administrador')) {
                if ($this->activeAdminCoordinacion != $roleData->pivot->coordinacion_id) {
                    session()->flash('message_error', "Acceso denegado: Este profesor corresponde a otro Coordinación y no posee jurisdicción para revocarlo.");
                    return;
                }
            }

            $user->roles()->detach($projectProfessorRoleId);
            $this->selectedYear[$userId] = '';
            $this->selectedSection[$userId] = '';
            session()->flash('message', "Rol de Profesor de Proyecto quitado a {$user->nombre}.");
        } else {
            if(empty($this->selectedYear[$userId])) {
                session()->flash('message_error', "Debe seleccionar un Año asignado para el proyecto.");
                return;
            }
            if(empty($this->selectedSection[$userId])) {
                session()->flash('message_error', "Debe seleccionar o escribir una Sección académica.");
                return;
            }

            $user->roles()->attach($projectProfessorRoleId, [
                'id_asignador' => auth()->id(),
                'anio' => $this->selectedYear[$userId],
                'seccion' => $this->selectedSection[$userId],
                'coordinacion_id' => $this->activeAdminCoordinacion,
                'estado_logico' => true
            ]);
            session()->flash('message', "Rol de Profesor de Proyecto asignado a {$user->nombre}.");
        }
        
        $this->dispatch('refresh-icons');
    }


    public function with()
    {
        // Buscamos usuarios que tengan el rol de profesor (ID 4)
        $users = User::with(['roles' => function($query) {
            $query->whereIn('rol.id', [3, 4]); // 3=ProfesorProyecto, 4=Profesor
        }])
        ->whereHas('roles', function($query) {
            $query->where('rol.id', 4); // Profesor base
        })
        ->where(function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
        })
        ->latest()
        ->paginate(10);

        return [
            'users' => $users,
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Profesores de Proyecto</h2>

    <!-- Mensajes de Estado -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('message_error'))
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message_error') }}
        </div>
    @endif

    <div style="margin-bottom: 15px; font-size: 11px;">
        Esta vista permite asignar el rol de <strong>Profesor de Proyecto</strong> a los docentes registrados en el sistema, vinculándolos con su grupo de año académico a impartir. 
        <br><br>
        @if($activeAdminCoordinacion)
            <span style="background-color: #e9ecef; padding: 4px 8px; border: 1px solid #CCC; border-radius: 3px;">
                🔑 Los docentes agregados heredarán el <b>Coordinación</b> gestionado por ti de forma automática.
            </span>
        @endif
    </div>

    <!-- Acciones de Cabecera -->
    <div style="margin-bottom: 15px;">
        <b>Búsqueda de Docente:</b>
        <input wire:model.live="search" type="text" style="width: 250px;" placeholder="Término de búsqueda...">
    </div>

    <!-- Tabla -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Profesores</legend>
        
        <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th padding="5" width="30%">Nombre del Docente</th>
                    <th padding="5" width="20%">Estado Proyecto</th>
                    <th padding="5" width="20%">Año Asignado y Coordinación</th>
                    <th padding="5" width="30%">Acciones de Asignación</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($users as $user)
                    @php
                        $isProjectProfessor = $user->roles->contains('id', 3);
                        $currentAnio = null;
                        $currentSeccion = null;
                        $currentCoordinacionId = null;
                        if($isProjectProfessor) {
                            $roleData = $user->roles->where('id', 3)->first();
                            if($roleData && $roleData->pivot){
                                $currentAnio = $roleData->pivot->anio;
                                $currentSeccion = $roleData->pivot->seccion;
                                $currentCoordinacionId = $roleData->pivot->coordinacion_id;
                            }
                        }
                    @endphp
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="middle">
                        <td align="left" style="padding: 5px;">
                            <span style="font-weight: bold;">
                                {{ mb_strtoupper($user->nombre) }} {{ mb_strtoupper($user->apellido) }}
                                @if($user->id == auth()->id())
                                    <span style="color: #0000EE; font-size: 10px;">(Tú)</span>
                                @endif
                            </span>
                            <br>
                            <span style="font-size: 10px; color: #555;">{{ strtolower($user->email) }}</span>
                        </td>
                        <td align="center" style="padding: 5px;">
                            @if($isProjectProfessor)
                                <span style="background-color: #d4edda; border: 1px solid #c3e6cb; padding: 2px 6px; font-size: 10px; font-weight: bold; color: #155724;">
                                    PROF. PROYECTO
                                </span>
                            @else
                                <span style="font-size: 10px; color: #555; background-color: #EEE; padding: 2px 6px; border: 1px solid #CCC;">
                                    SOLO DOCENTE
                                </span>
                            @endif
                        </td>
                        <td align="center" style="padding: 5px;">
                            @if(!$isProjectProfessor)
                                <select wire:model="selectedYear.{{ $user->id }}" style="width: 100%; padding: 2px; font-size: 10px; margin-bottom:4px;">
                                    <option value="">- Seleccione Año -</option>
                                    <option value="Año I">Año I</option>
                                    <option value="Año II">Año II</option>
                                    <option value="Año III">Año III</option>
                                    <option value="Año IV">Año IV</option>
                                    <option value="Año V">Año V</option>
                                </select>
                                <input wire:model="selectedSection.{{ $user->id }}" type="text" placeholder="Sección..." style="width: 100%; padding: 2px; font-size: 10px; margin-bottom:4px;">
                            @else
                                <span style="font-weight: bold; color: #8b0000; font-size:11px;">{{ mb_strtoupper($currentAnio) }} - SECC: {{ mb_strtoupper($currentSeccion) }}</span><br>
                                @if($currentCoordinacionId)
                                    @php
                                        $coordinacionObj = \App\Models\Coordinacion::find($currentCoordinacionId);
                                    @endphp
                                    <span style="font-size: 9px; font-weight: bold; color: #333; background-color: #e9ecef; padding: 1px 4px; border:1px solid #ccc;">
                                        Coordinación: {!! $coordinacionObj ? mb_strtoupper($coordinacionObj->nombre) : 'N/A' !!}
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td align="center" style="padding: 5px;">
                            @php
                                $canRevoke = true;
                                if ($isProjectProfessor && $currentCoordinacionId && !auth()->user()->hasRole('administrador')) {
                                    if ($this->activeAdminCoordinacion != $currentCoordinacionId) {
                                        $canRevoke = false;
                                    }
                                }
                            @endphp
                            
                            @if(!$isProjectProfessor || $canRevoke)
                                <button wire:click="toggleProjectProfessor({{ $user->id }})" class="boton" style="border: 1px solid {{ $isProjectProfessor ? '#FF0000' : '#0000EE' }}; border-radius: 4px; padding: 4px 10px; font-weight: bold; background-color: {{ $isProjectProfessor ? '#f8d7da' : '#cce5ff' }}; color: {{ $isProjectProfessor ? '#721c24' : '#004085' }}; cursor: pointer; font-size: 10px; width: 100px; white-space: normal; height: auto;">
                                    {{ $isProjectProfessor ? 'Quitar Rol' : 'Asignar Rol' }}
                                </button>
                            @else
                                <span style="font-size: 9px; color: #888; font-weight: bold; border: 1px dashed #ccc; padding: 4px; display: inline-block;">
                                    GESTIONADO POR<br>OTRO DEPARTAMENTO
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if($users->isEmpty())
                    <tr>
                        <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron docentes según el criterio de búsqueda.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div style="margin-top: 10px;">
            {{ $users->links() }}
        </div>
    </fieldset>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh-icons', () => {
                setTimeout(() => lucide.createIcons(), 10);
            });
        });
    </script>
</div>
