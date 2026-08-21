@extends('layouts.superadmin')

@section('title', 'System Settings — MIKHUNAWASI')

@section('content')

@vite(['resources/assets/css/pages/settings.css'])

{{-- Encabezado --}}
<div class="sa-page-header">
    <div>
        <p class="sa-page-sub">MIKHUNAWASI</p>
        <h2 class="sa-page-title">System Settings</h2>
    </div>
    <div class="sa-page-date">Versión {{ $settings['version'] }}</div>
</div>

{{-- System Health Banner --}}
<div class="ss-health-banner">
    <div>
        <p class="ss-health-label">SYSTEM HEALTH</p>
        <p class="ss-health-value">100% Online</p>
    </div>
    <div class="ss-health-dot"></div>
</div>

{{-- Métricas --}}
<div class="ss-metrics-grid">
    <div class="ss-metric-card">
        <span class="ss-metric-ico"><i class="fa-solid fa-store"></i></span>
        <p class="ss-metric-label">Restaurantes activos</p>
        <p class="ss-metric-value">{{ $metrics['total_restaurantes'] }}</p>
    </div>
    <div class="ss-metric-card">
        <span class="ss-metric-ico"><i class="fa-solid fa-users"></i></span>
        <p class="ss-metric-label">Usuarios totales</p>
        <p class="ss-metric-value">{{ $metrics['total_usuarios'] }}</p>
    </div>
    <div class="ss-metric-card">
        <span class="ss-metric-ico"><i class="fa-solid fa-clipboard-list"></i></span>
        <p class="ss-metric-label">Pedidos totales</p>
        <p class="ss-metric-value">{{ $metrics['total_pedidos'] }}</p>
    </div>
    <div class="ss-metric-card">
        <span class="ss-metric-ico"><i class="fa-solid fa-bolt"></i></span>
        <p class="ss-metric-label">Uptime</p>
        <p class="ss-metric-value">{{ $metrics['uptime'] }}</p>
    </div>
</div>

