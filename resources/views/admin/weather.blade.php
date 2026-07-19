@extends('admin.layouts.app')
@section('title', 'Weather Sources')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Weather</span>
  </div>
  <h1 class="page-title">Weather Sources</h1>
  <p class="page-subtitle">Rekaman kondisi cuaca dan meteorologis dari berbagai wilayah</p>
</div>

<div class="admin-card">
  <div class="admin-card-body">
    <div class="table-responsive">
      <table id="tableWeather" class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Country</th>
            <th>Condition</th>
            <th>Wind Speed</th>
            <th>Wave Height</th>
            <th>Visibility</th>
            <th>Risk Level</th>
            <th>Recorded At</th>
          </tr>
        </thead>
        <tbody>
          @foreach($weathers as $i => $w)
            <tr>
              <td>{{ $i + 1 }}</td>
              <td><strong style="font-size:13px;">{{ $w->country?->name ?? 'N/A' }}</strong></td>
              <td style="font-size:13px; color:var(--text-muted);">{{ $w->weather_condition ?? 'N/A' }}</td>
              <td style="font-size:12px;">{{ $w->wind_speed ? number_format($w->wind_speed, 1) . ' km/h' : 'N/A' }}</td>
              <td style="font-size:12px;">{{ $w->wave_height ? number_format($w->wave_height, 1) . ' m' : 'N/A' }}</td>
              <td style="font-size:12px;">{{ $w->visibility ? number_format($w->visibility, 1) . ' km' : 'N/A' }}</td>
              <td>
                @php $rl = $w->weather_risk_level ?? 'Low'; @endphp
                <span class="badge-{{ strtolower($rl) }}">{{ $rl }}</span>
              </td>
              <td style="font-size:11px; color:var(--text-muted);">
                {{ $w->recorded_at ? \Carbon\Carbon::parse($w->recorded_at)->format('d M Y H:i') : 'N/A' }}
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
<script>$('#tableWeather').DataTable({ order: [[7, 'desc']] });</script>
@endsection
