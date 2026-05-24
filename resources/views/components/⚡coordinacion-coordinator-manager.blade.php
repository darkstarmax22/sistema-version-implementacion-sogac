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
    public $selectedCoordinacion = [];

    public function mount()
    {
        // Cargar los Coordinaciones configurados previamente para los coordinadores
        $coordinators = User::whereHas('roles', function($q){
            $q->where('rol.id', 2); // 2 = coordinador
        })->with(['roles' => function($q){
            $q->where('rol.id', 2);
        }])->get();
        
        foreach($coordinators as $c) {
            $pivotInfo = $c->roles->first()->pivot;
            if($pivotInfo && $pivotInfo->coordinacion_id) {
                $this->selectedCoordinacion[$c->id] = $pivotInfo->coordinacion_id;
            }
        }
    }

    public function toggleCoordinator($userId)
    {
        $user = User::find($userId);
        $coordinatorRoleId = 2; // COORDINADOR_Coordinación_TEMP_PLACEHOLDER
        
        if ($user->roles->contains($coordinatorRoleId)) {
            $user->roles()->detach($coordinatorRoleId);
            $this->selectedCoordinacion[$userId] = '';
            session()->flash('message', "Rol de Coordinador revocado de {$user->nombre}.");
        } else {
            if(empty($this->selectedCoordinacion[$userId])) {
                session()->flash('message_error', "Debe seleccionar primero a qué Coordinación será asignado para este docente.");
                return;
            }

            $user->roles()->attach($coordinatorRoleId, [
                'coordinacion_id' => $this->selectedCoordinacion[$userId],
                'estado_logico' => true,
                'id_asignador' => auth()->id() ?? 1
            ]);
            session()->flash('message', "Rol de Coordinador asignado a {$user->nombre}.");
        }
        
        $this->dispatch('refresh-icons');
    }

    public function updateCoordinatorCoordinacion($userId)
    {
        if (empty($this->selectedCoordinacion[$userId])) {
            session()->flash('message_error', 'Debe seleccionar una Coordinación válida antes de actualizar.');
            return;
        }

        $user = User::findOrFail($userId);
        $coordinatorRoleId = 2;

        if ($user->roles->contains($coordinatorRoleId)) {
            $user->roles()->updateExistingPivot($coordinatorRoleId, [
                'coordinacion_id' => $this->selectedCoordinacion[$userId],
                'id_asignador' => auth()->id() ?? 1
            ]);
            session()->flash('message', "Coordinación actualizado correctamente para el Coordinador {$user->nombre}.");
        }
    }

    public function with()
    {
        // Buscamos usuarios que tengan el rol de profesor (ID 4)
        $users = User::with(['roles' => function($query) {
            $query->whereIn('rol.id', [2, 4]);
        }])
        ->whereHas('roles', function($query) {
            $query->where('rol.id', 4); // Profesor
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
            'coordinaciones' => Coordinacion::where('activo', true)->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Coordinadores de Coordinación</h2>

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
        Esta vista permite asignar permisos de <strong>Coordinador de Coordinación</strong> a los docentes registrados en el sistema, y definir su respectivo Coordinación a cargo.
    </div>

    <!-- Acciones de Cabecera -->
    <div style="margin-bottom: 15px;">
        <b>Búsqueda de Docente:</b>
        <input wire:model.live="search" type="text" style="width: 250px;" placeholder="Buscar por Nombre / Correo...">
    </div>

    <!-- Tabla -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Profesores Activos</legend>
        
        <table width="100%" border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th width="35%">Nombre del Docente</th>
                    <th width="20%">Estado Actual</th>
                    <th width="25%">Programa de Formación (Coordinación)</th>
                    <th width="20%">Acción</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($users as $user)
                    @php
                        $isCoordinator = $user->roles->contains('id', 2);
                        $currentCoordinacionId = null;
                        if($isCoordinator) {
                            $role = $user->roles->where('id', 2)->first();
                            if ($role && $role->pivot) {
                                $currentCoordinacionId = $role->pivot->coordinacion_id;
                            }
                        }
                    @endphp
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="middle">
                        <td align="left">
                            <span style="font-weight: bold;">{{ mb_strtoupper($user->nombre) }} {{ mb_strtoupper($user->apellido) }}</span>
                            <br>
                            <span style="font-size: 10px; color: #555;">{{ strtolower($user->email) }}</span>
                        </td>
                        <td align="center">
                            @if($isCoordinator)
                                <span style="background-color: #FFFF00; border: 1px solid #CCC; padding: 2px 6px; font-size: 10px; font-weight: bold; color: #000;">
                                    COORDINADOR ACTIVO
                                </span>
                            @else
                                <span style="font-size: 10px; color: #555; background-color: #EEE; padding: 2px 6px; border: 1px solid #CCC;">
                                    PROFESOR REGULAR
                                </span>
                            @endif
                        </td>
                        <td align="center">
                            <select wire:model="selectedCoordinacion.{{ $user->id }}" style="width: 100%; padding: 2px; font-size: 10px;">
                                <option value="">--- Seleccione Coordinación ---</option>
                                @foreach($coordinaciones as $coordinacion)
                                    <option value="{{ $coordinacion->id }}">{{ mb_strtoupper($coordinacion->nombre) }}</option>
                                @endforeach
                            </select>
                            
                            @if($isCoordinator)
                                <br>
                                <a href="#" wire:click.prevent="updateCoordinatorCoordinacion({{ $user->id }})" style="color: #0000EE; text-decoration: none; font-size: 9px; font-weight: bold; margin-top: 3px; display: inline-block;">[Cambiar Coordinación Asignado]</a>
                            @endif
                        </td>
                        <td align="center">
                            <button wire:click="toggleCoordinator({{ $user->id }})" class="boton" style="border: 1px solid {{ $isCoordinator ? '#FF0000' : '#008000' }}; border-radius: 4px; padding: 4px 10px; font-weight: bold; background-color: {{ $isCoordinator ? '#f8d7da' : '#d4edda' }}; color: {{ $isCoordinator ? '#721c24' : '#155724' }}; cursor: pointer; font-size: 10px; width: 100px; white-space: normal; height: auto;">
                                {{ $isCoordinator ? 'Quitar Rol' : 'Asignar Rol' }}
                            </button>
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
</div>
