<?php

use App\Models\Componente;
use App\Models\Coordinacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $viewMode = 'list';
    public $editingId = null;

    public $coordinacion_id = '';
    public $anio = '';

    // For editing or single mode
    public $nombre = '';
    public $es_obligatorio = true;

    // For multiple addition
    public $rows = [];

    protected function rules()
    {
        if ($this->editingId) {
            return [
                'nombre' => 'required|min:3',
                'coordinacion_id' => 'required',
                'anio' => 'required|string',
                'es_obligatorio' => 'boolean',
            ];
        }

        return [
            'coordinacion_id' => 'required',
            'anio' => 'required|string',
            'rows.*.nombre' => 'required|min:3',
            'rows.*.es_obligatorio' => 'boolean',
        ];
    }

    protected $messages = [
        'nombre.required' => 'Debe nombrar el documento.',
        'rows.*.nombre.required' => 'Debe nombrar el documento en esta fila.',
        'coordinacion_id.required' => 'Debe asignar una PNF / Coordinación rectora.',
        'anio.required' => 'Debe asignarle el trayecto (I, II, III, IV).',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetFields();
        $this->rows = [['nombre' => '', 'es_obligatorio' => true]];

        $this->viewMode = 'form';
    }

    public function addRow()
    {
        $this->rows[] = ['nombre' => '', 'es_obligatorio' => true];
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
        }
    }

    public function edit($id)
    {
        $this->resetValidation();
        $this->editingId = $id;
        $comp = Componente::findOrFail($id);

        $this->nombre = $comp->nombre;
        $this->coordinacion_id = $comp->coordinacion_id;
        $this->anio = $comp->anio;
        $this->es_obligatorio = $comp->es_obligatorio;

        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->resetFields();
        $this->viewMode = 'list';
    }

    public function resetFields()
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->coordinacion_id = '';
        $this->anio = '';
        $this->es_obligatorio = true;
        $this->rows = [];
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            Componente::guardar(
                [
                    'nombre' => $this->nombre,
                    'coordinacion_id' => $this->coordinacion_id,
                    'anio' => $this->anio,
                    'es_obligatorio' => $this->es_obligatorio,
                ],
                $this->editingId,
            );
            session()->flash('message', 'Componente documental actualizado.');
        } else {
            Componente::guardarMuchos($this->rows, $this->coordinacion_id, $this->anio);
            session()->flash('message', count($this->rows) . ' Componentes creados con éxito.');
        }

        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = Componente::findOrFail($id);
        $item->alternarEstado();
        session()->flash('message', 'Estado lógico del componente actualizado.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        $item = Componente::findOrFail($id);
        $item->borrar();
        session()->flash('message', 'Regla de componente eliminada de la base de datos.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $query = Componente::where(function ($q) {
            $q->where('nombre', 'like', "%{$this->search}%")->orWhere('anio', 'like', "%{$this->search}%");
        });

        return [
            'listaRegistros' => $query->latest()->paginate(10),
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
            min-width: 110px;
            font-size: 0.92rem;
            font-weight: 600;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
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
            padding: 0.35rem 0.7rem;
            min-width: auto;
            font-size: 0.85rem;
        }

        .cm-btn-group button {
            margin-right: 0.35rem;
            margin-bottom: 0.25rem;
        }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Componentes</h2>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Filtrar Componente:</b>
                <input wire:model.live="search" type="text" style="width: 250px;"
                    placeholder="Buscar componente o año...">
            </div>
            <button wire:click="create" class="cm-btn cm-btn-success cm-btn-sm">
                Adicionar Componente Nuevo
            </button>
        </div>

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Sistema de Componentes
                de Proyecto</legend>
            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N°</th>
                        <th width="35%">Nombre del Documento Exigido</th>
                        <th width="30%">Coordinación Asociada y Trayecto/Año</th>
                        <th width="10%">Obligatorio</th>
                        <th width="10%">Estatus</th>
                        <th width="10%">Configurar</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($listaRegistros as $item)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$item->estado_logico ? 'color: #888;' : 'color: #000;' }}"
                            valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="center" style="font-weight: bold; padding: 8px;">
                                {{ mb_strtoupper($item->nombre) }}</td>
                            <td align="center" style="font-weight: bold; font-style: italic; padding: 8px;">
                                {{ $item->nombre_coordinacion }} <br>
                                <span style="color: #8b0000;">Trayecto/Año: {{ mb_strtoupper($item->anio) }}</span>
                            </td>
                            <td align="center">
                                {!! $item->es_obligatorio
                                    ? '<span style="color: #FF0000; font-weight:bold;">SÍ</span>'
                                    : '<span style="color: #008000; font-weight:bold;">NO</span>' !!}
                            </td>
                            <td align="center">
                                @if ($item->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Suspendido</span>
                                @endif
                            </td>
                            <td align="center">
                                <div class="cm-btn-group"
                                    style="display: inline-flex; flex-wrap: wrap; justify-content: center;">
                                    <button type="button" wire:click.prevent="edit({{ $item->id }})"
                                        title="Editar Regla" class="cm-btn cm-btn-primary cm-btn-sm">
                                        Editar
                                    </button>
                                    <button type="button" wire:click.prevent="toggleStatus({{ $item->id }})"
                                        title="{{ $item->estado_logico ? 'Suspender Regla' : 'Publicar Regla' }}"
                                        class="cm-btn cm-btn-warning cm-btn-sm">
                                        {{ $item->estado_logico ? 'Suspender' : 'Publicar' }}
                                    </button>
                                    <button type="button" wire:click.prevent="delete({{ $item->id }})"
                                        wire:confirm="¿Seguro desea eliminar esta regla? Desaparecerán solicitudes antiguas para este documento."
                                        title="Eliminar Base" class="cm-btn cm-btn-danger cm-btn-sm">
                                        Borrar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($listaRegistros->isEmpty())
                        <tr>
                            <td colspan="6" align="center"
                                style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay componentes configurados en la Base de Datos.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $listaRegistros->links() }}</div>
        </fieldset>
    @else
        <!-- Formulario (Modo Form) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Editar Directriz de Componente' : 'Registrar Exigencias de Proyecto' }}
            </legend>
            <form wire:submit="save" style="margin: 0;">
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                    <tr>
                        <td width="30%"><b>Programa Titular:</b></td>
                        <td width="70%">
                            @if (auth()->user()->hasRole('administrador'))
                                <select wire:model="coordinacion_id" style="width: 80%; padding: 4px;">
                                    <option value="">Seleccione a quién pertenece esta regla...</option>
                                    @foreach ($programas as $p)
                                        <option value="{{ $p->id }}">{{ $p->siglas }} -
                                            {{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div
                                    style="padding: 4px 8px; background-color: #f5f5f5; border: 1px solid #ddd; width: 80%; font-weight:bold; color: #555;">
                                    {{ \App\Models\Coordinacion::find($coordinacion_id)?->nombre ?? '[COORDINACIÓN AUTOASIGNADA]' }}
                                </div>
                            @endif
                            @error('coordinacion_id')
                                <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                    <tr>
                        <td width="30%"><b>Aplica a los de Trayecto/Año:</b></td>
                        <td width="70%">
                            <input type="text" wire:model="anio" style="width: 25%; padding: 4px;"
                                placeholder="Ej: I, II, III, IV...">
                            @error('anio')
                                <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                            @enderror
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 15px;">
                    <table width="100%" border="1" cellpadding="5" cellspacing="0"
                        style="border-collapse: collapse; border-color: #bbbbbb; font-size: 12px;">
                        <thead style="background-color: #f0f0f0;">
                            <tr>
                                <th>Nombre del Componente</th>
                                <th width="15%">Obligatorio</th>
                                @if (!$editingId)
                                    <th width="10%">Acción</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if ($editingId)
                                <tr>
                                    <td>
                                        <input type="text" wire:model="nombre" style="width: 95%; padding: 4px;"
                                            placeholder="Ej: Trabajo Escrito...">
                                        @error('nombre')
                                            <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                                        @enderror
                                    </td>
                                    <td align="center">
                                        <input type="checkbox" wire:model="es_obligatorio">
                                    </td>
                                </tr>
                            @else
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td>
                                            <input type="text" wire:model="rows.{{ $index }}.nombre"
                                                style="width: 95%; padding: 4px;"
                                                placeholder="Ej: Trabajo Escrito...">
                                            @error("rows.$index.nombre")
                                                <br><span style="color:red; font-size:10px;">{{ $message }}</span>
                                            @enderror
                                        </td>
                                        <td align="center">
                                            <input type="checkbox"
                                                wire:model="rows.{{ $index }}.es_obligatorio">
                                        </td>
                                        <td align="center">
                                            @if (count($rows) > 1)
                                                <button type="button" wire:click="removeRow({{ $index }})"
                                                    class="cm-btn cm-btn-danger cm-btn-sm">
                                                    Quitar
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>

                    @if (!$editingId)
                        <div style="margin-top: 10px;">
                            <button type="button" wire:click="addRow()" class="cm-btn cm-btn-success cm-btn-sm">
                                Agregar otro componente
                            </button>
                        </div>
                    @endif
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="cm-btn cm-btn-success" style="margin-right: 10px;">
                        @if ($editingId)
                            Guardar cambios
                        @else
                            Registrar Componentes
                        @endif
                    </button>
                    <button type="button" wire:click="cancel" class="cm-btn cm-btn-secondary">
                        Cancelar
                    </button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
