@extends('admin.layouts.app')
@section('title', 'Weather Sources')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Weather</span>
  </div>
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="page-title">Weather Sources</h1>
      <p class="page-subtitle">Rekaman kondisi cuaca dan meteorologis dari berbagai wilayah</p>
    </div>
    <div>
      <form action="{{ route('admin.weather.sync') }}" method="POST" class="m-0">
        @csrf
        <button type="submit" class="btn-admin-primary">
          <i class="bi bi-cloud-arrow-down-fill me-1"></i> Sync Weather Now
        </button>
      </form>
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableWeather" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Country</th>
            <th>Temperature</th>
            <th>Rainfall</th>
            <th>Wind Speed</th>
            <th>Storm Risk</th>
            <th>Fetched At</th>
          </tr>
        </thead>
        <tbody>
          @foreach($weathers as $i => $w)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong style="font-size:13px;">{{ $w->country?->name ?? 'N/A' }}</strong></td>
              <td style="font-size:13px; font-weight: 600;">{{ $w->temperature !== null ? number_format($w->temperature, 1) . ' °C' : 'N/A' }}</td>
              <td style="font-size:12px;">{{ $w->rainfall !== null ? number_format($w->rainfall, 1) . ' mm' : '0.0 mm' }}</td>
              <td style="font-size:12px;">{{ $w->wind_speed !== null ? number_format($w->wind_speed, 1) . ' km/h' : 'N/A' }}</td>
              <td>
                @php
                  $risk = $w->storm_risk ?? 0;
                  $badge = $risk >= 75 ? 'critical' : ($risk >= 50 ? 'high' : ($risk >= 25 ? 'medium' : 'low'));
                @endphp
                <span class="risk-pill {{ $badge }}">{{ $risk }}%</span>
              </td>
              <td style="font-size:11px; color:var(--text-muted);">
                {{ $w->fetched_at ? \Carbon\Carbon::parse($w->fetched_at)->format('d M Y H:i') : 'N/A' }}
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>$('#tableWeather').DataTable({ order: [[6, 'desc']] });</script>
@endsection
