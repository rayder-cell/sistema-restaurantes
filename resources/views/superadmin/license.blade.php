@extends('layouts.superadmin')

@section('title', 'License Control — MIKHUNAWASI')

@section('content')

{{-- Encabezado --}}
<div class="sa-page-header">
    <div>
        <p class="sa-page-sub">MIKHUNAWASI</p>
        <h2 class="sa-page-title">License Control</h2>
    </div>
    <div class="sa-page-date">Hoy: {{ now()->format('Y-m-d') }}</div>
</div>

{{-- Métricas --}}
<div class="lc-grid-metrics">

    <div class="lc-metric-card">
        <div class="lc-metric-icon" style="background:#DCFCE7;color:#16a34a;">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <p class="lc-metric-label">LICENCIAS VIGENTES</p>
            <p class="lc-metric-value">{{ $vigentes }}</p>
            <p class="lc-metric-sub">Más de 30 días restantes</p>
        </div>
    </div>

    <div class="lc-metric-card">
        <div class="lc-metric-icon" style="background:#FEF9C3;color:#D97706;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <p class="lc-metric-label">POR VENCER</p>
            <p class="lc-metric-value" style="color:#D97706;">{{ $porVencer }}</p>
            <p class="lc-metric-sub">Menos de 30 días</p>
        </div>
    </div>

    <div class="lc-metric-card">
        <div class="lc-metric-icon" style="background:#FEE2E2;color:#BA1A1A;">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div>
            <p class="lc-metric-label">VENCIDAS</p>
            <p class="lc-metric-value" style="{{ $vencidas > 0 ? 'color:#BA1A1A;' : '' }}">{{ $vencidas }}</p>
            @if($vencidas > 0)
                <p class="lc-metric-sub" style="color:#BA1A1A;font-weight:500;">Requiere acción inmediata</p>
            @else
                <p class="lc-metric-sub">Sin problemas</p>
            @endif
        </div>
    </div>

</div>

{{-- Tabla de licencias --}}
<div class="sa-card">

    <div class="sa-card-toolbar">
        <div class="sa-filter-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="sa-filter-ico">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="filtro-licencia"
                   placeholder="Buscar restaurante..."
                   class="sa-filter-input"
                   onkeyup="filtrarTabla()">
        </div>
        <div class="lc-leyenda">
            <span class="lc-dot" style="background:#16a34a;"></span> Vigente
            <span class="lc-dot" style="background:#D97706;margin-left:12px;"></span> Por vencer
            <span class="lc-dot" style="background:#BA1A1A;margin-left:12px;"></span> Vencida
        </div>
    </div>

    <table class="sa-table" id="tabla-licencias">
        <thead>
            <tr>
                <th>RESTAURANTE</th>
                <th>INICIO LICENCIA</th>
                <th>VENCIMIENTO</th>
                <th>DÍAS RESTANTES</th>
                <th>ESTADO</th>
                <th>ACCIONES</th>
            </tr>
        </thead>
        <tbody>
            @forelse($restaurantes as $r)
            <tr data-nombre="{{ strtolower($r->nombre) }}">

                {{-- Restaurante --}}
                <td>
                    <div class="sa-rest-cell">
                        <div class="sa-rest-avatar">
                            {{ strtoupper(substr($r->nombre, 0, 1)) }}
                        </div>
                        <div>
                            <p class="sa-rest-nombre">{{ $r->nombre }}</p>
                            <p class="sa-rest-ruc">RUC: {{ $r->ruc }}</p>
                        </div>
                    </div>
                </td>

                {{-- Inicio --}}
                <td class="lc-fecha">{{ $r->licencia_inicio->format('d/m/Y') }}</td>

                {{-- Vencimiento --}}
                <td class="lc-fecha">{{ $r->licencia_fin->format('d/m/Y') }}</td>

                {{-- Días restantes --}}
                <td>
                    @if($r->dias_restantes > 30)
                        <span class="lc-dias-badge" style="background:#DCFCE7;color:#16a34a;">
                            {{ $r->dias_restantes }} días
                        </span>
                    @elseif($r->dias_restantes > 0)
                        <span class="lc-dias-badge" style="background:#FEF9C3;color:#D97706;">
                            {{ $r->dias_restantes }} días
                        </span>
                    @else
                        <span class="lc-dias-badge" style="background:#FEE2E2;color:#BA1A1A;">
                            Vencida
                        </span>
                    @endif
                </td>

                {{-- Estado --}}
                <td>
                    @if($r->licencia_estado === 'vigente')
                        <span class="sa-estado-activo">● Vigente</span>
                    @elseif($r->licencia_estado === 'por_vencer')
                        <span class="lc-estado-porvencer">● Por vencer</span>
                    @else
                        <span class="sa-estado-suspendido">● Vencida</span>
                    @endif
                </td>

                {{-- Acción --}}
                <td>
                    <form method="POST"
                          action="{{ route('superadmin.license.renovar', $r->id) }}"
                          onsubmit="return confirm('¿Renovar licencia de {{ addslashes($r->nombre) }} por 1 año?')">
                        @csrf
                        <button type="submit" class="lc-btn-renovar">
                            <i class="fa-solid fa-arrows-rotate"></i> Renovar
                        </button>
                    </form>
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="6" class="sa-table-empty">
                    <p><i class="fa-solid fa-clipboard-list text-2xl text-gray-300"></i></p>
                    <p>No hay restaurantes registrados</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="sa-card-footer">
        <span>{{ $restaurantes->count() }} licencias en total</span>
    </div>

</div>

{{-- Estilos propios de esta vista --}}
<style>
.lc-grid-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 20px;
}
.lc-metric-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.lc-metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.lc-metric-label {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
}
.lc-metric-value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
}
.lc-metric-sub {
    font-size: 12px;
    color: #9ca3af;
    margin-top: 2px;
}
.lc-leyenda {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #6b7280;
}
.lc-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.lc-fecha {
    font-size: 13px;
    color: #6b7280;
}
.lc-dias-badge {
    font-size: 12px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}
.lc-estado-porvencer {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 20px;
    background: #FEF9C3;
    color: #D97706;
}
.lc-btn-renovar {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 8px;
    border: 1.5px solid #000;
    color: #000;
    background: white;
    cursor: pointer;
    transition: all .15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.lc-btn-renovar:hover {
    background: #4F378A;
    color: white;
}
</style>

<script>
function filtrarTabla() {
    const q = document.getElementById('filtro-licencia').value.toLowerCase();
    document.querySelectorAll('#tabla-licencias tbody tr[data-nombre]').forEach(tr => {
        tr.style.display = tr.dataset.nombre.includes(q) ? '' : 'none';
    });
}
</script>

@endsection