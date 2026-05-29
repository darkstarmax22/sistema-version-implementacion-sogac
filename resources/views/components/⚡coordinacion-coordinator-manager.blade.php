<?php

use App\Helpers\DualDatabase;
use App\Models\Coordinacion;
use App\Services\IntranetProfessorService;
use App\Services\ModuloRepositorioService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function with(IntranetProfessorService $profesores)
    {
        $conn = DualDatabase::academicConnection();
        $map = config('roles.usu_cod_rol_map', []);
        $codCoord = array_search('coordinador', $map, true);

        $coordinadores = collect();
        if ($codCoord !== false) {
            try {
                $qb = DB::connection($conn)
                    ->table('usuario as u')
                    ->leftJoin('persona as p', DB::raw('TRIM(p.per_cedula)'), '=', DB::raw('TRIM(u.usu_cedula)'))
                    ->where('u.usu_cod_rol', (int) $codCoord);

                if ($this->search !== '') {
                    $termRaw = trim(mb_strtolower($this->search));
                    // If the search looks like a cedula (digits, at least 4), prefer prefix match on cedula
                    $digits = preg_replace('/\D+/', '', $termRaw);
                    if ($digits !== '' && ctype_digit($digits) && strlen($digits) >= 4) {
                        $qb->whereRaw('TRIM(u.usu_cedula) LIKE ?', [$digits.'%']);
                    } else {
                        $parts = preg_split('/\s+/', $termRaw);
                        $qb->where(function ($qparts) use ($parts) {
                            foreach ($parts as $pw) {
                                $pwLike = $pw.'%';
                                $qparts->orWhereRaw('LOWER(TRIM(p.per_nombres)) LIKE ?', [$pwLike])
                                    ->orWhereRaw('LOWER(TRIM(p.per_apellidos)) LIKE ?', [$pwLike])
                                    ->orWhereRaw('LOWER(TRIM(u.usu_nombre)) LIKE ?', [$pwLike]);
                            }
                        });
                    }
                }

                $coordinadores = $qb
                    ->selectRaw('TRIM(u.usu_cedula) as cedula')
                    ->selectRaw('TRIM(COALESCE(p.per_nombres, u.usu_nombre)) as nombre')
                    ->selectRaw('TRIM(p.per_apellidos) as apellido')
                    ->orderBy('apellido')
                    ->orderBy('nombre')
                    ->limit(200)
                    ->get();
            } catch (\Throwable) {
                $coordinadores = collect();
            }
        }

        $lap = $profesores->lapsoVigenteCodigo();
        $docentes = $lap
            ? $profesores->paginateDocentesActivos($this->search, $lap, 8, $this->getPage())
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 8, 1);

        // Define $coordinacionesCatalogo here
        $moduloRepositorioService = app(ModuloRepositorioService::class);
        $coordinacionesCatalogo = $moduloRepositorioService->coordinacionesActivas();

        return [
            'coordinadoresIntranet' => $coordinadores,
            'docentes' => $docentes,
            'programas' => app(\App\Services\AcademicCatalog::class)->programasForSelect(),
            'lapsoVigente' => $lap,
            'coordinacionesCatalogo' => $coordinacionesCatalogo, // Pass the variable to the view
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 16px; font-weight: bolder;">Coordinadores de coordinación</h2>

    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 10px; margin-bottom: 14px; font-size: 11px;">
        Los coordinadores se definen en <strong>intranet</strong> (<code>usuario.usu_cod_rol</code>).
        Este módulo <strong>no crea tablas</strong> ni duplica datos académicos en MySQL repositorio.
        La asignación a un PNF/coordinación se gestiona en intranet.
    </div>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 18px;">
        <legend style="font-weight: bold; font-style: italic;">Coordinadores (intranet)</legend>
        <table width="100%" border="1" cellpadding="5" style="border-collapse: collapse; font-size: 11px;">
            <thead>
                <tr style="background: #8bb2b7; font-weight: bold; text-align: center;">
                    <th>Cédula</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coordinadoresIntranet as $c)
                    <tr style="background: {{ $loop->even ? '#eee' : '#fff' }};">
                        <td align="center">{{ $c->cedula }}</td>
                        <td>{{ mb_strtoupper($c->apellido) }} {{ mb_strtoupper($c->nombre) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" align="center" style="padding: 12px;">No hay usuarios con rol coordinador en intranet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </fieldset>

    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px;">
        <legend style="font-weight: bold; font-style: italic;">Docentes activos (referencia, lapso {{ $lapsoVigente ?? '—' }})</legend>
        <p style="font-size: 10px; color: #555; margin-bottom: 8px;">
            Catálogo local de coordinaciones (solo lectura): 
            @foreach($coordinacionesCatalogo as $coord)
                {{ $coord->nombre }}@if(!$loop->last), @endif
            @endforeach
        </p>
        <input wire:model.debounce.500ms="search" type="text" placeholder="Buscar docente…" style="width: 240px; margin-bottom: 8px; font-size: 11px;">
        <table width="100%" border="1" cellpadding="4" style="font-size: 10px; border-collapse: collapse;">
            <thead>
                <tr style="background: #ddd; font-weight: bold;">
                    <th>Docente</th>
                    <th>Sección / lapso</th>
                </tr>
            </thead>
            <tbody>
                @foreach($docentes as $d)
                    <tr>
                        <td>{{ $d->apellido }}, {{ $d->nombre }} ({{ $d->cedula }})</td>
                        <td>{{ $d->lapso_nombre }} — {{ $d->trayecto_nombre ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $docentes->links() }}
    </fieldset>
</div>
