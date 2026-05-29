<?php

use App\Models\TipoPublicacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nombre = '';
    public $mencion_honorifica = false;
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'mencion_honorifica' => 'boolean',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del tipo de publicación es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'mencion_honorifica.required' => 'El campo mención honorífica es obligatorio.',
            'mencion_honorifica.integer' => 'Formato de mención no válido.',
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
        $this->editingId = $id;
        $item = TipoPublicacion::find($id);
        $this->nombre = $item->nombre;
        $this->mencion_honorifica = $item->mencion_honorifica;
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
        $this->mencion_honorifica = false;
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        TipoPublicacion::guardar(
            [
                'nombre' => $this->nombre,
                'mencion_honorifica' => $this->mencion_honorifica,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Tipo de Publicación actualizado con éxito.' : 'Tipo de Publicación registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->alternarEstado();

        session()->flash('message', $item->estado_logico ? 'Tipo habilitado correctamente.' : 'Tipo deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        $item = TipoPublicacion::findOrFail($id);
        $item->borrar();
        session()->flash('message', 'Tipo de Publicación eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => TipoPublicacion::where('nombre', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
        ];
    }
};
?>

<div>
    <style>
        .cm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 0.55rem 0.95rem;
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            text-decoration: none;
        }

        .cm-btn:hover {
            transform: translateY(-1px);
        }

        .cm-btn-primary {
            background: #19692e;
            border-color: #154f26;
            color: #fff;
        }

        .cm-btn-success {
            background: #198754;
            border-color: #166f43;
            color: #fff;
        }

        .cm-btn-warning {
            background: #f0b606;
            border-color: #d99e00;
            color: #212529;
        }

        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }

        .cm-btn-secondary {
            background: #f4f4f4;
            border-color: #c2c2c2;
            color: #222;
        }

        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Tipos de Publicación
    </h2>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <!-- Header Actions -->
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Tipo:</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>

            <button wire:click="create" class="cm-btn cm-btn-success cm-btn-sm" style="min-width: 170px;">
                Registrar Tipo
            </button>
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Tipos de
                Publicación</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="40%">Tipo de Publicación</th>
                        <th padding="5" width="20%">Mención Honorífica</th>
                        <th padding="5" width="20%">Estado</th>
                        <th padding="5" width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($items as $item)
                        <tr
                            style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold; padding: 5px;">
                                {{ $item->nombre }}
                            </td>
                            <td align="center">
                                @if ($item->mencion_honorifica)
                                    <span style="font-weight: bold; color: #d4a017;">Sí</span>
                                @else
                                    <span style="font-style: italic; color: #888;">No aplica</span>
                                @endif
                            </td>
                            <td align="center">
                                @if ($item->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center">
                                <div
                                    style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="edit({{ $item->id }})" title="Editar"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="toggleStatus({{ $item->id }})"
                                        title="{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->estado_logico ? 'Deshabilitar' : 'Habilitar' }}</button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE este tipo de publicación?"
                                        title="Eliminar" class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($items->isEmpty())
                        <tr>
                            <td colspan="4" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No se encontraron resultados
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div style="margin-top: 10px;">
                {{ $items->links() }}
            </div>
        </fieldset>
    @else
        <!-- Formulario (Nueva Página) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Editar Tipo' : 'Nuevo Tipo' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="35%"><b>Nombre del Tipo:</b></td>
                        <td width="65%">
                            <input wire:model="nombre" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('nombre')
                                <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="35%"><b>Mención Honorífica:</b></td>
                        <td width="65%">
                            <label style="display: flex; align-items: center; gap: 5px;">
                                <input type="checkbox" wire:model="mencion_honorifica">
                                <span style="font-size: 12px;">¿Este tipo otorga mérito especial?</span>
                            </label>
                            @error('mencion_honorifica')
                                <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 15px; font-size: 13px;">
                    Los campos con <span class="obligatorio">*</span> son obligatorios
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-secondary"
                        style="margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="cm-btn cm-btn-primary">Guardar</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
