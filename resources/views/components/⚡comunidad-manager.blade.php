<?php

use App\Models\Comunidad;
use App\Models\User;
use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $viewMode = 'list';
    public $editingId = null;

    // Comunidad fields
    public $nombre = '';
    public $direccion_exacta = '';
    public $rif = '';
    public $correo = '';
    public $numero_telefono = '';
    public $estado_id = '';
    public $municipio_id = '';
    public $fecha_registro = '';

    public $contactos = [];
    public $nuevo_contacto_nombre = '';
    public $nuevo_contacto_role_id = '';
    public $nuevo_contacto_telefono = '';
    public $nuevo_contacto_correo = '';



    protected $rules = [
        'nombre' => 'required|string|max:255',
        'rif' => 'required|string|max:50',
        'direccion_exacta' => 'required|string',
        'correo' => 'required|email|max:150',
        'numero_telefono' => 'required|string|max:20',
        'estado_id' => 'required|exists:estados,id',
        'municipio_id' => 'required|exists:municipios,id',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre de la comunidad es obligatorio',
        'rif.required' => 'El RIF es obligatorio',
        'correo.required' => 'El correo es obligatorio',
        'numero_telefono.required' => 'El teléfono es obligatorio',
        'estado_id.required' => 'El estado es obligatorio',
        'municipio_id.required' => 'El municipio es obligatorio',
    ];

    public function updatedEstadoId()
    {
        $this->municipio_id = '';
    }

    public function updatingSearch() { $this->resetPage(); }

    public function create()
    {
        $this->reset(['editingId', 'nombre', 'direccion_exacta', 'rif', 'correo', 'numero_telefono', 'estado_id', 'municipio_id', 'contactos']);
        $this->resetValidation();
        $this->fecha_registro = now()->format('Y-m-d');
        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function edit($id)
    {
        $this->resetValidation();
        $comunidad = Comunidad::with('direccion.municipio.estado', 'contactos.cargo')->findOrFail($id);
        $this->editingId = $id;
        $this->nombre = $comunidad->nombre;
        $this->rif = $comunidad->rif;
        $this->correo = $comunidad->correo;
        $this->numero_telefono = $comunidad->numero_telefono;
        $this->fecha_registro = $comunidad->fecha_registro ? date('Y-m-d', strtotime($comunidad->fecha_registro)) : '';

        if ($comunidad->direccion) {
            $this->direccion_exacta = $comunidad->direccion->direccion_exacta;
            $this->municipio_id = $comunidad->direccion->municipio_id;
            $this->estado_id = $comunidad->direccion->municipio->estado_id;
        }

        $this->contactos = $comunidad->contactos->map(function($c) {
            return [
                'nombre' => $c->nombre,
                'role_id' => $c->role_id,
                'telefono' => $c->telefono,
                'correo' => $c->correo,
                'cargo_nombre' => $c->cargo->tipo_de_rol ?? 'Desconocido'
            ];
        })->toArray();


        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function save()
    {
        $this->validate();

        $comunidadExistente = Comunidad::find($this->editingId);

        $direccionRecord = \App\Models\Direccion::updateOrCreate(
            ['id' => $comunidadExistente?->direccion_id],
            [
                'municipio_id' => $this->municipio_id,
                'direccion_exacta' => $this->direccion_exacta,
            ]
        );

        $datosPayload = [
            'nombre' => $this->nombre,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'numero_telefono' => $this->numero_telefono,
            'direccion_id' => $direccionRecord->id,
            'fecha_registro' => $this->fecha_registro ?: null,
        ];

        if (!$this->editingId && auth()->user()->hasRole('profesor proyecto')) {
            $roleData = auth()->user()->roles()->where('nombre', 'profesor proyecto')->first();
            if ($roleData) {
                $datosPayload['anio'] = $roleData->pivot->anio;
                $datosPayload['profesor_id'] = auth()->id();
                $datosPayload['coordinacion_id'] = $roleData->pivot->coordinacion_id;
            }
        }

        $comunidad = Comunidad::updateOrCreate(
            ['id' => $this->editingId],
            $datosPayload
        );

        $comunidad->contactos()->delete();
        foreach($this->contactos as $c) {
            $comunidad->contactos()->create([
                'nombre' => $c['nombre'],
                'role_id' => $c['role_id'],
                'telefono' => $c['telefono'],
                'correo' => $c['correo']
            ]);
        }



        session()->flash('message', 'Comunidad guardada exitosamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function toggleStatus($id)
    {
        $item = Comunidad::find($id);
        if ($item) {
            $item->update(['activa' => !$item->activa]);
            session()->flash('message', 'Estado de la comunidad actualizado.');
            $this->dispatch('refresh-icons');
        }
    }

    public function cancel()
    {
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function addContacto()
    {
        $this->validate([
            'nuevo_contacto_nombre' => 'required',
            'nuevo_contacto_role_id' => 'required',
        ], [
            'nuevo_contacto_nombre.required' => 'El nombre del contacto es obligatorio',
            'nuevo_contacto_role_id.required' => 'El cargo es obligatorio',
        ]);

        $rol = Role::find($this->nuevo_contacto_role_id);

        $this->contactos[] = [
            'nombre' => $this->nuevo_contacto_nombre,
            'role_id' => $this->nuevo_contacto_role_id,
            'telefono' => $this->nuevo_contacto_telefono,
            'correo' => $this->nuevo_contacto_correo,
            'cargo_nombre' => $rol ? $rol->tipo_de_rol : 'Desconocido'
        ];

        $this->reset(['nuevo_contacto_nombre', 'nuevo_contacto_role_id', 'nuevo_contacto_telefono', 'nuevo_contacto_correo']);
        $this->dispatch('contactoAdded'); 
    }

    public function removeContacto($index)
    {
        unset($this->contactos[$index]);
        $this->contactos = array_values($this->contactos);
    }
    public function toggleAlertaCoordinacion()
    {
        $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
        if ($coordRole && $coordRole->pivot->coordinacion_id) {
            $coordinacion = \App\Models\Coordinacion::find($coordRole->pivot->coordinacion_id);
            if ($coordinacion) {
                $coordinacion->alertar_comunidades = !$coordinacion->alertar_comunidades;
                $coordinacion->save();
            }
        }
        $this->dispatch('refresh-icons');
    }

    public function with()
    {
        $alertaAsesor = false;
        $alertaCoordinador = false;

        if (auth()->user()->hasRole('profesor proyecto')) {
            $profRole = auth()->user()->roles()->where('nombre', 'profesor proyecto')->first();
            if ($profRole && $profRole->pivot->coordinacion_id) {
                $coordinacion = \App\Models\Coordinacion::find($profRole->pivot->coordinacion_id);
                $alertaAsesor = $coordinacion ? $coordinacion->alertar_comunidades : false;
            }
        }

        if (auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
            $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
            if ($coordRole && $coordRole->pivot->coordinacion_id) {
                $coordinacion = \App\Models\Coordinacion::find($coordRole->pivot->coordinacion_id);
                $alertaCoordinador = $coordinacion ? $coordinacion->alertar_comunidades : false;
            }
        }

        $municipios_list = [];
        if (!empty($this->estado_id)) {
            $municipios_list = \App\Models\Municipio::where('estado_id', $this->estado_id)->orderBy('nombre')->get();
        }

        return [
            'alertaAsesor' => $alertaAsesor,
            'alertaCoordinador' => $alertaCoordinador,
            'estados' => \App\Models\Estado::orderBy('nombre')->get(),
            'municipios' => $municipios_list,
            'roles_sistema' => \App\Models\Role::whereNotIn('tipo_de_rol', ['lider', 'autor'])->orderBy('tipo_de_rol')->get(),
            'comunidades' => Comunidad::with(['coordinacion', 'profesor', 'direccion.municipio.estado'])->where(function($q) {
                                          $q->where('nombre', 'like', "%{$this->search}%")
                                            ->orWhere('rif', 'like', "%{$this->search}%");
                                      })
                                      ->when(!auth()->user()->hasRole('administrador'), function($query) {
                                          if (auth()->user()->hasRole('profesor proyecto')) {
                                              $query->where('profesor_id', auth()->id());
                                          } else {
                                              $query->where(function($q) {
                                                  $q->whereHas('equipos.estudiantes', function($q2) {
                                                      $q2->where('persona_id', auth()->id());
                                                  });
                                                  if (auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER')) {
                                                      $coordRole = auth()->user()->roles()->whereIn('nombre', ['coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->first();
                                                      if ($coordRole && $coordRole->pivot->coordinacion_id) {
                                                          $q->orWhere('coordinacion_id', $coordRole->pivot->coordinacion_id);
                                                      }
                                                  }
                                              });
                                          }
                                      })
                                      ->latest()
                                      ->paginate(10),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px;">Gestión de Comunidades</h2>

    @if(isset($alertaAsesor) && $alertaAsesor)
        <div style="background-color: #f8d7da; color: #721c24; border: 2px dashed #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:13px; font-weight:bold; text-align:center; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1)); cursor: default;">
            ⚠ ¡ATENCIÓN! La COORDINACION_DE_Coordinación_TITLE_TEMP_PLACEHOLDER solicita urgentemente el registro y actualización de sus Comunidades.
        </div>
    @endif

    @if (session()->has('message'))
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size:12px;">
            {{ session('message') }}
        </div>
    @endif

    @if($viewMode === 'list')
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Buscador y Listado</legend>
            <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="30%"><b>Buscar Sector/RIF:</b></td>
                    <td width="50%">
                        <input wire:model.live="search" type="text" style="width: 90%; padding: 3px;" placeholder="...">
                    </td>
                    <td width="30%" align="right">
                        @if(isset($alertaCoordinador) && auth()->user()->hasRole('coordinador', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER'))
                            <button wire:click="toggleAlertaCoordinacion" class="boton" style="border: 2px solid {{ $alertaCoordinador ? '#FF0000' : '#4CAF50' }}; border-radius: 4px; padding: 4px 10px; font-weight: bold; background-color: {{ $alertaCoordinador ? '#FFdddd' : '#ddFFdd' }}; color: #000; height: auto; min-height: 26px; white-space: normal; margin-bottom: 4px;">
                                {{ $alertaCoordinador ? '🔕 Desactivar Alerta a Profesores' : '🔔 Enviar Alerta a Profesores' }}
                            </button>
                        @endif

                        @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                            <button wire:click="create" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: auto; min-height: 26px; white-space: nowrap;">
                                Registrar Nueva Comunidad
                            </button>
                        @endif
                    </td>
                </tr>
            </table>

            <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 10px;">
                <thead>
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                        <th width="5%">N°</th>
                        <th width="30%">Nombre de la Comunidad</th>
                        <th width="15%">RIF</th>
                        <th width="30%">Contacto (Correo / Tlf)</th>
                        <th width="20%">Acciones</th>
                    </tr>
                </thead>
                <tbody class="Texto">
                    @foreach($comunidades as $index => $c)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                            <td align="center">{{ $loop->iteration }}</td>
                            <td align="left">
                                <span style="font-weight: bold; font-size: 11px;">{{ $c->nombre }}</span><br>
                                @if($c->anio || $c->coordinacion_id)
                                    <span style="font-weight: bold; font-size: 9px; color: #8b0000; display:inline-block; margin-bottom:2px;">
                                        [{{ mb_strtoupper($c->coordinacion->nombre ?? 'N/A') }} - {{ mb_strtoupper($c->anio) }}]
                                    </span><br>
                                @endif
                                @if($c->profesor)
                                    <span style="font-size: 9px; color: #000; display:inline-block; margin-bottom: 2px;"><b>Prof. Asesor:</b> {{ mb_strtoupper($c->profesor->nombre . ' ' . $c->profesor->apellido) }}</span><br>
                                @endif
                                <span style="font-size:9px; color:#555;"><b>Estado:</b> {{ $c->direccion?->municipio?->estado?->nombre ?? 'N/A' }} | <b>Mcpio:</b> {{ $c->direccion?->municipio?->nombre ?? 'N/A' }} <br> {{ $c->direccion?->direccion_exacta ?? 'N/A' }}</span>
                            </td>
                            <td align="center">
                                <span style="font-weight: bold;">{{ $c->rif ?? 'N/A' }}</span><br>
                                @if($c->activa)
                                    <span style="color: #008000; font-weight: bold; font-size: 9px;">Activa</span>
                                @else
                                    <span style="color: #FF0000; font-weight: bold; font-size: 9px;">Inactiva</span>
                                @endif
                            </td>
                            <td align="center">
                                {{ $c->correo }} <br>
                                <span style="font-weight:bold;">{{ $c->numero_telefono }}</span>
                            </td>
                            <td align="center">
                                @if(auth()->user()->hasRole('administrador', 'profesor proyecto'))
                                    <a href="#" wire:click.prevent="edit({{ $c->id }})" title="Editar" style="color: #0000EE; text-decoration: none; margin-bottom: 2px; display: inline-block;">
                                        [Editar]
                                    </a>
                                    <br>
                                    <a href="#" wire:click.prevent="toggleStatus({{ $c->id }})" title="Cambiar Estado" style="color: {{ $c->activa ? '#FF0000' : '#008000' }}; text-decoration: none; font-size: 10px;">
                                        [{{ $c->activa ? 'Inhabilitar' : 'Habilitar' }}]
                                    </a>
                                @else
                                    <span style="color: #888;">[Solo Lectura]</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($comunidades->isEmpty())
                        <tr>
                            <td colspan="5" align="center" style="padding: 20px;">No hay comunidades registradas.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div style="margin-top: 10px;">{{ $comunidades->links() }}</div>
        </fieldset>

    @else
        <!-- FORMULARIO DE REGISTRO/EDICION -->
        <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 20px;">
            <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">
                {{ $editingId ? 'Modificar Información de Comunidad' : 'Registrar Nueva Comunidad' }}
            </legend>

            <!-- Datos Base -->
            <table width="100%" border="0" cellpadding="6" cellspacing="0" style="font-size: 11px;">
                <tr>
                    <td width="20%"><b>Nombre de la Comunidad:</b></td>
                    <td width="30%">
                        <input wire:model="nombre" type="text" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('nombre') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="20%"><b>Documento RIF:</b></td>
                    <td width="30%">
                        <input wire:model="rif" type="text" style="width: 80%;" placeholder="J-12345678-9"> <span style="color:red; font-weight:bold;">*</span>
                        @error('rif') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <td width="20%"><b>Correo Electrónico:</b></td>
                    <td width="30%">
                        <input wire:model="correo" type="email" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('correo') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="20%"><b>Número Teléfono:</b></td>
                    <td width="30%">
                        <input wire:model="numero_telefono" type="text" style="width: 80%;"> <span style="color:red; font-weight:bold;">*</span>
                        @error('numero_telefono') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <td width="20%"><b>Fecha de Registro:</b></td>
                    <td width="30%">
                        <input wire:model="fecha_registro" type="date" style="width: 80%;">
                    </td>
                <tr>
                    <td width="20%"><b>Estado:</b></td>
                    <td width="30%">
                        <select wire:model.live="estado_id" style="width: 80%;">
                            <option value="">Seleccione Estado...</option>
                            @foreach($estados as $est)
                                <option value="{{ $est->id }}">{{ $est->nombre }}</option>
                            @endforeach
                        </select> <span style="color:red; font-weight:bold;">*</span>
                        @error('estado_id') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                    <td width="20%"><b>Municipio:</b></td>
                    <td width="30%">
                        <select wire:model="municipio_id" style="width: 80%;">
                            <option value="">Seleccione Municipio...</option>
                            @foreach($municipios as $mun)
                                <option value="{{ $mun->id }}">{{ $mun->nombre }}</option>
                            @endforeach
                        </select> <span style="color:red; font-weight:bold;">*</span>
                        @error('municipio_id') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <td width="20%" valign="top"><b>Dirección Completa:</b></td>
                    <td colspan="3">
                        <textarea wire:model="direccion_exacta" style="width: 90%; height:40px;"></textarea>
                        @error('direccion_exacta') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                    </td>
                </tr>
            </table>

            <br>
            <fieldset style="border: 1px solid #CCC; padding: 10px; margin-bottom: 15px;">
                <legend style="font-weight: bold; font-size: 12px; padding: 0 5px; background-color: #f0f0f0;">Representantes / Personas de Contacto</legend>
                
                <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px; margin-bottom: 10px; background-color: #e9ecef; border: 1px solid #CCC; padding: 5px;">
                    <tr>
                        <td width="25%"><b>Nombre:</b><br>
                            <input wire:model="nuevo_contacto_nombre" type="text" style="width: 90%;">
                            @error('nuevo_contacto_nombre') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="25%"><b>Cargo/Rol:</b><br>
                            <select wire:model="nuevo_contacto_role_id" style="width: 90%;">
                                <option value="">Seleccione...</option>
                                @foreach($roles_sistema as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->tipo_de_rol) }}</option>
                                @endforeach
                            </select>
                            @error('nuevo_contacto_role_id') <br><span style="color:red; font-size:10px;">{{ $message }}</span> @enderror
                        </td>
                        <td width="20%"><b>Teléfono:</b><br>
                            <input wire:model="nuevo_contacto_telefono" type="text" style="width: 90%;">
                        </td>
                        <td width="30%" rowspan="2" valign="bottom" align="center">
                            <button wire:click.prevent="addContacto" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 15px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 35px;">
                                + Añadir a la Lista
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div x-data="{ correo: @entangle('nuevo_contacto_correo'), confirmacion: '' }" @contacto-added.window="confirmacion = ''">
                                <table width="100%" border="0" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td width="50%"><b>Correo Electrónico:</b><br><input type="email" x-model="correo" style="width: 90%;"></td>
                                        <td width="50%"><b>Confirmar Correo:</b> <span x-show="confirmacion !== '' && correo === confirmacion" style="color:green; font-weight:bold;">(✓ Coinciden)</span>
                                            <span x-show="confirmacion !== '' && correo !== confirmacion" style="color:red; font-weight:bold;">(X No coinciden)</span>
                                            <br><input type="email" x-model="confirmacion" style="width: 90%;">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <table width="100%" border="1" cellpadding="3" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; margin-top: 10px; font-size: 11px;">
                    <tr style="background-color: #8bb2b7; color: #000; font-weight: bold; text-align: center;">
                        <td width="30%">Nombre</td>
                        <td width="20%">Cargo</td>
                        <td width="20%">Teléfono</td>
                        <td width="20%">Correo</td>
                        <td width="10%">Acción</td>
                    </tr>
                    @foreach($contactos as $index => $cont)
                        <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};">
                            <td align="left" style="padding-left:10px;">{{ mb_strtoupper($cont['nombre']) }}</td>
                            <td align="center"><span style="font-weight: bold;">{{ mb_strtoupper($cont['cargo_nombre']) }}</span></td>
                            <td align="center">{{ $cont['telefono'] ?? 'N/A' }}</td>
                            <td align="center">{{ $cont['correo'] ?? 'N/A' }}</td>
                            <td align="center">
                                <a href="#" wire:click.prevent="removeContacto({{ $index }})" style="color: #FF0000; text-decoration: none;">[Quitar]</a>
                            </td>
                        </tr>
                    @endforeach
                    @if(empty($contactos))
                        <tr>
                            <td colspan="5" align="center" style="padding: 10px; background-color: #FFFFFF;">
                                No hay representantes registrados. Utilice el formulario de arriba para añadirlos.
                            </td>
                        </tr>
                    @endif
                </table>
            </fieldset>

            <br>
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
                <tr>
                    <td align="center">
                        <button wire:click="save" class="boton" style="border: 1px solid #000; border-radius: 4px; padding: 4px 20px; font-weight: bold; background-color: #8bb2b7; color: #000; height: 30px;">
                            {{ $editingId ? 'Actualizar Información' : 'Registrar Comunidad' }}
                        </button>
                        <button wire:click="cancel" class="boton" style="border: 1px solid #999; border-radius: 4px; padding: 4px 15px; font-weight: normal; background-color: #f0f0f0; color: #000; height: 30px; margin-left: 10px;">
                            Cancelar
                        </button>
                    </td>
                </tr>
            </table>
        </fieldset>
    @endif


</div>