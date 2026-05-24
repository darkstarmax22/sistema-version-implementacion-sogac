<?php

use App\Models\Proyecto;
use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $lapso_id = '';
    public $selectedProject = null;
    public $isDetailsModalOpen = false;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingLapsoId() { $this->resetPage(); }

    public function openDetails($id)
    {
        $this->selectedProject = Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'metodologia', 'tipo_investigacion', 'comunidad'])->find($id);
        $this->isDetailsModalOpen = true;
        $this->dispatch('refresh-icons');
    }

    public function closeDetails()
    {
        $this->isDetailsModalOpen = false;
        $this->selectedProject = null;
    }

    public function with()
    {
        $proyectos = Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'comunidad'])
            ->visiblesPublico()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('titulo', 'like', '%'.$this->search.'%')
                        ->orWhere('resumen', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);

        return [
            'proyectos' => $proyectos,
            'lapsos' => LapsoAcademico::where('lap_estatus', 'A')->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Consulta de Proyectos Institucionales</h2>

    <!-- Filtros -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Criterios de Búsqueda</legend>
        <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%">
                    <b>Término (Título, Resumen):</b><br>
                    <input wire:model.live="search" type="text" style="width: 95%;" placeholder="...">
                </td>
                <td width="50%">
                    <b>Lapso Académico:</b><br>
                    <select wire:model.live="lapso_id" style="width: 95%;">
                        <option value="">Todos los Lapsos</option>
                        @foreach($lapsos as $l)
                            <option value="{{ $l->id }}">{{ $l->nombre }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>

    <!-- Resultados -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de la Búsqueda</legend>

        <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead>
                <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                    <th padding="5" width="50%">Título del Proyecto / Autores</th>
                    <th padding="5" width="25%">Resumen</th>
                    <th padding="5" width="10%">Fecha</th>
                    <th padding="5" width="15%">Acciones</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($proyectos as $p)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td align="left" style="padding: 5px;">
                            <div style="font-weight: bold; font-size: 12px; color: #8b0000; margin-bottom: 3px;">{{ $p->titulo }}</div>
                            <div style="font-size: 10px; color: #000;">
                                @if($p->comunidad)
                                    <b>Comunidad:</b> {{ mb_strtoupper($p->comunidad->nombre) }}
                                @endif
                            </div>
                        </td>
                        <td align="justify" style="padding: 5px; font-size: 10px; color: #333;">
                            {{ Str::limit($p->resumen, 100) }}
                        </td>
                        <td align="center" style="padding: 5px; font-size: 10px;">
                            {{ $p->fecha_subida?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td align="center" style="padding: 5px;">
                            <a href="#" wire:click.prevent="openDetails({{ $p->id }})" title="Ver Detalles" style="color: #0000EE; text-decoration: none; margin-bottom: 5px; display: inline-block; font-weight: bold;">
                                [Ver Detalles]
                            </a>
                            <br>
                            @if($p->archivo_path)
                                <a href="{{ Storage::url($p->archivo_path) }}" target="_blank" style="color: #008000; text-decoration: none; display: inline-block; font-weight: bold; font-size: 10px;">
                                    [Ver PDF Legado]
                                </a>
                            @else
                                <span style="color: #999; font-size: 9px;">Sin Documento</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if($proyectos->isEmpty())
                    <tr>
                        <td colspan="5" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron proyectos con los criterios seleccionados
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div style="margin-top: 10px;">
            {{ $proyectos->links() }}
        </div>
    </fieldset>

    <!-- Modal de Detalles -->
    @if($isDetailsModalOpen && $selectedProject)
        <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; display: flex; justify-content: center; align-items: center; overflow-y: auto;">
            <div style="background-color: #FFF; border: 2px solid #8b0000; border-radius: 6px; padding: 20px; width: 850px; max-height: 90vh; overflow-y: auto; box-shadow: 0px 0px 10px #000;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #CCC; padding-bottom: 10px; margin-bottom: 15px;">
                    <div style="width: 90%;">
                        @if($selectedProject->fecha_subida)
                            <span style="background-color: #8bb2b7; color: #000; font-size: 10px; font-weight: bold; padding: 2px 5px; margin-right: 5px; border: 1px solid #777;">
                                {{ $selectedProject->fecha_subida->format('d/m/Y') }}
                            </span>
                        @endif
                        @if($selectedProject->asignacion_ct)
                            <span style="background-color: #FFFF00; color: #000; font-size: 10px; font-weight: bold; padding: 2px 5px; border: 1px solid #CCC;">Asig. C&T</span>
                        @endif
                        <h3 style="margin-top: 5px; margin-bottom: 0; color: #000; font-size: 16px; font-weight: bold;">
                            {{ $selectedProject->titulo }}
                        </h3>
                    </div>
                    <div>
                        <button type="button" wire:click="closeDetails" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #FF0000;">X</button>
                    </div>
                </div>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Resumen del Proyecto</legend>
                    <div style="font-size: 12px; text-align: justify;">
                        {{ $selectedProject->resumen }}
                    </div>
                </fieldset>

                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="48%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 10px; height: 100%;">
                                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Ficha Técnica</legend>
                                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                                    <tr>
                                        <td width="35%"><b>Publicación:</b></td>
                                        <td width="65%">{{ $selectedProject->tipo_publicacion?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Investigación:</b></td>
                                        <td>{{ $selectedProject->tipo_investigacion?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Metodología:</b></td>
                                        <td>{{ $selectedProject->metodologia?->nombre ?? 'N/D' }}</td>
                                    </tr>
                                    <tr>
                                        <td><b>Línea de Inv.:</b></td>
                                        <td>{{ $selectedProject->linea_investigacion?->nombre_investigacion ?? 'N/D' }}</td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                        <td width="4%"></td>
                        <td width="48%" valign="top">
                            <fieldset style="border: 1px solid #CCC; padding: 10px; height: 100%;">
                                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Comunidad Beneficiada</legend>
                                <div style="font-size: 11px;">
                                    <b>Nombre:</b> {{ $selectedProject->comunidad->nombre ?? 'N/A' }}<br>
                                    <b>RIF:</b> {{ $selectedProject->comunidad->rif ?? 'N/A' }}<br>
                                    <b>Dirección:</b>
                                    {{ $selectedProject->comunidad?->direccion ?? 'N/A' }}
                                </div>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <div style="margin-top: 15px; border-top: 1px solid #CCC; padding-top: 10px; font-size: 11px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <b>Calificación Final:</b> <span style="font-size: 14px; font-weight: bold; color: #8b0000;">{{ $selectedProject->calificacion }}/20</span>
                    </div>
                </div>

                @if($selectedProject->archivo_path)
                    <fieldset style="border: 1px solid #CCC; padding: 10px; margin-top: 15px; margin-bottom: 15px;">
                        <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Documento del Proyecto</legend>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; padding: 5px;">
                            @if($selectedProject->archivo_path)
                                <a href="{{ Storage::url($selectedProject->archivo_path) }}" target="_blank" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 6px 15px; font-weight: bold; background-color: #8bb2b7; color: #000; text-decoration: none; display: inline-block; font-size: 11px;">
                                    📁 Ver PDF Legado
                                </a>
                            @endif
                        </div>
                    </fieldset>
                @endif

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="closeDetails" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                        Cerrar Detalles
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
