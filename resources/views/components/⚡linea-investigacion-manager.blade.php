<?php

use App\Models\LineaInvestigacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $nombre_investigacion = '';
    public $descripcion = '';
    public $area_de_investigacion = '';
    public $coordinacion_id = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    protected $rules = [
        'nombre_investigacion' => 'required|min:3|max:255',
        'descripcion' => 'required|max:500',
        'area_de_investigacion' => 'required|max:255',
        'coordinacion_id' => 'required',
    ];

    public function messages()
    {
        return [
            'nombre_investigacion.required' => 'El nombre de la línea de investigación es obligatorio.',
            'nombre_investigacion.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre_investigacion.max' => 'El nombre no debe exceder los 255 caracteres.',
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
            'area_de_investigacion.required' => 'El área académica es obligatoria.',
            'area_de_investigacion.max' => 'El área no debe exceder los 255 caracteres.',
            'coordinacion_id.required' => 'Seleccionar un Programa / Coordinación es obligatorio.',
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
        $item = LineaInvestigacion::find($id);
        $this->nombre_investigacion = $item->nombre_investigacion;
        $this->descripcion = $item->descripcion;
        $this->area_de_investigacion = $item->area_de_investigacion;
        $this->coordinacion_id = $item->coordinacion_id;
        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->nombre_investigacion = '';
        $this->descripcion = '';
        $this->area_de_investigacion = '';
        $this->coordinacion_id = '';
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        LineaInvestigacion::guardar(
            [
                'nombre_investigacion' => $this->nombre_investigacion,
                'descripcion' => $this->descripcion,
                'area_de_investigacion' => $this->area_de_investigacion,
                'coordinacion_id' => $this->coordinacion_id,
            ],
            $this->editingId,
        );

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Línea de Investigación actualizada con éxito.' : 'Línea de Investigación registrada con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = LineaInvestigacion::findOrFail($id);
        $item->alternarEstado();

        session()->flash('message', $item->activo ? 'Línea habilitada correctamente.' : 'Línea deshabilitada correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        LineaInvestigacion::find($id)->delete();
        session()->flash('message', 'Línea de Investigación eliminada correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => LineaInvestigacion::where('nombre_investigacion', 'like', '%' . $this->search . '%')
                ->orWhere('area_de_investigacion', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10),
            'programas' => app(\App\Services\AcademicCatalog::class)->programasForSelect(),
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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Líneas de
        Investigación</h2>

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
                <b>Buscar Línea:</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>

            <button wire:click="create" class="cm-btn cm-btn-success cm-btn-sm" style="min-width: 170px;">
                Registrar Línea
            </button>
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Líneas de
                Investigación</legend>

            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5">Línea de Investigación</th>
                        <th padding="5">Área / Coordinación</th>
                        <th padding="5" width="80">Estado</th>
                        <th padding="5" width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($items as $item)
                        <tr
                            style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->activo ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold;">
                                {{ $item->nombre_investigacion }}
                                <br>
                                <span
                                    style="font-size: 10px; font-weight: normal;">{{ Str::limit($item->descripcion, 50) ?: 'Sin descripción' }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $item->area_de_investigacion }}
                                <br>
                                <span style="font-size: 10px; font-weight: bold;">Coordinación:
                                    {{ $item->nombre_coordinacion }}</span>
                            </td>
                            <td align="center">
                                @if ($item->activo)
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
                                        title="{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">{{ $item->activo ? 'Deshabilitar' : 'Habilitar' }}</button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE esta línea de investigación?"
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
                {{ $editingId ? 'Editar Línea' : 'Nueva Línea' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="30%"><b>Nombre Línea de Inv.:</b></td>
                        <td width="70%">
                            <input wire:model="nombre_investigacion" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('nombre_investigacion')
                                <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"><b>Área Académica:</b></td>
                        <td width="70%">
                            <input wire:model="area_de_investigacion" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('area_de_investigacion')
                                <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"><b>Seleccionar Programa:</b></td>
                        <td width="70%">
                            <select wire:model="coordinacion_id" style="width: 90%; padding: 2px;">
                                <option value="">Seleccione un Programa...</option>
                                @foreach ($programas as $p)
                                    <option value="{{ $p->id }}">{{ $p->siglas }} - {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="obligatorio">*</span>
                            @error('coordinacion_id')
                                <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" valign="top"><b>Descripción Breve:</b></td>
                        <td width="70%">
                            <textarea wire:model="descripcion" rows="3" style="width: 90%;"></textarea>
                            <span class="obligatorio">*</span>
                            @error('descripcion')
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
