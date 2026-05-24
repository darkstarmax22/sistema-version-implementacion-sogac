<?php

use App\Models\Coordinacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $nombre = '';
    public $descripcion = '';
    public $search = '';
    public $editingCoordinacionId = null;
    public $viewMode = 'list'; // 'list' o 'form'

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre de la Coordinación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingCoordinacionId = $id;
        $coordinacion = Coordinacion::find($id);
        $this->nombre = $coordinacion->nombre;
        $this->descripcion = $coordinacion->descripcion;
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->nombre = '';
        $this->descripcion = '';
        $this->editingCoordinacionId = null;
    }

    public function save()
    {
        $this->validate();

        Coordinacion::updateOrCreate(
            ['id' => $this->editingCoordinacionId],
            [
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
            ]
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingCoordinacionId ? 'Coordinación actualizado con éxito.' : 'Coordinación creado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $coordinacion = Coordinacion::find($id);
        $coordinacion->update(['activo' => !$coordinacion->activo]);
        
        session()->flash('message', $coordinacion->activo ? 'Coordinación habilitado correctamente.' : 'Coordinación deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Coordinacion::find($id)->delete();
        session()->flash('message', 'Coordinación eliminado permanentemente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'coordinaciones' => Coordinacion::where('nombre', 'like', '%' . $this->search . '%')
                        ->latest()
                        ->paginate(10)
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Coordinaciones</h2>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <!-- Header Actions -->
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Coordinación:</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>
            
            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                Nueva Coordinación
            </button>
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Coordinaciones</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5">Nombre de la Coordinación</th>
                        <th padding="5">Descripción</th>
                        <th padding="5" width="80">Estado</th>
                        <th padding="5" width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($coordinaciones as $coordinacion)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$coordinacion->activo ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold;">{{ $coordinacion->nombre }}</td>
                            <td style="padding: 5px;">
                                {{ $coordinacion->descripcion ?: 'Sin descripción registrada' }}
                            </td>
                            <td align="center">
                                @if($coordinacion->activo)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center">
                                <a href="#" wire:click.prevent="edit({{ $coordinacion->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-right: 5px;">
                                    [Editar]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="toggleStatus({{ $coordinacion->id }})" title="{{ $coordinacion->activo ? 'Deshabilitar' : 'Habilitar' }}" style="color: #0000EE; text-decoration: none; margin-right: 5px;">
                                    [{{ $coordinacion->activo ? 'Deshabilitar' : 'Habilitar' }}]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="delete({{ $coordinacion->id }})" wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE esta Coordinación?" title="Eliminar" style="color: #FF0000; text-decoration: none;">
                                    [Eliminar]
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($coordinaciones->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No se encontraron resultados
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                {{ $coordinaciones->links() }}
            </div>
        </fieldset>

    @else
        <!-- Formulario (Nueva Página) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingCoordinacionId ? 'Actualizar Registro' : 'Nuevo Registro Coordinación' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="30%"><b>Nombre Oficial:</b></td>
                        <td width="70%">
                            <input wire:model="nombre" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('nombre') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Descripción del Trayecto:</b></td>
                        <td valign="top">
                            <textarea wire:model="descripcion" rows="4" style="width: 90%;"></textarea>
                            <span class="obligatorio">*</span>
                            @error('descripcion') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 15px; font-size: 13px;">
                    Los campos con <span class="obligatorio">*</span> son obligatorios
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">Guardar</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
