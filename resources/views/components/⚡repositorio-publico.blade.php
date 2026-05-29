<?php

use App\Models\Proyecto;
use App\Models\Coordinacion;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public $search = '';
    public $filterCoordinacion = '';
    public $filterLapso = '';

    public function with()
    {
        return [
            'proyectos' => Proyecto::busquedaPublica(
                $this->search,
                (int) $this->filterCoordinacion ?: null,
                $this->filterLapso
            )->latest()->paginate(9),
            'coordinaciones' => app(\App\Services\ModuloRepositorioService::class)
                ->queryModel(\App\Models\Coordinacion::class)
                ->orderBy('nombre')
                ->get(),
        ];
    }
};
?>

<div>
    <h2 class="titulo" style="margin-bottom: 20px; font-weight: bolder; margin-top: 10px; text-align: center;">Acervo Institucional de Proyectos (UPTP)</h2>
    <p style="text-align: center; color: #555; font-size: 11px; margin-bottom: 20px;">
        Consulta la producción intelectual validada y bajo custodia de la Universidad Politécnica Territorial Juan de Jesús Montilla.
    </p>

    <!-- Filters & Search -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Busqueda en el Repositorio</legend>
        <table width="100%" border="0" cellpadding="4" cellspacing="0" style="font-size: 11px;">
            <tr>
                <td width="50%">
                    <b>Búsqueda global (Título, resumen):</b><br>
                    <input wire:model.live="search" type="text" style="width: 95%;" placeholder="...">
                </td>
                <td width="25%">
                    <b>Filtrar por Coordinación:</b><br>
                    <select wire:model.live="filterCoordinacion" style="width: 95%;">
                        <option value="">Todos los Coordinación...</option>
                        @foreach($coordinaciones as $p) <option value="{{ $p->id }}">{{ $p->nombre }}</option> @endforeach
                    </select>
                </td>
                <td width="25%">
                    <b>Lapso Académico:</b><br>
                    <select wire:model.live="filterLapso" style="width: 95%;">
                        <option value="">Cualquier Lapso...</option>
                        <option value="2024-II">2024-II</option>
                        <option value="2025-I">2025-I</option>
                        <option value="2025-II">2025-II</option>
                        <option value="2026-I">2026-I</option>
                    </select>
                </td>
            </tr>
        </table>
    </fieldset>

    <!-- Results Grid -->
    <fieldset style="border: 2px solid #8b0000; border-radius: 6px; padding: 10px; margin-bottom: 15px;">
        <legend style="color: #000; font-weight: bold; font-style: italic; padding: 0 5px;">Resultados de Búsqueda</legend>

        <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; border-color: #bbbbbb; font-size: 11px; margin-top: 5px;">
            <thead style="background-color: #8bb2b7; color: #000; font-weight: bold;">
                <tr>
                    <th width="35%" align="center">Información del Proyecto</th>
                    <th width="40%" align="center">Resumen Abstracto</th>
                    <th width="15%" align="center">Clasificación</th>
                    <th width="10%" align="center">Descarga</th>
                </tr>
            </thead>
            <tbody class="Texto">
                @foreach($proyectos as $p)
                    <tr style="background-color: {{ $loop->iteration % 2 == 0 ? '#E0E0E0' : '#FFFFFF' }};" valign="top">
                        <td style="padding: 10px;">
                            <span style="font-weight: bold; font-size: 12px; color: #000;">{{ mb_strtoupper($p->titulo) }}</span>
                            <br><br>
                            <span style="font-size: 10px; font-weight: bold; color: #333;">Autores: {{ $p->autores ?? 'N/A' }}</span>
                            @if($p->tutor)
                                <br><span style="font-size: 10px; color: #555;">Tutor: {{ $p->tutor->nombre }} {{ $p->tutor->apellido }}</span>
                            @endif
                        </td>
                        <td align="justify" style="padding: 10px; font-size: 10px; color: #333;">
                            {{ Str::limit($p->resumen, 200) }}
                        </td>
                        <td align="center" style="padding: 10px;">
                            <span style="background-color: #FFFF00; border: 1px solid #CCC; padding: 2px 4px; font-size: 9px; font-weight: bold; color: #000; display: inline-block; margin-bottom: 5px;">
                                {{ $p->coordinacion->nombre }}
                            </span>
                            <br>
                            <span style="font-size: 10px; font-weight: bold;">Lapso: {{ $p->lapso_academico->nombre ?? 'N/A' }}</span>
                        </td>
                        <td align="center" style="padding: 10px;">
                            @if($p->archivo_path)
                                <a href="{{ Storage::url($p->archivo_path) }}" target="_blank" style="display: inline-block; text-align: center; color: #0000EE; text-decoration: none; font-weight: bold; margin-top: 10px;">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/PDF_file_icon.svg/640px-PDF_file_icon.svg.png" alt="PDF" style="width: 32px; height: 32px; border: 0; margin-bottom: 5px;">
                                    <br>Descargar PDF
                                </a>
                            @else
                                <span style="font-size: 10px; color: #999;">Sin Documento</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @if($proyectos->isEmpty())
                    <tr>
                        <td colspan="4" align="center" style="padding: 30px; font-weight: bold; background-color: #FFFFFF;">
                            No se encontraron proyectos publicados que coincidan con los criterios.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <div style="margin-top: 15px;">
            {{ $proyectos->links() }}
        </div>
    </fieldset>

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('refresh-icons', () => {
                setTimeout(() => lucide.createIcons(), 10);
            });
        });
    </script>
</div>
