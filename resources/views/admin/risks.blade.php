@extends('admin.layouts.app')
@section('title', 'Risk Analytics')

@section('content')
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="bi bi-house-fill"></i></a>
    <i class="bi bi-chevron-right" style="font-size:10px;"></i><span>Risk Analytics</span>
  </div>
  <h1 class="page-title">Risk Analytics</h1>
  <p class="page-subtitle">Analisis distribusi dan skor risiko seluruh negara</p>
</div>

{{-- Risk Summary Cards --}}
<div class="row g-3 mb-4">
  @php
    $levels = [
      'Low'      => ['color' => '#10b981', 'icon' => 'bi-check-circle-fill'],
      'Medium'   => ['color' => '#f59e0b', 'icon' => 'bi-exclamation-circle-fill'],
      'High'     => ['color' => '#f97316', 'icon' => 'bi-exclamation-triangle-fill'],
      'Critical' => ['color' => '#ef4444', 'icon' => 'bi-x-octagon-fill'],
    ];
  @endphp
  @foreach($riskDist as $rd)
    @if(isset($levels[$rd->risk_level]))
      @php $l = $levels[$rd->risk_level]; @endphp
      <div class="col-6 col-md-3">
        <div class="admin-card p-3 text-center">
          <i class="bi {{ $l['icon'] }} mb-2" style="font-size:24px; color:{{ $l['color'] }};"></i>
          <div style="font-size:26px; font-weight:800; color:{{ $l['color'] }};">{{ $rd->count }}</div>
          <div style="font-size:12px; font-weight:600; color:var(--text-muted);">{{ $rd->risk_level }} Risk</div>
        </div>
      </div>
    @endif
  @endforeach
</div>

<div class="row g-3 mb-4">
  <div class="col-xl-4">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-pie-chart-fill text-danger"></i> Risk Distribution</span>
      </div>
      <div class="admin-card-body">
        <canvas id="riskChart" height="220"></canvas>
      </div>
    </div>
  </div>
  <div class="col-xl-8">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="bi bi-bar-chart-fill text-primary"></i> Top 10 Highest Risk Countries</span>
      </div>
      <div class="admin-card-body p-0">
        <table class="admin-table">
          <thead>
            <tr><th>#</th><th>Country</th><th>Risk Score</th><th>Risk Level</th><th>Updated</th></tr>
          </thead>
          <tbody>
            @foreach($risks->take(10) as $i => $r)
              <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong style="font-size:13px;">{{ $r->country?->name ?? 'N/A' }}</strong></td>
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div style="flex:1; height:6px; background:var(--border); border-radius:99px; overflow:hidden; max-width:80px;">
                      <div style="width:{{ min(100, ($r->risk_score ?? 0) * 10) }}%; height:100%; background:
                        {{ ($r->risk_score ?? 0) >= 8.5 ? '#ef4444' : (($r->risk_score ?? 0) >= 6 ? '#f97316' : (($r->risk_score ?? 0) >= 3.5 ? '#f59e0b' : '#10b981')) }};
                        border-radius:99px;"></div>
                    </div>
                    <strong style="font-size:13px;">{{ number_format($r->risk_score ?? 0, 1) }}</strong>
                  </div>
                </td>
                <td><span class="badge-{{ strtolower($r->risk_level ?? 'low') }}">{{ $r->risk_level }}</span></td>
                <td style="font-size:11px; color:var(--text-muted);">{{ $r->updated_at?->diffForHumans() }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
const rc = document.getElementById('riskChart');
if (rc) {
  new Chart(rc, {
    type: 'doughnut',
    data: {
      labels: @json($riskDist->pluck('risk_level')),
      datasets: [{
        data: @json($riskDist->pluck('count')),
        backgroundColor: ['#10b981', '#f59e0b', '#f97316', '#ef4444'],
        borderWidth: 0, hoverOffset: 8,
      }]
    },
    options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 12 } } } } }
  });
}
</script>
@endsection
