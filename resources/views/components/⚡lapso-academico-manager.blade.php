<?php

use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $nombre = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    protected $rules = [
        'nombre' => 'required|min:3|max:255',
        'fecha_inicio' => 'required|date',
        'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
    ];

    public function messages()
    {
        return [
            'nombre.required' => 'El nombre del lapso es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.max' => 'El nombre no debe exceder los 255 caracteres.',
            'fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida.',
            'fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
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
        $item = LapsoAcademico::find($id);
        $this->nombre = $item->lap_nombre;
        $this->fecha_inicio = $item->lap_fecha_inicio->format('Y-m-d');
        $this->fecha_fin = $item->lap_fecha_fin->format('Y-m-d');
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
        $this->fecha_inicio = '';
        $this->fecha_fin = '';
        $this->editingId = null;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            $item = LapsoAcademico::find($this->editingId);
            $item->update([
                'lap_nombre' => $this->nombre,
                'lap_fecha_inicio' => $this->fecha_inicio,
                'lap_fecha_fin' => $this->fecha_fin,
            ]);
        } else {
            LapsoAcademico::create([
                'lap_nombre' => $this->nombre,
                'lap_fecha_inicio' => $this->fecha_inicio,
                'lap_fecha_fin' => $this->fecha_fin,
                'lap_estatus' => 'A',
                'lap_cod_tipo_lapso' => 1,
                'lap_cod_universidad' => 1,
                'lap_condicion' => 'C',
                'lap_cerrado' => 'S',
                'lap_nota' => 'N',
            ]);
        }

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Lapso Académico actualizado con éxito.' : 'Lapso Académico registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = LapsoAcademico::find($id);
        $newEstatus = $item->lap_estatus === 'A' ? 'I' : 'A';
        $item->update(['lap_estatus' => $newEstatus]);
        
        session()->flash('message', $newEstatus === 'A' ? 'Lapso habilitado correctamente.' : 'Lapso deshabilitado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        LapsoAcademico::find($id)->delete();
        session()->flash('message', 'Lapso Académico eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        return [
            'items' => LapsoAcademico::where('lap_nombre', 'like', '%' . $this->search . '%')
                        ->orderBy('lap_codigo', 'desc')
                        ->paginate(10)
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Lapsos Académicos</h2>

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
                <b>Buscar Lapso:</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>
            
            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                Registrar Lapso
            </button>
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Lapsos Académicos</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5">Lapso Académico</th>
                        <th padding="5">Periodo</th>
                        <th padding="5" width="80">Estado</th>
                        <th padding="5" width="100">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($items as $item)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_lapso ? 'color: #888;' : 'color: #000;' }}">
                            <td align="center" style="font-weight: bold;">
                                {{ $item->nombre }}
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $item->fecha_inicio->format('d/m/Y') }} - {{ $item->fecha_fin->format('d/m/Y') }}
                                <br>
                                <span style="font-size: 10px;">Duración: {{ $item->fecha_inicio->diffInMonths($item->fecha_fin) }} Meses</span>
                            </td>
                            <td align="center">
                                @if($item->estado_lapso)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center">
                                <a href="#" wire:click.prevent="edit({{ $item->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-right: 5px;">
                                    [Editar]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="toggleStatus({{ $item->id }})" title="{{ $item->estado_lapso ? 'Deshabilitar' : 'Habilitar' }}" style="color: #0000EE; text-decoration: none; margin-right: 5px;">
                                    [{{ $item->estado_lapso ? 'Deshabilitar' : 'Habilitar' }}]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="delete({{ $item->id }})" wire:confirm="¿Estás seguro de eliminar PERMANENTEMENTE este lapso?" title="Eliminar" style="color: #FF0000; text-decoration: none;">
                                    [Eliminar]
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($items->isEmpty())
                        <tr>
                            <td colspan="4" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
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
                {{ $editingId ? 'Editar Lapso' : 'Nuevo Lapso' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="margin-top: 15px;">
                    <tr>
                        <td width="30%"><b>Nombre del Lapso:</b></td>
                        <td width="70%" colspan="2">
                            <input wire:model="nombre" type="text" style="width: 90%;">
                            <span class="obligatorio">*</span>
                            @error('nombre') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"><b>Fecha Inicio:</b></td>
                        <td valign="top">
                            <input wire:model="fecha_inicio" type="date">
                            <span class="obligatorio">*</span>
                            @error('fecha_inicio') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                        </td>
                        <td valign="top"><b>Fecha Fin:</b></td>
                        <td valign="top">
                            <input wire:model="fecha_fin" type="date">
                            <span class="obligatorio">*</span>
                            @error('fecha_fin') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
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
