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
            background: #8b0000;
            border-color: #6d0000;
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
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Comunidades</h2>

    <p style="font-size: 10px; color: #555; margin-bottom: 12px;">
        Datos en tabla <b>comunidades</b> del repositorio. Los contactos por cargo (autoridad / personal vinculado) se
        registran en el campo dirección hasta existir tablas dedicadas.
        @if ($lapsoVigente)
            Lapso vigente intranet: <b>{{ $lapsoVigente->lap_nombre }}</b>.
        @endif
    </p>

    @if (session()->has('message'))
        <div
            style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('message_error'))
        <div
            style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message_error') }}
        </div>
    @endif

    @if ($viewMode === 'list')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y listado
            </legend>
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="30%"><b>Buscar (nombre / RIF / dirección):</b></td>
                    <td width="50%">
                        <input wire:model.live="search" type="text" style="width: 90%; padding: 3px;"
                            placeholder="...">
                    </td>
                    <td width="20%" align="right">
                        @if ($puedeGestionar)
                            <button type="button" wire:click="create" class="cm-btn cm-btn-danger cm-btn-sm"
                                style="min-width: 170px;">
                                Registrar nueva comunidad
                            </button>
                        @endif
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellpadding="5" cellspacing="0"
                style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N°</th>
                        <th width="35%">Comunidad / dirección</th>
                        <th width="15%">RIF</th>
                        <th width="25%">Contacto</th>
                        <th width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach ($comunidades as $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};"
                            valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td>
                                <span style="font-weight: bold;">{{ $c->nombre }}</span>
                                @if ($c->anio)
                                    <br><span style="font-size: 9px; color: #8b0000;">[{{ $c->anio }}]</span>
                                @endif
                                <br><span style="font-size: 9px; color: #555;">{{ $c->direccion }}</span>
                            </td>
                            <td align="center">{{ $c->rif }}</td>
                            <td align="center">{{ $c->correo }}<br><b>{{ $c->numero_telefono }}</b></td>
                            <td align="center">
                                @if ($puedeGestionar)
                                    <button type="button" wire:click.prevent="edit({{ $c->id }})"
                                        class="cm-btn cm-btn-secondary cm-btn-sm" wire:loading.attr="disabled"
                                        wire:target="edit">
                                        Editar
                                    </button>
                                @else
                                    <span style="color: #888; font-size: 10px;">Solo lectura</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if ($comunidades->isEmpty())
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay comunidades registradas.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $comunidades->links() }}</div>
        </fieldset>
    @else
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
            <legend style="font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Modificar comunidad' : 'Registrar comunidad' }}
            </legend>
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="20%"><b>Nombre:</b></td>
                    <td width="30%">
                        <input wire:model="nombre" type="text" style="width: 90%;"> <span
                            class="obligatorio">*</span>
                        @error('nombre')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td width="20%"><b>RIF:</b></td>
                    <td width="30%">
                        <input wire:model="rif" type="text" style="width: 90%;">
                        @error('rif')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Correo:</b></td>
                    <td>
                        <input wire:model="correo" type="email" style="width: 90%;"> <span
                            class="obligatorio">*</span>
                        @error('correo')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                    </td>
                    <td><b>Teléfono:</b></td>
                    <td>
                        <div style="display: flex; gap: 5px; align-items: center;">
                            <select wire:model="prefijo_telefono" style="padding: 3px;">
                                <option value="0424">0424</option>
                                <option value="0414">0414</option>
                                <option value="0412">0412</option>
                                <option value="0422">0422</option>
                                <option value="0416">0416</option>
                                <option value="0426">0426</option>
                            </select>
                            <input wire:model="numero_telefono" type="text" style="width: 60%;"
                                placeholder="XXX-XXXX"> <span class="obligatorio">*</span>
                        </div>
                        @error('prefijo_telefono')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                        @error('numero_telefono')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td><b>Año / trayecto ref.:</b></td>
                    <td colspan="3">
                        <input wire:model="anio" type="text" style="width: 40%;"
                            placeholder="Ej. Año IV (opcional)">
                        @error('anio')
                            <span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                    </td>
                </tr>
                <tr>
                    <td valign="top"><b>Dirección y notas:</b></td>
                    <td colspan="3">
                        <textarea wire:model="direccion" rows="4" style="width: 95%;"
                            placeholder="Dirección, estado/municipio, autoridades y personal vinculado..."></textarea>
                        <span class="obligatorio">*</span>
                        @error('direccion')
                            <br><span style="color:red;font-size:10px;">{{ $message }}</span>
                        @enderror
                        <div style="font-size: 9px; color: #666; margin-top: 4px;">
                            Cargos sugeridos (texto libre):
                            {{ implode(' · ', config('comunidades.cargos_contacto', [])) }}
                        </div>
                    </td>
                </tr>
            </table>
            <div style="margin-top: 15px; text-align: center;">
                <button type="button" wire:click="cancel" class="cm-btn cm-btn-secondary"
                    style="margin-right: 10px;">Cancelar</button>
                <button type="button" wire:click="save" class="cm-btn cm-btn-primary">Guardar</button>
            </div>
        </fieldset>
    @endif
</div>
