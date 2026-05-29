<div class="pgm-wrap">
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Proyectos</h2>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border: 1px solid #c3e6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('message_error'))
        <div
            style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border: 1px solid #f5c6cb; border-radius: 4px; font-weight: bold; text-align: center;">
            {{ session('message_error') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <div class="pgm-action-bar">
            @if ($canRegister ?? false)
                <div>
                    <button type="button" wire:click="iniciarRegistro" class="boton"
                        style="background-color: #28a745; color: #fff; border: 1px solid #218838; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold;">
                        + REGISTRAR NUEVO PROYECTO
                    </button>
                </div>
            @else
                <span class="pgm-aviso" style="font-weight: bold;">
                    Registro no disponible: se requiere inscripción activa en una sección del lapso académico
                    (intranet).
                </span>
            @endif
        </div>

        @if (!empty($canValidate))
            <div class="pgm-tabs" style="margin-bottom: 12px; font-size: 11px;">
                <button type="button" wire:click="irAListado('gestion')"
                    style="border: 1px solid #999; border-radius: 4px; padding: 4px 12px; margin-right: 6px; {{ $listTab === 'gestion' ? 'background:#8bb2b7;font-weight:bold;' : 'background:#f0f0f0;' }}">
                    Listado general
                </button>
                <button type="button" wire:click="irAListado('validar')"
                    style="border: 1px solid #999; border-radius: 4px; padding: 4px 12px; {{ $listTab === 'validar' ? 'background:#8bb2b7;font-weight:bold;' : 'background:#f0f0f0;' }}">
                    Validar pendientes
                </button>
            </div>
        @endif

        @if ($listTab === 'validar')
            <div style="margin-bottom: 15px;">
                <b>Búsqueda (título):</b>
                <input wire:model.live="search" type="text" style="width: 250px;" placeholder="...">
            </div>

            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Revisión de
                    expedientes pendientes</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                            <th width="35%">Título / resumen</th>
                            <th width="20%">Equipo / comunidad</th>
                            <th width="20%">Documentos</th>
                            <th width="25%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($proyectos as $p)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                                valign="top">
                                <td style="padding: 5px;">
                                    <span style="font-weight: bold;">{{ mb_strtoupper($p->titulo) }}</span><br>
                                    <span
                                        style="font-size: 10px; color: #555;">{{ Str::limit($p->resumen, 60) }}</span><br>
                                    <span style="font-size: 9px; color: #888;">Registrado:
                                        {{ $p->created_at->format('d/m/Y') }}</span>
                                </td>
                                <td align="center" style="padding: 5px; font-size: 10px;">
                                    {{ $p->equipo_resumen }}<br>
                                    {{ $p->comunidad->nombre ?? 'N/A' }}
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if (count($p->documentos ?? []))
                                        @foreach ($p->documentos as $doc)
                                            <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}"
                                                target="_blank"
                                                style="color: #0000EE; font-size: 10px; display:block;">[{{ mb_strtoupper(data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'DOC'))) }}]</a>
                                        @endforeach
                                    @elseif($p->archivo_path)
                                        <a href="{{ Storage::url($p->archivo_path) }}" target="_blank"
                                            style="color: #0000EE; font-size: 10px;">[Ver PDF]</a>
                                    @else
                                        <span style="color: #999;">Sin archivos</span>
                                    @endif
                                    <br>
                                    <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                        style="color: #0000EE; font-size: 10px;">[Ficha técnica]</a>
                                </td>
                                <td align="center" style="padding: 5px;">
                                    <button type="button" wire:click="approve({{ $p->id }})"
                                        onclick="return confirm('¿Aprueba este proyecto?')"
                                        style="border: 1px solid #008000; border-radius: 4px; padding: 2px 10px; font-weight: bold; background-color: #d4edda; color: #155724; cursor: pointer; display: block; width: 90%; margin: 0 auto 5px auto; font-size: 10px;">
                                        Aprobar
                                    </button>
                                    <button type="button" wire:click="openReject({{ $p->id }})"
                                        style="border: 1px solid #FF0000; border-radius: 4px; padding: 2px 10px; font-weight: bold; background-color: #f8d7da; color: #721c24; cursor: pointer; display: block; width: 90%; margin: 0 auto; font-size: 10px;">
                                        Rechazar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        @if ($proyectos->isEmpty())
                            <tr>
                                <td colspan="4" align="center" style="padding: 20px; font-weight: bold;">No hay
                                    expedientes pendientes de revisión.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
            </fieldset>
        @else
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px;">Filtros</legend>
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                    <tr>
                        <td width="33%"><b>Título:</b><br>
                            <input wire:model.live="search" type="text" style="width: 95%;" placeholder="Buscar...">
                        </td>
                        <td width="33%"><b>Estado validación:</b><br>
                            <select wire:model.live="filterEstadoList" style="width: 95%;">
                                <option value="">- Todos -</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                            </select>
                        </td>
                        <td width="34%"><b>Comunidad:</b><br>
                            <select wire:model.live="filterComunidadList" style="width: 95%;">
                                <option value="">- Todas -</option>
                                @foreach ($comunidades as $com)
                                    <option value="{{ $com->id }}">{{ $com->nombre }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                </table>
            </fieldset>

            <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin: 0;">
                <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de proyectos
                    institucionales</legend>
                <table width="100%" border="1" cellpadding="4" cellspacing="0"
                    style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
                    <thead>
                        <tr style="background-color: #8bb2b7; color: #000; text-align: center; font-weight: bold;">
                            <th width="25%">Título del proyecto</th>
                            <th width="20%">Comunidad / línea inv.</th>
                            <th width="15%">Validación / C&amp;T</th>
                            <th width="10%">Estado</th>
                            <th width="30%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="Texto">
                        @foreach ($proyectos as $p)
                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }}; {{ !$p->estado_logico ? 'color: #888;' : 'color: #000;' }}"
                                valign="top">
                                <td style="padding: 5px; font-weight: bold;">
                                    {{ $p->titulo }}
                                    <br><span style="font-size: 9px; font-weight: normal;">Subido:
                                        {{ $p->fecha_subida?->format('d/m/Y') ?? '-' }}</span>
                                    @if (count($p->documentos ?? []))
                                        <div style="margin-top: 5px;">
                                            @foreach ($p->documentos as $doc)
                                                <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}"
                                                    target="_blank"
                                                    style="color: #0000EE; font-size: 10px; display:block;">[{{ mb_strtoupper(data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'DOC'))) }}]</a>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 5px;">
                                    <span style="font-size: 11px; font-weight: bold; color: #8b0000;">Equipo:
                                        {{ $p->equipo_resumen }}</span><br>
                                    <span style="font-size: 10px;">Comunidad:
                                        {{ $p->comunidad->nombre ?? 'N/A' }}</span><br>
                                    <span style="font-size: 10px;">Línea:
                                        {{ $p->linea_investigacion->nombre_investigacion ?? '' }}</span>
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_validacion === 'pendiente')
                                        <span style="color: #d4a017; font-weight: bold;">En revisión</span>
                                    @elseif($p->estado_validacion === 'rechazado')
                                        <span style="color: #FF0000; font-weight: bold;"
                                            title="{{ $p->motivo_rechazo }}">Rechazado</span>
                                    @else
                                        <span style="color: #008000; font-weight: bold;">Aprobado</span>
                                    @endif
                                    @if ($p->asignacion_ct)
                                        <br><span
                                            style="background-color: #FFFF00; padding: 2px; border: 1px solid #CCC; font-size: 9px;">Asig.
                                            C&amp;T</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px;">
                                    @if ($p->estado_logico)
                                        <span style="color: #008000; font-weight: bold;">Activo</span>
                                    @else
                                        <span style="color: #FF0000; font-weight: bold;">Inactivo</span>
                                    @endif
                                </td>
                                <td align="center" style="padding: 5px; font-size: 10px;">
                                    @if (!empty($canValidate) && $p->estado_validacion === 'pendiente')
                                        <button type="button" wire:click="approve({{ $p->id }})"
                                            onclick="return confirm('¿Aprueba este proyecto?')"
                                            style="font-size: 10px; margin-bottom: 3px;">[Aprobar]</button><br>
                                        <a href="#" wire:click.prevent="openReject({{ $p->id }})"
                                            style="color: #FF0000;">[Rechazar]</a><br>
                                        <a href="#" wire:click.prevent="openDetails({{ $p->id }})"
                                            style="color: #0000EE;">[Ficha]</a><br>
                                    @endif
                                    <a href="#" wire:click.prevent="edit({{ $p->id }})"
                                        style="color: #0000EE;">[Editar]</a><br>
                                    <a href="#" wire:click.prevent="toggleStatus({{ $p->id }})"
                                        style="color: #0000EE;">[{{ $p->estado_logico ? 'Inhabilitar' : 'Habilitar' }}]</a><br>
                                    <a href="#" wire:click.prevent="delete({{ $p->id }})"
                                        wire:confirm="¿Eliminar este proyecto permanentemente?"
                                        style="color: #FF0000;">[Eliminar]</a>
                                </td>
                            </tr>
                        @endforeach
                        @if ($proyectos->isEmpty())
                            <tr>
                                <td colspan="5" align="center" style="padding: 20px; font-weight: bold;">No hay
                                    expedientes registrados</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                <div style="margin-top: 10px;">{{ $proyectos->links() }}</div>
            </fieldset>
        @endif
    @elseif($viewMode === 'reject')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Motivo de rechazo
            </legend>
            <div style="margin-bottom: 15px; font-size: 12px;">Indique la justificación para no aprobar el expediente:
            </div>
            <textarea wire:model="motivo_rechazo" rows="6" style="width: 100%; max-width: 600px; padding: 5px;"></textarea>
            @error('motivo_rechazo')
                <div class="obligatorio" style="font-size: 11px; margin-top: 5px;">{{ $message }}</div>
            @enderror
            <div style="margin-top: 20px;">
                <button type="button" wire:click="irAListado('{{ $listTab }}')" class="boton"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="confirmReject" class="boton"
                    style="background-color: #f8d7da; color: #721c24; font-weight: bold;">Confirmar rechazo</button>
            </div>
        </fieldset>
    @elseif($viewMode === 'details' && $selectedProject)
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Ficha técnica del
                proyecto</legend>
            <h3 style="margin: 5px 0; font-size: 16px; font-weight: bold;">{{ $selectedProject->titulo }}</h3>
            <p style="font-size: 13px;"><b>Equipo:</b> {{ $selectedProject->equipo_resumen }}</p>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin: 15px 0;">
                <legend style="font-weight: bold; font-size: 12px;">Resumen</legend>
                <div style="font-size: 14px; text-align: justify;">{{ $selectedProject->resumen }}</div>
            </fieldset>
            <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 13px;">
                <tr>
                    <td width="30%"><b>Publicación:</b></td>
                    <td>{{ $selectedProject->tipo_publicacion->nombre ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>Investigación:</b></td>
                    <td>{{ $selectedProject->tipo_investigacion->nombre ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>Metodología:</b></td>
                    <td>{{ $selectedProject->metodologia->nombre ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>Línea inv.:</b></td>
                    <td>{{ $selectedProject->linea_investigacion->nombre_investigacion ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>Comunidad:</b></td>
                    <td>{{ $selectedProject->comunidad->nombre ?? '-' }}</td>
                </tr>
            </table>
            @if (count($selectedProject->documentos ?? []))
                <div style="margin-top: 10px; font-size: 13px;">
                    <b>Documentos:</b><br>
                    @foreach ($selectedProject->documentos as $doc)
                        <a href="{{ Storage::url(data_get($doc, 'archivo_path')) }}" target="_blank"
                            style="color: #0000EE;">[{{ data_get($doc, 'componente.nombre', data_get($doc, 'componente_nombre', 'Documento')) }}]</a><br>
                    @endforeach
                </div>
            @endif
            <div style="text-align: center; margin-top: 20px; border-top: 1px solid #CCC; padding-top: 15px;">
                @if ($selectedProject->estado_validacion === 'pendiente')
                    <button type="button" wire:click="approveFromDetails({{ $selectedProject->id }})"
                        onclick="return confirm('¿Aprueba este proyecto?')" class="boton"
                        style="background-color: #d4edda; color: #155724; font-weight: bold; margin-right: 10px;">Aprobar</button>
                    <button type="button" wire:click="rejectFromDetails({{ $selectedProject->id }})" class="boton"
                        style="background-color: #f8d7da; color: #721c24; font-weight: bold; margin-right: 10px;">Rechazar</button>
                @endif
                <button type="button" wire:click="irAListado('{{ $listTab }}')" class="boton">Regresar al
                    listado</button>
            </div>
        </fieldset>
    @elseif($viewMode === 'form')
        <button type="button" wire:click="cancel" class="pgm-btn-volver">&laquo; Volver al listado</button>

        @if (!empty($catalogosVacios))
            <div
                style="background-color: #fff3cd; color: #856404; padding: 10px; margin: 12px 0; border: 1px solid #ffeeba; border-radius: 4px; font-size: 11px;">
                <b>Catálogos sin datos en repositorio:</b> {{ implode(', ', $catalogosVacios) }}.
                Un administrador debe cargarlos antes de poder guardar el expediente (los desplegables quedarán vacíos).
            </div>
        @endif

        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 20px; background-color: #FFF;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Actualizar expediente' : 'Nuevo registro de proyecto' }}
            </legend>
            <form wire:submit="save">
                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Datos principales</legend>
                    <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Título:</b></td>
                            <td colspan="3"><input wire:model="titulo" type="text" style="width: 95%;"><span
                                    class="obligatorio">*</span>
                                @error('titulo')
                                    <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Resumen:</b></td>
                            <td colspan="3">
                                <textarea wire:model="resumen" rows="3" style="width: 95%;"></textarea><span class="obligatorio">*</span>
                                @error('resumen')
                                    <br><span class="obligatorio" style="font-size: 11px;">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Fecha subida:</b></td>
                            <td colspan="3"><input wire:model="fecha_subida" type="date"><span
                                    class="obligatorio">*</span> @error('fecha_subida')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td valign="top"><b>Documento PDF:</b></td>
                            <td colspan="3">
                                <input type="file" wire:model="archivo_proyecto" accept=".pdf,application/pdf">
                                @error('archivo_proyecto')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                                @if ($archivo_actual)
                                    <div style="font-size: 10px; margin-top: 4px;"><a
                                            href="{{ Storage::url($archivo_actual) }}" target="_blank"
                                            style="color:#0000EE;">Ver PDF actual</a></div>
                                @endif
                                <div wire:loading wire:target="archivo_proyecto"
                                    style="font-size:10px;color:#0000EE;">Cargando archivo...</div>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Asignación C&amp;T:</b></td>
                            <td><label><input type="checkbox" wire:model="asignacion_ct"> ¿Aplica?</label></td>
                            @if ($editingId)
                                <td><b>Nota (1-20):</b></td>
                                <td><input wire:model="calificacion" type="number" min="1" max="20"
                                        style="width: 60px;"> @error('calificacion')
                                        <span class="obligatorio">{{ $message }}</span>
                                    @enderror
                                </td>
                            @endif
                        </tr>
                        @if ($editingId)
                            <tr>
                                <td><b>Fecha aprobación:</b></td>
                                <td colspan="3"><input wire:model="fecha_aprobacion" type="date">
                                    @error('fecha_aprobacion')
                                        <span class="obligatorio">{{ $message }}</span>
                                    @enderror
                                </td>
                            </tr>
                        @endif
                        @if (($usaComponentes ?? false) && isset($componentes_requeridos) && count($componentes_requeridos) > 0)
                            <tr>
                                <td colspan="4">
                                    <fieldset style="border: 1px dashed #CCC; padding: 10px;">
                                        <legend style="font-weight: bold; font-size: 11px; color: #8b0000;">Requisitos
                                            documentales</legend>
                                        @foreach ($componentes_requeridos as $comp)
                                            <div style="margin-bottom: 8px; font-size: 11px;">
                                                <b>{{ mb_strtoupper($comp->nombre) }}</b>
                                                @if ($comp->es_obligatorio)
                                                    <span class="obligatorio">*</span>
                                                @endif
                                                <input type="file"
                                                    wire:model="archivos_componentes.{{ $comp->id }}">
                                                @if (isset($archivos_actuales[$comp->id]))
                                                    <span style="font-size: 10px; color: #008000;">(Ya subido)</span>
                                                @endif
                                                @error('archivos_componentes.' . $comp->id)
                                                    <span class="obligatorio">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </fieldset>
                                </td>
                            </tr>
                        @endif
                    </table>
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size: 12px;">Equipo (sección intranet) y comunidad</legend>
                    @if ($esAdmin ?? false)
                        <p style="font-size: 10px; color: #555;">Seleccione primero el <b>lapso</b> para cargar
                            secciones/equipos (evita listas enormes).</p>
                        <select wire:model.live="filterLapsoEquipo" style="width: 32%; margin-bottom: 6px;">
                            <option value="">- Lapso -</option>
                            @foreach ($lapsos as $lap)
                                <option value="{{ $lap->id }}">{{ $lap->nombre }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterProgramaEquipo" style="width: 32%;"
                            @disabled(!$filterLapsoEquipo)>
                            <option value="">- Programa -</option>
                            @foreach ($programasEquipo as $pro)
                                <option value="{{ $pro->pro_codigo }}">{{ trim($pro->pro_siglas) }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="filterSeccionEquipo" style="width: 32%;"
                            @disabled(!$filterLapsoEquipo)>
                            <option value="">- Sección -</option>
                            @foreach ($seccionesEquipo as $sec)
                                <option value="{{ $sec->sec_codigo }}">{{ trim($sec->sec_nombre) }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select wire:model.live="equipo_seccion_clave" style="width: 100%; margin-top: 6px;">
                        <option value="">Seleccione grupo de proyecto o sección…</option>
                        @foreach ($equipos_disp ?? [] as $eq)
                            <option value="{{ $eq->clave }}">
                                {{ $eq->nombre ?? $eq->clave }}
                                @if (!empty($eq->es_grupo_registrado))
                                    [grupo de proyecto]
                                @endif
                                @if (!empty($eq->lapso_nombre))
                                    - {{ $eq->lapso_nombre }}
                                @endif
                                ({{ $eq->integrantes ?? '?' }} int.)
                            </option>
                        @endforeach
                    </select>
                    <span class="obligatorio">*</span>
                    @error('equipo_seccion_clave')
                        <span class="obligatorio">{{ $message }}</span>
                    @enderror
                    @if (!empty($equipoValidado))
                        <div style="margin-top: 6px; padding: 6px; background: #d4edda; font-size: 10px;">
                            <b>Validado:</b> {{ $equipoValidado->nombre }}
                            ({{ ($integrantesEquipo ?? collect())->count() }} integrantes)
                        </div>
                    @endif
                    <br><br>
                    <b>Comunidad:</b>
                    <select wire:model="comunidad_id" style="width: 100%;">
                        <option value="">Seleccione...</option>
                        @foreach ($comunidades as $com)
                            <option value="{{ $com->id }}">{{ mb_strtoupper($com->nombre) }}</option>
                        @endforeach
                    </select>
                    <span class="obligatorio">*</span>
                    @error('comunidad_id')
                        <span class="obligatorio">{{ $message }}</span>
                    @enderror
                </fieldset>

                <fieldset style="border: 1px solid #CCC; padding: 10px;">
                    <legend style="font-weight: bold; font-size: 12px;">Clasificación (catálogo repositorio)</legend>
                    <p style="font-size: 10px; color: #555; margin: 0 0 8px 0;">El <b>equipo</b> es el grupo de
                        proyecto encapsulado; la clave <code>EQSEC:lapso:sección</code> se guarda en
                        <b>pry_direccion_logica</b>.
                    </p>
                    <table width="100%" cellpadding="4" cellspacing="0" style="font-size: 12px;">
                        <tr>
                            <td width="20%"><b>Línea inv.:</b></td>
                            <td width="30%"><select wire:model="linea_investigacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($lineas as $l)
                                        <option value="{{ $l->id }}">
                                            {{ Str::limit($l->nombre_investigacion, 40) }}</option>
                                    @endforeach
                                </select>
                                <span class="obligatorio">*</span> @error('linea_investigacion_id')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td width="20%"><b>Metodología:</b></td>
                            <td width="30%"><select wire:model="metodologia_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($metodologias as $m)
                                        <option value="{{ $m->id }}">{{ $m->nombre }}</option>
                                    @endforeach
                                </select>
                                <span class="obligatorio">*</span> @error('metodologia_id')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Tipo publicación:</b></td>
                            <td><select wire:model="tipo_publicacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipos_publicacion as $tp)
                                        <option value="{{ $tp->id }}">{{ $tp->nombre }}</option>
                                    @endforeach
                                </select>
                                <span class="obligatorio">*</span> @error('tipo_publicacion_id')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                            <td><b>Tipo investigación:</b></td>
                            <td><select wire:model="tipo_investigacion_id" style="width: 95%;">
                                    <option value="">Seleccione...</option>
                                    @foreach ($tipos_investigacion as $ti)
                                        <option value="{{ $ti->id }}">{{ $ti->nombre }}</option>
                                    @endforeach
                                </select>
                                <span class="obligatorio">*</span> @error('tipo_investigacion_id')
                                    <span class="obligatorio">{{ $message }}</span>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="button" wire:click="cancel" class="boton"
                        style="margin-right: 10px;">Cancelar</button>
                    <button type="submit" class="boton"
                        style="background-color: #28a745; color: #fff; border: 1px solid #218838; border-radius: 4px; padding: 6px 12px; font-size: 12px; font-weight: bold;">{{ $editingId ? 'Guardar cambios' : 'Registrar proyecto' }}</button>
                </div>
            </form>
        </fieldset>
    @endif
</div>
