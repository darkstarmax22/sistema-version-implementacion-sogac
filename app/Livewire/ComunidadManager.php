<?php

namespace App\Livewire;

use App\Models\Comunidad;
use App\Services\IntranetProfessorService;
use Livewire\Component;
use Livewire\WithPagination;

class ComunidadManager extends Component
{
    use WithPagination;

    public string $search = '';

    public string $viewMode = 'list';

    public ?int $editingId = null;

    public string $nombre = '';

    public string $direccion = '';

    public string $rif = '';

    public string $correo = '';

    public string $numero_telefono = '';

    public string $prefijo_telefono = '0424';

    public string $anio = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'rif' => 'nullable|string|max:50',
            'direccion' => 'required|string|max:500',
            'correo' => 'required|email|max:150',
            'prefijo_telefono' => 'required|in:0424,0414,0412,0422,0416,0426',
            'numero_telefono' => 'required|digits:7',
            'anio' => 'nullable|string|max:32',
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la comunidad es obligatorio',
            'direccion.required' => 'La dirección es obligatoria',
            'correo.required' => 'El correo es obligatorio',
            'prefijo_telefono.required' => 'El prefijo del teléfono es obligatorio',
            'numero_telefono.required' => 'El teléfono es obligatorio',
            'numero_telefono.digits' => 'El teléfono debe tener 7 dígitos.',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function puedeGestionar(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole('administrador', 'coordinador')) {
            return true;
        }

        if ($user->hasRole('profesor proyecto')) {
            return app(IntranetProfessorService::class)
                ->esProfesorProyectoVigente(trim((string) $user->usu_cedula));
        }

        return false;
    }

    public function create(): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para registrar comunidades.');
            return;
        }

        $this->reset(['editingId', 'nombre', 'direccion', 'rif', 'correo', 'numero_telefono', 'prefijo_telefono', 'anio']);
        $this->prefijo_telefono = '0424';
        $this->resetValidation();

        $cfg = auth()->user()?->profesorProyectoModulo();
        if ($cfg && ! empty($cfg->ppm_anio)) {
            $this->anio = (string) $cfg->ppm_anio;
        }

        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function edit(int $id): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para editar comunidades.');
            return;
        }

        $this->resetValidation();
        $comunidad = Comunidad::query()->whereKey($id)->firstOrFail();
        $this->editingId = $id;
        $this->nombre = $comunidad->nombre;
        $this->rif = $comunidad->rif;
        $this->correo = $comunidad->correo;

        $telefonoCompleto = $comunidad->numero_telefono;
        $prefijos = ['0424', '0414', '0412', '0422', '0416', '0426'];
        $this->prefijo_telefono = '0424';
        $this->numero_telefono = $telefonoCompleto;

        foreach ($prefijos as $prefijo) {
            if (str_starts_with($telefonoCompleto, $prefijo)) {
                $this->prefijo_telefono = $prefijo;
                $this->numero_telefono = substr($telefonoCompleto, strlen($prefijo));
                break;
            }
        }

        $this->direccion = $comunidad->direccion ?? '';
        $this->anio = $comunidad->anio ?? '';
        $this->viewMode = 'form';
        $this->dispatch('refresh-icons');
    }

    public function save(): void
    {
        if (! $this->puedeGestionar()) {
            session()->flash('message_error', 'No tiene permiso para guardar comunidades.');
            return;
        }

        $this->validate();

        $payload = [
            'nombre' => $this->nombre,
            'rif' => $this->rif,
            'correo' => $this->correo,
            'numero_telefono' => $this->prefijo_telefono . $this->numero_telefono,
            'direccion' => $this->direccion,
            'anio' => $this->anio !== '' ? $this->anio : null,
        ];

        Comunidad::guardar($payload, $this->editingId);

        session()->flash('message', 'Comunidad guardada correctamente.');
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function cancel(): void
    {
        $this->viewMode = 'list';
        $this->dispatch('refresh-icons');
    }

    public function with(): array
    {
        $termino = trim($this->search);

        $comunidades = Comunidad::query()
            ->when($termino !== '', function ($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                    ->orWhere('rif', 'like', '%' . $termino . '%')
                    ->orWhere('direccion', 'like', '%' . $termino . '%');
            })
            ->orderByDesc((new Comunidad())->getKeyName())
            ->paginate(10);

        return [
            'comunidades' => $comunidades,
            'puedeGestionar' => $this->puedeGestionar(),
            'lapsoVigente' => app(IntranetProfessorService::class)->lapsosActivos()->first(),
        ];
    }

    public function render()
    {
        return view('livewire.comunidad-manager', $this->with());
    }
}