{{-- Grid de configuración 2 columnas --}}
<div class="ss-grid">

    {{-- Configuración Financiera --}}
    <div class="sa-card ss-section">
        <div class="ss-section-header">
            <span class="ss-section-ico"><i class="fa-solid fa-sack-dollar"></i></span>
            <h3 class="ss-section-title">Configuración Financiera</h3>
        </div>
        <form method="POST" action="{{ route('superadmin.settings.update') }}" class="ss-form">
            @csrf
            <input type="hidden" name="zona_horaria" value="{{ $settings['zona_horaria'] }}">
            <input type="hidden" name="idioma"       value="{{ $settings['idioma'] }}">

            <div class="ss-form-group">
                <label class="ss-label">IGV (%)</label>
                <div class="ss-input-wrap">
                    <input type="number" name="igv" value="{{ $settings['igv'] }}"
                           min="0" max="100" step="0.1"
                           class="ss-input" style="padding-right:32px;">
                    <span class="ss-input-suffix">%</span>
                </div>
                <p class="ss-hint">Porcentaje de IGV aplicado a todos los comprobantes del sistema.</p>
            </div>

            <div class="ss-form-group">
                <label class="ss-label">Moneda</label>
                <select name="moneda" class="ss-input">
                    <option value="PEN" {{ $settings['moneda'] === 'PEN' ? 'selected' : '' }}>PEN — Sol Peruano</option>
                    <option value="USD" {{ $settings['moneda'] === 'USD' ? 'selected' : '' }}>USD — Dólar Americano</option>
                </select>
            </div>

            <div class="ss-form-group">
                <label class="ss-label">Símbolo de moneda</label>
                <input type="text" name="moneda_simbolo"
                       value="{{ $settings['moneda_simbolo'] }}"
                       maxlength="5" class="ss-input" style="max-width:100px;">
            </div>

            <button type="submit" class="ss-btn-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
        </form>
    </div>

    {{-- Configuración Regional --}}
    <div class="sa-card ss-section">
        <div class="ss-section-header">
            <span class="ss-section-ico"><i class="fa-solid fa-earth-americas"></i></span>
            <h3 class="ss-section-title">Configuración Regional</h3>
        </div>
        <form method="POST" action="{{ route('superadmin.settings.update') }}" class="ss-form">
            @csrf
            <input type="hidden" name="igv"            value="{{ $settings['igv'] }}">
            <input type="hidden" name="moneda_simbolo" value="{{ $settings['moneda_simbolo'] }}">

            <div class="ss-form-group">
                <label class="ss-label">Zona Horaria</label>
                <select name="zona_horaria" class="ss-input">
                    <option value="America/Lima"        {{ $settings['zona_horaria'] === 'America/Lima'        ? 'selected' : '' }}>América/Lima (UTC-5)</option>
                    <option value="America/Bogota"      {{ $settings['zona_horaria'] === 'America/Bogota'      ? 'selected' : '' }}>América/Bogotá (UTC-5)</option>
                    <option value="America/Santiago"    {{ $settings['zona_horaria'] === 'America/Santiago'    ? 'selected' : '' }}>América/Santiago (UTC-3)</option>
                    <option value="America/Mexico_City" {{ $settings['zona_horaria'] === 'America/Mexico_City' ? 'selected' : '' }}>América/Ciudad de México (UTC-6)</option>
                </select>
            </div>

            <div class="ss-form-group">
                <label class="ss-label">Idioma del sistema</label>
                <select name="idioma" class="ss-input">
                    <option value="es" {{ $settings['idioma'] === 'es' ? 'selected' : '' }}>Español</option>
                    <option value="en" {{ $settings['idioma'] === 'en' ? 'selected' : '' }}>English</option>
                </select>
            </div>

            <button type="submit" class="ss-btn-guardar"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
        </form>
    </div>

    {{-- Información de la Plataforma --}}
    <div class="sa-card ss-section">
        <div class="ss-section-header">
            <span class="ss-section-ico"><i class="fa-solid fa-circle-info"></i></span>
            <h3 class="ss-section-title">Información de la Plataforma</h3>
        </div>
        <div class="ss-info-list">
            <div class="ss-info-row">
                <span class="ss-info-label">Nombre del sistema</span>
                <span class="ss-info-value">{{ $settings['plataforma_nombre'] }}</span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">Versión actual</span>
                <span class="ss-info-value">
                    <span class="ss-version-badge">v{{ $settings['version'] }}</span>
                </span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">Framework</span>
                <span class="ss-info-value">Laravel {{ app()->version() }}</span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">PHP Version</span>
                <span class="ss-info-value">{{ PHP_VERSION }}</span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">Base de datos</span>
                <span class="ss-info-value">PostgreSQL 18</span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">Estado del sistema</span>
                <span class="ss-info-value">
                    @if($settings['mantenimiento'])
                        <span style="color:#D97706;font-weight:600;"><i class="fa-solid fa-wrench" style="color:#111827;"></i> En mantenimiento</span>
                    @else
                        <span style="color:#16a34a;font-weight:600;"><i class="fa-solid fa-circle-check" style="color:#111827;"></i> Operativo</span>
                    @endif
                </span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">Zona horaria</span>
                <span class="ss-info-value">{{ $settings['zona_horaria'] }}</span>
            </div>
            <div class="ss-info-row">
                <span class="ss-info-label">IGV configurado</span>
                <span class="ss-info-value">{{ $settings['igv'] }}%</span>
            </div>
        </div>
    </div>

    {{-- Status Indicators --}}
    <div class="sa-card ss-section">
        <div class="ss-section-header">
            <span class="ss-section-ico"><i class="fa-solid fa-chart-column"></i></span>
            <h3 class="ss-section-title">Status Indicators</h3>
        </div>

        <div class="ss-status-list">
            <div class="ss-status-row">
                <div class="ss-status-dot"></div>
                <span class="ss-status-label">Base de datos</span>
                <span class="ss-status-val">Conectada</span>
            </div>
            <div class="ss-status-row">
                <div class="ss-status-dot"></div>
                <span class="ss-status-label">Servidor web</span>
                <span class="ss-status-val">Operativo</span>
            </div>
            <div class="ss-status-row">
                <div class="ss-status-dot"></div>
                <span class="ss-status-label">Sesiones</span>
                <span class="ss-status-val">Activas</span>
            </div>
            <div class="ss-status-row">
                <div class="ss-status-dot"></div>
                <span class="ss-status-label">Storage</span>
                <span class="ss-status-val">Disponible</span>
            </div>
        </div>

        {{-- Badges del Figma --}}
        <div class="ss-badges">
            <span class="ss-badge" style="background:#DCFCE7;color:#16a34a;"><i class="fa-solid fa-check" style="color:#111827;"></i> Pedido</span>
            <span class="ss-badge" style="background:#DBEAFE;color:#1d4ed8;"><i class="fa-solid fa-circle" style="color:#111827;"></i> Nueva orden</span>
            <span class="ss-badge" style="background:#FEE2E2;color:#BA1A1A;"><i class="fa-solid fa-xmark" style="color:#111827;"></i> Cancelar</span>
            <span class="ss-badge" style="background:#FEF9C3;color:#D97706;"><i class="fa-solid fa-triangle-exclamation" style="color:#111827;"></i> Advertencia</span>
            <span class="ss-badge" style="background:#F2ECF4;color:#4F378A;"><i class="fa-solid fa-rotate" style="color:#111827;"></i> Procesando</span>
        </div>

        {{-- Sample Data Table --}}
        <div class="ss-sample-wrap">
            <p class="ss-sample-title">Sample Data Table</p>
            <table class="ss-sample-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Table</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#3492</td>
                        <td>Table 04</td>
                        <td><span style="color:#16a34a;font-weight:600;">Ready</span></td>
                    </tr>
                    <tr>
                        <td>#3493</td>
                        <td>Table 12</td>
                        <td><span style="color:#D97706;font-weight:600;">Pending</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>

@endsection