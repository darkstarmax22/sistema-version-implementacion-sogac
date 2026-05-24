<?php

use App\Models\Proyecto;
use App\Models\ProyectoDocumento;
use App\Models\Componente;
use App\Models\Coordinacion;
use App\Models\LineaInvestigacion;
use App\Models\MetodologiaInvestigacion;
use App\Models\TipoPublicacion;
use App\Models\TipoInvestigacion;
use App\Models\LapsoAcademico;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Form fields
    public $titulo = '';
    public $resumen = '';
    public $fecha_subida = '';
    public $asignacion_ct = false;
    public $calificacion = '';
    public $fecha_aprobacion = '';
    public $linea_investigacion_id = '';
    public $metodologia_id = '';
    public $tipo_publicacion_id = '';
    public $tipo_investigacion_id = '';
    public $lapso_academico_id = '';
    public $coordinacion_id = '';
    public $comunidad_id = '';
    public $equipo_id = '';
    public $trayecto = '';
    
    // Arrays para soportar la Multi-Subida Dinámica
    public $archivos_componentes = [];
    public $archivos_actuales = [];

    public $search = '';
    public $editingId = null;
    public $viewMode = 'list';

    public function messages()
    {
        return [
            'titulo.required' => 'El título del proyecto es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 5 caracteres.',
            'resumen.required' => 'El resumen es obligatorio.',
            'resumen.min' => 'El resumen debe tener al menos 10 caracteres.',
            'fecha_subida.required' => 'La fecha de subida es obligatoria.',
            'calificacion.required' => 'La calificación es obligatoria.',
            'calificacion.integer' => 'La calificación debe ser un número entero.',
            'calificacion.min' => 'La calificación mínima es 1.',
            'calificacion.max' => 'La calificación máxima es 20.',
            'fecha_aprobacion.required' => 'La fecha de aprobación es obligatoria.',
            'linea_investigacion_id.required' => 'Debe seleccionar una línea de investigación.',
            'metodologia_id.required' => 'Debe seleccionar una metodología.',
            'tipo_publicacion_id.required' => 'Debe seleccionar un tipo de publicación.',
            'tipo_investigacion_id.required' => 'Debe seleccionar un tipo de investigación.',
            'lapso_academico_id.required' => 'Debe seleccionar un lapso académico.',
            'coordinacion_id.required' => 'Debe seleccionar una Coordinación.',
            'equipo_id.required' => 'Debe elegir un Equipo de Proyecto.',
            'trayecto.required' => 'El trayecto es obligatorio.',
        ];
    }

    public function create()
    {
        $this->resetFields();
        $this->fecha_subida = now()->format('Y-m-d');
        $this->viewMode = 'form';
    }

    public function edit($id)
    {
        $this->resetFields();
        $this->editingId = $id;
        $item = Proyecto::with('documentos')->find($id);
        
        $this->titulo = $item->titulo;
        $this->resumen = $item->resumen;
        $this->fecha_subida = $item->fecha_subida->format('Y-m-d');
        $this->asignacion_ct = (bool)$item->asignacion_ct;
        $this->calificacion = $item->calificacion;
        $this->fecha_aprobacion = $item->fecha_aprobacion ? $item->fecha_aprobacion->format('Y-m-d') : '';
        $this->linea_investigacion_id = $item->linea_investigacion_id;
        $this->metodologia_id = $item->metodologia_id;
        $this->tipo_publicacion_id = $item->tipo_publicacion_id;
        $this->tipo_investigacion_id = $item->tipo_investigacion_id;
        $this->lapso_academico_id = $item->lapso_academico_id;
        $this->coordinacion_id = $item->coordinacion_id;
        $this->comunidad_id = $item->comunidad_id;
        $this->equipo_id = $item->equipo_id;
        $this->trayecto = $item->trayecto;
        
        $this->archivos_actuales = [];
        foreach($item->documentos as $doc) {
            $this->archivos_actuales[$doc->componente_id] = $doc->archivo_path;
        }

        $this->viewMode = 'form';
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->resetFields();
    }

    public function resetFields()
    {
        $this->titulo = '';
        $this->resumen = '';
        $this->fecha_subida = '';
        $this->asignacion_ct = false;
        $this->calificacion = '';
        $this->fecha_aprobacion = '';
        $this->linea_investigacion_id = '';
        $this->metodologia_id = '';
        $this->tipo_publicacion_id = '';
        $this->tipo_investigacion_id = '';
        $this->lapso_academico_id = '';
        $this->coordinacion_id = '';
        $this->comunidad_id = '';
        $this->equipo_id = '';
        $this->trayecto = '';
        $this->archivos_componentes = [];
        $this->archivos_actuales = [];
        $this->editingId = null;
    }

    public function save()
    {
        $rules = [
            'titulo' => 'required|min:5|max:255',
            'resumen' => 'required|min:10',
            'fecha_subida' => 'required|date',
            'asignacion_ct' => 'boolean',
            'calificacion' => 'required|integer|min:1|max:20',
            'fecha_aprobacion' => 'required|date',
            'linea_investigacion_id' => 'required|exists:linea_investigacions,id',
            'metodologia_id' => 'required|exists:metodologia_investigacions,id',
            'tipo_publicacion_id' => 'required|exists:tipo_publicacions,id',
            'tipo_investigacion_id' => 'required|exists:tipo_investigacions,id',
            'lapso_academico_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!LapsoAcademico::where('lap_codigo', $value)->exists()) {
                        $fail('El lapso académico seleccionado no es válido.');
                    }
                }
            ],
            'coordinacion_id' => 'required|exists:coordinaciones,id',
            'equipo_id' => 'required|exists:equipos,id',
            'trayecto' => 'required|string|max:100',
        ];

        $componentes_req = Componente::where('coordinacion_id', $this->coordinacion_id)
                                    ->where('anio', $this->trayecto)
                                    ->where('estado_logico', true)
                                    ->get();

        foreach($componentes_req as $c) {
            if ($c->es_obligatorio && !isset($this->archivos_actuales[$c->id])) {
                $rules['archivos_componentes.'.$c->id] = 'required|file|max:20480';
            } else {
                $rules['archivos_componentes.'.$c->id] = 'nullable|file|max:20480';
            }
        }

        $this->validate($rules, [
            'archivos_componentes.*.required' => 'El componente es de subida estrictamente obligatoria.',
            'archivos_componentes.*.max' => 'El archivo no debe exceder los 20MB permitidos.'
        ]);

        $equipoObj = \App\Models\Equipo::find($this->equipo_id);
        $comunidad_id_final = $equipoObj ? $equipoObj->comunidad_id : null;

        $proyecto = Proyecto::updateOrCreate(
            ['id' => $this->editingId],
            [
                'titulo' => $this->titulo,
                'resumen' => $this->resumen,
                'fecha_subida' => $this->fecha_subida,
                'asignacion_ct' => $this->asignacion_ct,
                'calificacion' => $this->calificacion ?: null,
                'fecha_aprobacion' => $this->fecha_aprobacion ?: null,
                'linea_investigacion_id' => $this->linea_investigacion_id,
                'metodologia_id' => $this->metodologia_id,
                'tipo_publicacion_id' => $this->tipo_publicacion_id,
                'tipo_investigacion_id' => $this->tipo_investigacion_id,
                'lapso_academico_id' => $this->lapso_academico_id,
                'coordinacion_id' => $this->coordinacion_id,
                'comunidad_id' => $comunidad_id_final,
                'equipo_id' => $this->equipo_id,
                'trayecto' => $this->trayecto,
                'archivo_path' => null, // Deprecated base column
                'estado_validacion' => 'pendiente',
                'persona_id' => $this->editingId ? Proyecto::find($this->editingId)->persona_id : auth()->id(),
            ]
        );

        foreach($componentes_req as $c) {
            if (isset($this->archivos_componentes[$c->id])) {
                $path = $this->archivos_componentes[$c->id]->store('proyectos/componentes', 'public');
                ProyectoDocumento::updateOrCreate(
                    ['proyecto_id' => $proyecto->id, 'componente_id' => $c->id],
                    ['archivo_path' => $path]
                );
            }
        }

        $this->viewMode = 'list';
        session()->flash('message', $this->editingId ? 'Proyecto actualizado con éxito.' : 'Proyecto registrado con éxito.');
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = Proyecto::find($id);
        $item->update(['estado_logico' => !$item->estado_logico]);
        session()->flash('message', 'Estado del proyecto actualizado.');
        $this->dispatch('refresh-icons');
    }

    public function delete($id)
    {
        Proyecto::find($id)->delete();
        session()->flash('message', 'Proyecto eliminado correctamente.');
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $canRegister = false;
        $equipos_disp = collect();

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole('administrador')) {
                 $canRegister = true;
                 $equipos_disp = \App\Models\Equipo::where('estado_logico', true)
                                                 ->whereNotNull('comunidad_id')
                                                 ->with('comunidad')
                                                 ->orderBy('nombre')
                                                 ->get();
            } else {
                 $equipos_disp = $user->equipos()
                                      ->wherePivot('role_id', 1)
                                      ->whereNotNull('comunidad_id')
                                      ->with('comunidad')
                                      ->get();
                 if($equipos_disp->count() > 0) {
                      $canRegister = true;
                 }
            }
        }

        $componentes_requeridos = [];
        if ($this->coordinacion_id && $this->trayecto) {
             $componentes_requeridos = Componente::where('coordinacion_id', $this->coordinacion_id)
                                    ->where('anio', $this->trayecto)
                                    ->where('estado_logico', true)->get();
        }

        return [
            'canRegister' => $canRegister,
            'equipos_disp' => $equipos_disp,
            'componentes_requeridos' => $componentes_requeridos,
            'proyectos' => Proyecto::with(['lapso_academico', 'tipo_publicacion', 'linea_investigacion', 'validador', 'user', 'coordinacion', 'comunidad', 'equipo.estudiantes', 'documentos.componente'])
                        ->where('titulo', 'like', '%' . $this->search . '%')
                        ->when(!auth()->user()->hasRole('administrador'), function($query) {
                            $query->where('persona_id', auth()->id());
                        })
                        ->latest()
                        ->paginate(10),
            'lineas' => LineaInvestigacion::where('activo', true)->get(),
            'metodologias' => MetodologiaInvestigacion::where('estado_logico', true)->get(),
            'tipos_publicacion' => TipoPublicacion::where('estado_logico', true)->get(),
            'tipos_investigacion' => TipoInvestigacion::where('estado_logico', true)->get(),
            'lapsos' => LapsoAcademico::where('lap_estatus', 'A')->get(),
            'coordinaciones' => Coordinacion::where('activo', true)->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Proyectos</h2>

    <!-- Notification -->
    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <b>Buscar Proyecto (Título):</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>
            
            @if($canRegister)
            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">
                Registrar Nuevo Proyecto
            </button>
            @endif
        </div>

        <!-- Table -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Proyectos Institucionales</legend>
            
            <table width="100%" border="1" cellpadding="4" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                        <th padding="5" width="25%">Título del Proyecto</th>
                        <th padding="5" width="20%">Comunidad / Línea Inv.</th>
                        <th padding="5" width="15%">Lapso / Coordinación</th>
                        <th padding="5" width="15%">Validación / C&T</th>
                        <th padding="5" width="10%">Estado</th>
                        <th padding="5" width="15%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($proyectos as $p)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$p->estado_logico ? 'color: #888;' : 'color: #000;' }}" valign="top">
                            <td align="left" style="padding: 5px; font-weight: bold;">
                                {{ $p->titulo }}
                                <br>
                                <span style="font-size: 9px; font-weight: normal; color: {{ !$p->estado_logico ? '#888' : '#555' }};">Subido: {{ $p->fecha_subida->format('d/m/Y') }}</span>
                                @if($p->archivo_path)
                                    <br><a href="{{ Storage::url($p->archivo_path) }}" target="_blank" style="color: #888; font-size: 10px;">[Ver Documento Legado]</a>
                                @endif
                                @if($p->documentos && $p->documentos->count() > 0)
                                    <div style="margin-top: 5px;">
                                    @foreach($p->documentos as $doc)
                                        <a href="{{ Storage::url($doc->archivo_path) }}" target="_blank" style="color: #0000EE; font-size: 10px; font-weight:bold; display:block;">[📁 {{ mb_strtoupper($doc->componente->nombre ?? 'DOCUMENTO') }}]</a>
                                    @endforeach
                                    </div>
                                @endif
                            </td>
                            <td align="left" style="padding: 5px;">
                                <span style="font-size: 11px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#8b0000' }};">Equipo: {{ $p->equipo->nombre ?? 'N/A' }}</span>
                                <br>
                                <span style="font-size: 10px; color: #555;">Comunidad: {{ $p->comunidad->nombre ?? 'N/A' }}</span>
                                <br>
                                <span style="font-size: 9px; color: #333;">
                                    @php 
                                        $todosEstudiantes = $p->equipo ? $p->equipo->estudiantes : collect();
                                    @endphp
                                    @if($todosEstudiantes->count() > 0)
                                        <b>Integrantes:</b> 
                                        @foreach($todosEstudiantes as $estu)
                                            {{ $estu->nombre }} {{ mb_substr($estu->apellido, 0, 1) }}. ({{ $estu->pivot->role_id == 1 ? 'Líder' : 'Autor' }}){{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    @else
                                        <i>- Sin integrantes vinculados -</i>
                                    @endif
                                </span>
                                <br>
                                <span style="font-size: 10px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#666' }};">Línea Inv: {{ $p->linea_investigacion->nombre_investigacion ?? '' }}</span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                {{ $p->lapso_academico->nombre }}
                                <br>
                                <span style="font-size: 10px; font-weight: bold; color: {{ !$p->estado_logico ? '#888' : '#666' }};">
                                    Coordinación: {{ $p->coordinacion->nombre ?? '' }} 
                                    @if($p->trayecto) (Tr. {{ $p->trayecto }}) @endif
                                </span>
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($p->estado_validacion == 'pendiente')
                                    <span style="color: #d4a017; font-weight: bold;">En Revisión</span>
                                @elseif($p->estado_validacion == 'rechazado')
                                    <span style="color: #FF0000; font-weight: bold;" title="Por: {{ $p->validador?->nombre ?? 'N/A' }}">Rechazado</span>
                                @else
                                    <span style="color: #008000; font-weight: bold;" title="Por: {{ $p->validador?->nombre ?? 'N/A' }}">Aprobado</span>
                                @endif
                                <br>
                                @if($p->asignacion_ct)
                                    <span style="background-color: #FFFF00; padding: 2px; border: 1px solid #CCC; font-size: 9px; color: #000;">Asig. C&T</span>
                                @endif
                                @if($p->calificacion)
                                    <br><span style="font-size: 9px;">Nota: {{ $p->calificacion }}</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                @if($p->estado_logico)
                                    <span style="color: #008000; font-weight: bold;">Activo</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                @endif
                            </td>
                            <td align="center" style="padding: 5px;">
                                <a href="#" wire:click.prevent="edit({{ $p->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-bottom: 2px; display: inline-block;">
                                    [Editar]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="toggleStatus({{ $p->id }})" title="{{ $p->estado_logico ? 'Deshabilitar' : 'Habilitar' }}" style="color: #0000EE; text-decoration: none; font-size: 10px; margin-bottom: 2px; display: inline-block;">
                                    [{{ $p->estado_logico ? 'Inhabilitar' : 'Habilitar' }}]
                                </a>
                                <br>
                                <a href="#" wire:click.prevent="delete({{ $p->id }})" wire:confirm="¿Desea eliminar este proyecto permanentemente?" title="Eliminar" style="color: #FF0000; text-decoration: none; font-size: 10px; display: inline-block;">
                                    [Eliminar]
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($proyectos->isEmpty())
                        <tr>
                            <td colspan="6" align="center" style="padding: 20px; font-weight: bold; background-color: #FFFFFF;">
                                No hay expedientes registrados
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            
            <div style="margin-top: 10px;">
                {{ $proyectos->links() }}
            </div>
        </fieldset>

    @else
        <!-- Formulario (Nueva Página) -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Actualizar Expediente' : 'Nuevo Registro de Proyecto' }}
            </legend>

            <form wire:submit="save" style="margin: 0;">
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Datos Principales</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Título del Proyecto:</b></td>
                            <td width="80%" colspan="3">
                                <input wire:model="titulo" type="text" style="width: 95%;">
                                <span class="obligatorio">*</span>
                                @error('titulo') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%" valign="top"><b>Resumen / Abstract:</b></td>
                            <td width="80%" colspan="3">
                                <textarea wire:model="resumen" rows="3" style="width: 95%;"></textarea>
                                <span class="obligatorio">*</span>
                                @error('resumen') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Fecha de Subida:</b></td>
                            <td width="30%">
                                <input wire:model="fecha_subida" type="date" style="width: 120px;">
                                <span class="obligatorio">*</span>
                                @error('fecha_subida') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Fecha Aprobación:</b></td>
                            <td width="30%">
                                <input wire:model="fecha_aprobacion" type="date" style="width: 120px;">
                                <span class="obligatorio">*</span>
                                @error('fecha_aprobacion') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Nota Final (1-20):</b></td>
                            <td width="30%">
                                <input wire:model="calificacion" type="number" min="1" max="20" style="width: 60px;">
                                <span class="obligatorio">*</span>
                                @error('calificacion') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Asignación C&T:</b></td>
                            <td width="30%">
                                <label><input type="checkbox" wire:model="asignacion_ct"> ¿Aplica?</label>
                                @error('asignacion_ct') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        @if(isset($componentes_requeridos) && count($componentes_requeridos) > 0)
                            <tr>
                                <td width="100%" colspan="4">
                                    <fieldset style="border: 1px dashed #CCC; padding: 10px; background-color: #fcfcfc;">
                                        <legend style="font-weight: bold; font-size: 11px; padding: 0 5px; color: #8b0000;">Requisitos Documentales Inteligentes (Según Coordinación y Año)</legend>
                                        <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                                            @foreach($componentes_requeridos as $comp)
                                                <tr>
                                                    <td width="30%">
                                                        <b>📁 {{ mb_strtoupper($comp->nombre) }}</b> 
                                                        @if($comp->es_obligatorio) <span class="obligatorio">*</span> @endif
                                                    </td>
                                                    <td width="70%">
                                                        <input type="file" wire:model="archivos_componentes.{{ $comp->id }}" style="font-size: 11px;">
                                                        @if(isset($archivos_actuales[$comp->id]))
                                                            <div style="font-size: 10px; margin-top: 5px; color: #008000; font-weight: bold;">(Ya subido. Selecciona otro para reemplazar)</div>
                                                        @endif
                                                        <div wire:loading wire:target="archivos_componentes.{{ $comp->id }}" style="font-size: 10px; color: #0000EE;">Preparando archivo para procesamiento de red...</div>
                                                        @error('archivos_componentes.'.$comp->id) <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </fieldset>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td colspan="4" align="center" style="color: #666; font-style: italic; font-size: 11px; border: 1px dashed #CCC; padding: 10px;">
                                    (Al seleccionar Coordinación y escribir el Trayecto/Año válido, se cargarán en este espacio los componentes documentales que dictaminen las reglas de tu coordinación)
                                </td>
                            </tr>
                        @endif
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Equipo de Proyecto y Comunidad</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Equipo del Proyecto:</b></td>
                            <td width="80%">
                                <select wire:model="equipo_id" style="width: 100%;">
                                    <option value="">Seleccione Equipo...</option>
                                    @foreach($equipos_disp as $eq) 
                                        <option value="{{ $eq->id }}">
                                            {{ mb_strtoupper($eq->nombre) }} - [COMUNIDAD: {{ mb_strtoupper($eq->comunidad->nombre ?? 'Sin Comunidad') }}]
                                        </option> 
                                    @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('equipo_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px;">
                    <legend style="font-weight: bold; font-size: 12px; padding: 0 5px;">Clasificación de la Investigación</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Lapso Académico:</b></td>
                            <td width="30%">
                                <select wire:model="lapso_academico_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($lapsos as $lap) <option value="{{ $lap->id }}">{{ $lap->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('lapso_academico_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Coordinación:</b></td>
                            <td width="30%">
                                <select wire:model.live="coordinacion_id" style="width: 90%;">
                                    <option value="">Seleccione Coordinación...</option>
                                    @foreach($coordinaciones as $coordinacion) <option value="{{ $coordinacion->id }}">{{ $coordinacion->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('coordinacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Trayecto / Año:</b></td>
                            <td width="80%" colspan="3">
                                <input wire:model.live="trayecto" type="text" placeholder="Ej: Trayecto I..." style="width: 85%;">
                                <span class="obligatorio">*</span>
                                @error('trayecto') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Línea de Inv.:</b></td>
                            <td width="30%">
                                <select wire:model="linea_investigacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($lineas as $l) <option value="{{ $l->id }}">{{ substr($l->nombre_investigacion, 0, 30) }}...</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('linea_investigacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Metodología Aplic.:</b></td>
                            <td width="30%">
                                <select wire:model="metodologia_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($metodologias as $m) <option value="{{ $m->id }}">{{ $m->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('metodologia_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                        <tr>
                            <td width="20%"><b>Tipo Publicación:</b></td>
                            <td width="30%">
                                <select wire:model="tipo_publicacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos_publicacion as $tp) <option value="{{ $tp->id }}">{{ $tp->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('tipo_publicacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                            <td width="20%"><b>Tipo Investigación:</b></td>
                            <td width="30%">
                                <select wire:model="tipo_investigacion_id" style="width: 90%;">
                                    <option value="">Seleccione...</option>
                                    @foreach($tipos_investigacion as $ti) <option value="{{ $ti->id }}">{{ $ti->nombre }}</option> @endforeach
                                </select>
                                <span class="obligatorio">*</span>
                                @error('tipo_investigacion_id') <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span> @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <div style="margin-top: 15px; font-size: 11px;">
                    Los campos con <span class="obligatorio">*</span> son obligatorios
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px; margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 26px;">{{ $editingId ? 'Guardar Cambios' : 'Finalizar Registro' }}</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
