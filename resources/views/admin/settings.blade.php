@extends('admin.layouts.app')
@section('title', 'Settings')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Settings</span>
  </div>
  <h1 class="page-title">System Settings</h1>
  <p class="page-subtitle">Konfigurasi sistem, API credentials, dan preferensi platform</p>
</div>

<div class="row g-4">

  {{-- General Settings --}}
  <div class="col-xl-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-sliders text-primary"></i> General Configuration</span>
      </div>
      <div class="admin-card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
          @csrf
          <div class="mb-3">
            <label class="admin-form-label">System Name</label>
            <input type="text" name="system_name" class="admin-form-control" value="{{ $settings['system_name'] }}" required>
          </div>
          <div class="mb-3">
            <label class="admin-form-label">System Version</label>
            <input type="text" name="system_version" class="admin-form-control" value="{{ $settings['system_version'] ?? '2.0' }}">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">Map Tile Provider</label>
            <select name="map_provider" class="admin-form-control">
              <option value="cartodb-voyager" {{ ($settings['map_provider'] ?? '') === 'cartodb-voyager' ? 'selected' : '' }}>CartoDB Voyager (Default)</option>
              <option value="cartodb-dark"    {{ ($settings['map_provider'] ?? '') === 'cartodb-dark'    ? 'selected' : '' }}>CartoDB Dark Matter</option>
              <option value="esri-satellite"  {{ ($settings['map_provider'] ?? '') === 'esri-satellite'  ? 'selected' : '' }}>Esri Satellite Imagery</option>
              <option value="osm"             {{ ($settings['map_provider'] ?? '') === 'osm'             ? 'selected' : '' }}>OpenStreetMap Standard</option>
            </select>
          </div>
          <button type="submit" class="btn-admin-primary w-100 justify-content-center">
            <i class="bi bi-check-circle-fill"></i> Save Configuration
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- SMTP Settings --}}
  <div class="col-xl-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-envelope-fill text-warning"></i> Email Configuration (SMTP)</span>
      </div>
      <div class="admin-card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
          @csrf
          <input type="hidden" name="system_name" value="{{ $settings['system_name'] }}">
          <input type="hidden" name="system_version" value="{{ $settings['system_version'] ?? '2.0' }}">
          <input type="hidden" name="map_provider" value="{{ $settings['map_provider'] ?? 'cartodb-voyager' }}">
          <input type="hidden" name="news_api_key" value="{{ $settings['news_api_key'] }}">
          <input type="hidden" name="weather_api_key" value="{{ $settings['weather_api_key'] }}">
          <input type="hidden" name="exchange_api_key" value="{{ $settings['exchange_api_key'] }}">
          <div class="mb-3">
            <label class="admin-form-label">SMTP Host</label>
            <input type="text" name="smtp_host" class="admin-form-control" value="{{ $settings['smtp_host'] ?? '' }}">
          </div>
          <div class="mb-3">
            <label class="admin-form-label">SMTP Port</label>
            <input type="number" name="smtp_port" class="admin-form-control" value="{{ $settings['smtp_port'] ?? 587 }}">
          </div>
          <button type="submit" class="btn-admin-primary w-100 justify-content-center">
            <i class="bi bi-check-circle-fill"></i> Save SMTP Config
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- API Keys --}}
  <div class="col-xl-12">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-key-fill text-success"></i> API Credentials</span>
      </div>
      <div class="admin-card-body">
        <form action="{{ route('admin.settings.update') }}" method="POST">
          @csrf
          <input type="hidden" name="system_name" value="{{ $settings['system_name'] }}">
          <input type="hidden" name="system_version" value="{{ $settings['system_version'] ?? '2.0' }}">
          <input type="hidden" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}">
          <input type="hidden" name="map_provider" value="{{ $settings['map_provider'] ?? 'cartodb-voyager' }}">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="admin-form-label">News API Key</label>
              <input type="password" name="news_api_key" class="admin-form-control" value="{{ $settings['news_api_key'] }}" placeholder="Enter API key...">
            </div>
            <div class="col-md-4">
              <label class="admin-form-label">Weather API Key</label>
              <input type="password" name="weather_api_key" class="admin-form-control" value="{{ $settings['weather_api_key'] }}" placeholder="Enter API key...">
            </div>
            <div class="col-md-4">
              <label class="admin-form-label">Exchange Rates API Key</label>
              <input type="password" name="exchange_api_key" class="admin-form-control" value="{{ $settings['exchange_api_key'] }}" placeholder="Enter API key...">
            </div>
          </div>
          <button type="submit" class="btn-admin-primary mt-3 justify-content-center">
            <i class="bi bi-check-circle-fill"></i> Save API Keys
          </button>
        </form>
      </div>
    </div>
  </div>

  {{-- System Info --}}
  <div class="col-xl-12">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-info-circle-fill text-accent" style="color:var(--accent);"></i> System Information</span>
      </div>
      <div class="admin-card-body">
        <div class="row g-3">
          @php
            $infos = [
              ['label' => 'Laravel Version', 'value' => app()->version()],
              ['label' => 'PHP Version',     'value' => PHP_VERSION],
              ['label' => 'Environment',     'value' => app()->environment()],
              ['label' => 'Debug Mode',      'value' => config('app.debug') ? 'Enabled' : 'Disabled'],
              ['label' => 'Timezone',        'value' => config('app.timezone')],
              ['label' => 'Database',        'value' => config('database.default')],
            ];
          @endphp
          @foreach($infos as $info)
            <div class="col-md-4 col-xl-2">
              <div class="p-3" style="background:var(--surface-2); border:1px solid var(--border); border-radius:10px;">
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:600; letter-spacing:.5px; margin-bottom:4px;">{{ $info['label'] }}</div>
                <div style="font-size:14px; font-weight:700; color:var(--text);">{{ $info['value'] }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
