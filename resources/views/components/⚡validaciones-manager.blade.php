<?php

use App\Models\Proyecto;
use App\Models\Coordinacion;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\TipoPublicacion;
use App\Models\TipoInvestigacion;
use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $motivo_rechazo = '';
    public $selectedProjectId = null;
    public $selectedProject = null;
    public $viewMode = 'list';

    public function approve($id)
    {
        $proyecto = Proyecto::find($id);
        $proyecto->update([
            'estado_validacion' => 'aprobado',
            'estado_logico' => true,
        ]);

        session()->flash('message', 'Proyecto aprobado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function openRejectModal($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function openDetails($id)
    {
        $this->selectedProject = Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'metodologia', 'tipo_investigacion', 'comunidad'])->find($id);
        $this->viewMode = 'details';
        $this->dispatch('refresh-icons');
    }

    public function backToList()
    {
        $this->viewMode = 'list';
        $this->selectedProjectId = null;
        $this->selectedProject = null;
        $this->motivo_rechazo = '';
    }

    public function reject()
    {
        $this->validate([
            'motivo_rechazo' => 'required|min:10'
        ]);

        $proyecto = Proyecto::find($this->selectedProjectId);
        $proyecto->update([
            'estado_validacion' => 'rechazado',
            'motivo_rechazo' => $this->motivo_rechazo,
            'estado_logico' => false,
        ]);

        $this->backToList();
        session()->flash('message', 'Proyecto rechazado.');
        $this->dispatch('refresh-icons');
    }

    public function approveFromDetails($id)
    {
        $this->approve($id);
        $this->backToList();
    }

    public function rejectFromDetails($id)
    {
        $this->selectedProjectId = $id;
        $this->motivo_rechazo = '';
        $this->viewMode = 'reject';
    }

    public function with()
    {
        return [
            'proyectos' => Proyecto::with(['tipo_publicacion', 'linea_investigacion', 'comunidad'])
                ->where('estado_validacion', 'pendiente')
                ->where('titulo', 'like', '%' . $this->search . '%')
                ->latest()
                ->paginate(10)
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Validación de Proyectos</h2>

    <!-- Notification -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <!-- Search -->
        <div style="margin-bottom: 15px;">
            <b>Búsqueda (Título):</b>
            <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
        </div>

        <!-- Data Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Revisión de Expedientes Pendientes</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="35%">Título del Proyecto / Resumen</th>
                        <th padding="5" width="25%">Lapso / Coordinación</th>
                        <th padding="5" width="15%">Documento</th>
                        <th padding="5" width="25%">Acciones de Validación</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($proyectos as $p)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="left" style="padding: 5px;">
                                <span style="font-weight: bold;">{{ mb_strtoupper($p->titulo) }}</span>
                                <br>
                                <span style="font-size: 10px; color: #555;">{{ Str::limit($p->resumen, 60) }}</span>
                                <br>
                                <span style="font-size: 9px; color: #888; font-weight: bold;">Registrado el: {{ $p->created_at->format('d/m/Y') }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $p->lapso_academico->nombre }}
                                @if($p->coordinacion)
                                    <br><span style="font-size: 10px; font-weight: bold;">Coordinación: {{ $p->coordinacion->nombre }}</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($p->archivo_path)
                                    <a href="{{ Storage::url($p->archivo_path) }}" target="_blank" style="color: #0000EE; text-decoration: none; font-weight: bold;">[Ver Documento PDF]</a>
                                @else
                                    <span style="color: #999; font-size: 10px;">No aportado</span>
                                @endif
                                <br><br>
                                <a href="#" wire:click.prevent="openDetails({{ $p->id }})" title="Ver Detalles" style="color: #0000EE; text-decoration: none; display: inline-block;">
                                    [Ver Ficha Técnica]
                                </a>
                            </td>
                            <td align="center" style="padding: 5px;">
                                <button wire:click="approve({{ $p->id }})" onclick="return confirm('¿Confirma que el documento es válido y aprueba el proyecto?')" style="border: 1px solid #008000; border-radius: 4px; padding: 2px 10px; font-weight: bold; background-color: #d4edda; color: #155724; cursor: pointer; display: block; width: 90%; margin: 0 auto 5px auto; font-size: 10px;">
                                    Aprobar / Confirmar
                                </button>
                                <button wire:click="openRejectModal({{ $p->id }})" style="border: 1px solid #FF0000; border-radius: 4px; padding: 2px 10px; font-weight: bold; background-color: #f8d7da; color: #721c24; cursor: pointer; display: block; width: 90%; margin: 0 auto; font-size: 10px;">
                                    Rechazar
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    @if($proyectos->isEmpty())
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                Bandeja vacía. No hay expedientes pendientes de revisión.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                {{ $proyectos->links() }}
            </div>
        </fieldset>

    @elseif($viewMode === 'reject')
        <!-- Rechazar Proyecto -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Indicar Motivo de Rechazo</legend>
            
            <div style="margin-bottom: 15px; font-size: 12px;">
                Por favor, detalle la justificación para no aprobar el expediente:
            </div>

            <div style="text-align: left;">
                <textarea wire:model="motivo_rechazo" rows="6" style="width: 100%; max-width: 600px; padding: 5px;"></textarea>
                @error('motivo_rechazo') <div class="obligatorio" style="font-size: 11px; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>

            <div style="text-align: left; margin-top: 20px;">
                <button type="button" wire:click="backToList" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="reject" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: bold; background-color: #f8d7da; color: #721c24; height: 26px;">Confirmar Rechazo</button>
            </div>
        </fieldset>

    @elseif($viewMode === 'details' && $selectedProject)
        <!-- Ficha Técnica -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Ficha Técnica del Proyecto</legend>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #CCC; padding-bottom: 10px; margin-bottom: 15px;">
                <div style="width: 100%;">
                    <span style="background-color: #8bb2b7; color: #000; font-size: 10px; font-weight: bold; padding: 2px 5px; margin-right: 5px; border: 1px solid #777;">
                        {{ $selectedProject->lapso_academico->nombre }}
                    </span>
                    <h3 style="margin-top: 5px; margin-bottom: 0; color: #000; font-size: 16px; font-weight: bold;">
                        {{ $selectedProject->titulo }}
                    </h3>
                </div>
            </div>

            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Resumen del Proyecto</legend>
                <div style="font-size: 12px; text-align: justify; padding: 5px;">
                    {{ $selectedProject->resumen }}
                </div>
            </fieldset>

            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="48%" valign="top">
                        <fieldset style="border: 1px solid #CCC; padding: 10px; height: 100%;">
                            <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Detalles de Investigación</legend>
                            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                                <tr>
                                    <td width="35%"><b>Publicación:</b></td>
                                    <td width="65%">{{ $selectedProject->tipo_publicacion->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><b>Investigación:</b></td>
                                    <td>{{ $selectedProject->tipo_investigacion->nombre }}</td>
                                </tr>
                                <tr>
                                    <td><b>Metodología:</b></td>
                                    <td>{{ $selectedProject->metodologia->nombre }}</td>
                                </tr>
                                @if($selectedProject->coordinacion)
                                    <tr>
                                        <td><b>Coordinación:</b></td>
                                        <td>{{ $selectedProject->coordinacion->nombre }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><b>Línea de Inv.:</b></td>
                                    <td>{{ $selectedProject->linea_investigacion->nombre_investigacion }}</td>
                                </tr>
                            </table>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <div style="text-align: center; margin-top: 20px; border-top: 1px solid #CCC; padding-top: 15px;">
                <button type="button" wire:click="approveFromDetails({{ $selectedProject->id }})" onclick="return confirm('¿Confirma que aprueba el proyecto?')" class="boton" style="border: 1px solid #008000; border-radius: 4px; padding: 6px 15px; font-weight: bold; background-color: #d4edda; color: #155724; cursor: pointer; margin-right: 15px;">
                    Aprobar Proyecto Ahora
                </button>
                <button type="button" wire:click="rejectFromDetails({{ $selectedProject->id }})" class="boton" style="border: 1px solid #FF0000; border-radius: 4px; padding: 6px 15px; font-weight: bold; background-color: #f8d7da; color: #721c24; cursor: pointer; margin-right: 15px;">
                    Rechazar Revisión
                </button>
                <button type="button" wire:click="backToList" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 6px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; cursor: pointer;">
                    Regresar al Listado
                </button>
            </div>
        </fieldset>
    @endif
</div>
