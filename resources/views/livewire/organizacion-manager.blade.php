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
        .cm-btn-danger {
            background: #c82333;
            border-color: #a71d2a;
            color: #fff;
        }
        .cm-btn-secondary {
            background: #f4f4f4;
            border: 1px solid #c2c2c2;
            color: #222;
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
        .cm-btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
        }
    </style>

    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gesti&oacute;n de Organizaciones</h2>

    @if($mensaje)
        <div style="background-color: {{ $tipoMensaje === 'success' ? '#d4edda' : '#f8d7da' }}; color: {{ $tipoMensaje === 'success' ? '#155724' : '#721c24' }}; border: 1px solid {{ $tipoMensaje === 'success' ? '#c3e6cb' : '#f5c6cb' }}; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px; display: flex; justify-content: space-between; align-items: center;">
            <span>{{ $mensaje }}</span>
            <a href="#" wire:click.prevent="limpiarMensaje" style="font-size: 16px; font-weight: bold; text-decoration: none; color: inherit;">&times;</a>
        </div>
    @endif

    @if($accesoDenegado)
        <p style="color:red; font-weight:bold; text-align:center; margin-top:30px;">
            Acceso restringido. Solo el Gestionador puede administrar organizaciones.
        </p>
    @elseif($orgSeleccionadaNombre)
        {{-- ═══════════════════════════════════════════════════
             PANEL DEPARTAMENTOS
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="font-weight: bold; font-style: italic; padding: 0 5px;">
                Departamentos de: <strong>{{ $orgSeleccionadaNombre }}</strong>
            </legend>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <button type="button" wire:click="cerrarDepartamentos" class="cm-btn cm-btn-secondary cm-btn-sm">
                    &larr; Volver
                </button>
                <button type="button" wire:click="abrirFormNuevoDep" class="cm-btn cm-btn-success cm-btn-sm">
                    + Nuevo Departamento
                </button>
            </div>

            @if($mostrarFormDep)
                <fieldset style="border: 1px solid #ccc; border-radius: 4px; padding: 10px; margin-bottom: 15px;">
                    <legend style="font-weight: bold; font-size:12px; padding: 0 5px;">
                        {{ $editandoDepId ? 'Editar Departamento' : 'Registrar Departamento' }}
                    </legend>
                    <table width="100%" border="0" cellpadding="5" cellspacing="0" style="font-size: 11px;">
                        <tr>
                            <td width="15%"><b>Nombre:</b></td>
                            <td width="35%">
                                <input type="text" wire:model="dep_nombre" style="width: 90%;"> <span class="obligatorio">*</span>
                                @error('dep_nombre')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                            </td>
                            <td width="15%"><b>Cargo:</b></td>
                            <td width="35%">
                                <input type="text" wire:model="dep_cargo" style="width: 90%;">
                                @error('dep_cargo')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                            </td>
                        </tr>
                        <tr>
                            <td><b>Contacto:</b></td>
                            <td colspan="3">
                                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                                    <span><label style="font-size:11px; margin-right:3px;">Nombre</label>
                                    <input type="text" wire:model="dep_nombre_contacto" style="width:130px;"></span>
                                    <span><label style="font-size:11px; margin-right:3px;">Apellido</label>
                                    <input type="text" wire:model="dep_apellido_contacto" style="width:130px;"></span>
                                    <span><label style="font-size:11px; margin-right:3px;">Tel&eacute;fono</label>
                                    <input type="text" wire:model="dep_numero_contacto" style="width:100px;"></span>
                                </div>
                            </td>
                        </tr>
                    </table>
                    <div style="margin-top: 12px; text-align: center;">
                        <button type="button" wire:click="cancelarFormDep" class="cm-btn cm-btn-danger cm-btn-sm" style="margin-right: 8px;">Cancelar</button>
                        <button type="button" wire:click="guardarDep" class="cm-btn cm-btn-primary cm-btn-sm">
                            {{ $editandoDepId ? 'Actualizar' : 'Guardar' }}
                        </button>
                    </div>
                </fieldset>
            @endif

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="30%">Nombre</th>
                        <th width="20%">Cargo</th>
                        <th width="30%">Contacto</th>
                        <th width="15%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @forelse($deps as $dep)
                        @php
                            $contactoDep = trim(($dep->nombre_contacto ?? '') . ' ' . ($dep->apellido_contacto ?? ''));
                            if ($dep->numero_contacto) { $contactoDep .= ($contactoDep ? ' &middot; ' : '') . $dep->numero_contacto; }
                        @endphp
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>{{ $dep->nombre }}</td>
                            <td>{{ $dep->cargo ?? '-' }}</td>
                            <td>{{ $contactoDep ?: '-' }}</td>
                            <td align="center">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="editarDep({{ $dep->id }})"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="eliminarDep({{ $dep->id }})"
                                        wire:confirm="&iquest;Eliminar este departamento?"
                                        class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay departamentos registrados para esta organizaci&oacute;n.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </fieldset>
    @elseif($mostrarFormOrg)
        {{-- ═══════════════════════════════════════════════════
             FORMULARIO REGISTRO/EDICIÓN (sin tabla)
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $org_nombre_key ? 'Editar Organización' : 'Registrar Organización' }}
            </legend>
            <table width="100%" border="0" cellpadding="5" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="15%"><b>Nombre:</b></td>
                    <td width="35%">
                        <input type="text" wire:model="org_nombre" style="width: 90%;"> <span class="obligatorio">*</span>
                        @error('org_nombre')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                    <td width="15%"><b>RIF:</b></td>
                    <td width="35%">
                        <input type="text" wire:model="org_rif" style="width: 90%;">
                        @error('org_rif')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Correo:</b></td>
                    <td>
                        <input type="email" wire:model="org_correo" style="width: 90%;">
                        @error('org_correo')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                    <td><b>Cargo:</b></td>
                    <td>
                        <input type="text" wire:model="org_cargo" style="width: 90%;">
                        @error('org_cargo')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Direcci&oacute;n:</b></td>
                    <td colspan="3">
                        <textarea wire:model="org_direccion" rows="2" style="width: 95%; height: 50px;"></textarea>
                        @error('org_direccion')<br><span style="color:red;font-size:10px;">{{ $message }}</span>@enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Contacto:</b></td>
                    <td colspan="3">
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <span><label style="font-size:11px; margin-right:3px;">Nombre</label>
                            <input type="text" wire:model="org_nombre_contacto" style="width:130px;"></span>
                            <span><label style="font-size:11px; margin-right:3px;">Apellido</label>
                            <input type="text" wire:model="org_apellido_contacto" style="width:130px;"></span>
                            <span><label style="font-size:11px; margin-right:3px;">Tel&eacute;fono</label>
                            <input type="text" wire:model="org_numero_contacto" style="width:100px;"></span>
                        </div>
                    </td>
                </tr>
            </table>

            {{-- Acordeón de departamentos --}}
            @php
                $totalDeps = collect($departamentosForm)->filter(fn($d) => !($d['is_deleted'] ?? false))->count();
            @endphp
            <div style="margin-top: 12px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="button" wire:click="abrirModalDeps"
                    style="width:100%; background:{{ $mostrarModalDeps ? '#e8e8e8' : '#f5f5f5' }}; border:none; padding:8px 12px; cursor:pointer; text-align:left; font-weight:bold; font-size:12px; display:flex; justify-content:space-between; align-items:center;">
                    <span>{{ $totalDeps > 0 ? 'Departamentos (' . $totalDeps . ')' : '+ Agregar Departamentos' }}</span>
                    <span style="font-size:14px;">{{ $mostrarModalDeps ? '▲' : '▼' }}</span>
                </button>
                @if($mostrarModalDeps)
                    <div style="padding:10px; border-top:1px solid #ccc;">
                        <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap; background: #f9f9f9; padding: 8px; border-radius: 4px; margin-bottom: 8px;">
                            <input type="text" wire:model="nuevo_dep_nombre" placeholder="Nombre depto *" style="width: 130px; padding: 4px; border:1px solid #ccc; border-radius:3px;">
                            <input type="text" wire:model="nuevo_dep_cargo" placeholder="Cargo" style="width: 110px; padding: 4px; border:1px solid #ccc; border-radius:3px;">
                            <input type="text" wire:model="nuevo_dep_nombre_contacto" placeholder="Contacto nombre" style="width: 110px; padding: 4px; border:1px solid #ccc; border-radius:3px;">
                            <input type="text" wire:model="nuevo_dep_apellido_contacto" placeholder="Apellido" style="width: 110px; padding: 4px; border:1px solid #ccc; border-radius:3px;">
                            <input type="text" wire:model="nuevo_dep_numero_contacto" placeholder="Tel&eacute;fono" style="width: 90px; padding: 4px; border:1px solid #ccc; border-radius:3px;">
                            <button type="button" wire:click="agregarDepartamentoFila" class="cm-btn cm-btn-primary cm-btn-sm">+ A&ntilde;adir</button>
                        </div>
                        @error('nuevo_dep_nombre')<div style="color:red;font-size:10px;margin-bottom:5px;">{{ $message }}</div>@enderror
                        @error('nuevo_dep_cargo')<div style="color:red;font-size:10px;margin-bottom:5px;">{{ $message }}</div>@enderror

                        @php
                            $depsVisibles = collect($departamentosForm)->filter(fn($d) => !($d['is_deleted'] ?? false));
                        @endphp

                        @if($depsVisibles->isEmpty())
                            <p style="color:#777; font-size:11px; font-style:italic; text-align:center; padding:10px 0;">No se han agregado departamentos.</p>
                        @else
                            <table width="100%" border="1" cellpadding="4" cellspacing="0"
                                style="border-collapse:collapse; border-color:#bbb; font-size:10px;">
                                <thead>
                                    <tr style="background:#8bb2b7; font-weight:bold;">
                                        <th align="left" width="5%">N&deg;</th>
                                        <th align="left">Nombre</th>
                                        <th align="left">Cargo</th>
                                        <th align="left">Contacto</th>
                                        <th align="center" width="60">Acci&oacute;n</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departamentosForm as $index => $d)
                                        @if(!($d['is_deleted'] ?? false))
                                            @php
                                                $contactoDepForm = trim(($d['nombre_contacto'] ?? '') . ' ' . ($d['apellido_contacto'] ?? ''));
                                                if (!empty($d['numero_contacto'])) { $contactoDepForm .= ($contactoDepForm ? ' &middot; ' : '') . $d['numero_contacto']; }
                                            @endphp
                                            <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};">
                                                <td align="center">{{ $loop->iteration }}</td>
                                                <td>{{ $d['nombre'] }}</td>
                                                <td>{{ $d['cargo'] ?: '-' }}</td>
                                                <td>{{ $contactoDepForm ?: '-' }}</td>
                                                <td align="center">
                                                    <button type="button" wire:click="removerDepartamentoFila({{ $index }})"
                                                        class="cm-btn cm-btn-danger cm-btn-sm" style="font-size:10px;">Remover</button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                @endif
            </div>

            <div style="margin-top: 15px; text-align: center;">
                <button type="button" wire:click="cancelarFormOrg" class="cm-btn cm-btn-danger" style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="guardarOrg" class="cm-btn cm-btn-primary">
                    {{ $org_nombre_key ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </fieldset>
    @else
        {{-- ═══════════════════════════════════════════════════
             LISTADO DE ORGANIZACIONES
             ═══════════════════════════════════════════════════ --}}
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Listado de Organizaciones</legend>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div>
                    <b>Buscar:</b>
                    <input wire:model.live.debounce.300ms="busqueda" type="text"
                        style="width: 300px; padding: 3px 6px; border-radius: 4px; border: 1px solid #999;"
                        placeholder="Nombre de la organizaci&oacute;n...">
                </div>
                <button type="button" wire:click="abrirFormNuevaOrg" class="cm-btn cm-btn-success" style="font-size: 14px; padding: 8px 18px;">
                    + Nueva Organizaci&oacute;n
                </button>
            </div>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N&deg;</th>
                        <th width="20%">Nombre</th>
                        <th width="10%">RIF</th>
                        <th width="10%">Cargo</th>
                        <th width="30%">Direcci&oacute;n</th>
                        <th width="15%">Contacto</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @forelse($organizaciones as $org)
                        @php
                            $contactoOrg = trim(($org->nombre_contacto ?? '') . ' ' . ($org->apellido_contacto ?? ''));
                            if ($org->numero_contacto) { $contactoOrg .= ($contactoOrg ? ' &middot; ' : '') . $org->numero_contacto; }
                        @endphp
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>{{ $org->nombre }}</td>
                            <td>{{ $org->rif ?? '-' }}</td>
                            <td>{{ $org->cargo ?? '-' }}</td>
                            <td>{{ $org->direccion ?? '-' }}</td>
                            <td>{{ $contactoOrg ?: '-' }}</td>
                            <td align="center">
                                <div style="display: inline-flex; align-items: center; gap: 4px;">
                                    <button type="button" wire:click.prevent="editarOrg('{{ addslashes($org->nombre) }}')"
                                        class="cm-btn cm-btn-secondary cm-btn-sm">Editar</button>
                                    <button type="button" wire:click.prevent="eliminarOrg('{{ addslashes($org->nombre) }}')"
                                        wire:confirm="&iquest;Eliminar esta organizaci&oacute;n y todos sus departamentos?"
                                        class="cm-btn cm-btn-danger cm-btn-sm">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" align="center" style="padding: 20px;">No hay organizaciones registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </fieldset>
    @endif
</div>