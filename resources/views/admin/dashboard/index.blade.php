@extends('admin.layouts.app')
@section('title', 'Admin Dashboard')

@section('content')

<!-- ═══ BREADCRUMB & HEADER ═══ -->
<div class="page-header">
  <div class="breadcrumb-admin">
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-house"></i></a>
    <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
    <span>Dashboard</span>
  </div>
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
      <h1 class="page-title">Dashboard Overview</h1>
      <p class="page-subtitle">Welcome back, {{ Auth::user()->name }}. Here is a quick look at your system metrics.</p>
    </div>
    <div class="d-flex gap-2">
      <button class="btn-admin-secondary" onclick="window.location.reload()"><i class="fa-solid fa-sync"></i> Refresh Data</button>
      <a href="{{ route('admin.reports') }}" class="btn-admin-primary"><i class="fa-solid fa-file-invoice-dollar"></i> View Reports</a>
    </div>
  </div>
</div>

<!-- ═══ 8 METRIC CARDS ═══ -->
<div class="row g-3 mb-4">
  
  <!-- Total Users -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Total Users</div>
        <div class="kpi-value" data-counter="{{ $stats['total_users'] }}">{{ $stats['total_users'] }}</div>
      </div>
      <div class="kpi-icon blue"><i class="fa-solid fa-users"></i></div>
    </div>
  </div>

  <!-- Total Countries -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Total Countries</div>
        <div class="kpi-value" data-counter="{{ $stats['total_countries'] }}">{{ $stats['total_countries'] }}</div>
      </div>
      <div class="kpi-icon green"><i class="fa-solid fa-globe"></i></div>
    </div>
  </div>

  <!-- Total Ports -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Total Ports</div>
        <div class="kpi-value" data-counter="{{ $stats['total_ports'] }}">{{ $stats['total_ports'] }}</div>
      </div>
      <div class="kpi-icon indigo"><i class="fa-solid fa-anchor"></i></div>
    </div>
  </div>

  <!-- Total Watchlists -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Total Watchlists</div>
        <div class="kpi-value" data-counter="{{ $stats['total_watchlists'] }}">{{ $stats['total_watchlists'] }}</div>
      </div>
      <div class="kpi-icon orange"><i class="fa-solid fa-eye"></i></div>
    </div>
  </div>

  <!-- Total News -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Total News</div>
        <div class="kpi-value" data-counter="{{ $stats['total_news'] }}">{{ $stats['total_news'] }}</div>
      </div>
      <div class="kpi-icon purple"><i class="fa-solid fa-newspaper"></i></div>
    </div>
  </div>

  <!-- Published Articles -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Published News</div>
        <div class="kpi-value" data-counter="{{ $stats['published_articles'] }}">{{ $stats['published_articles'] }}</div>
      </div>
      <div class="kpi-icon teal"><i class="fa-solid fa-circle-check"></i></div>
    </div>
  </div>

  <!-- Draft Articles -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Draft News</div>
        <div class="kpi-value" data-counter="{{ $stats['draft_articles'] }}">{{ $stats['draft_articles'] }}</div>
      </div>
      <div class="kpi-icon yellow"><i class="fa-solid fa-file-signature"></i></div>
    </div>
  </div>

  <!-- Archived Articles -->
  <div class="col-12 col-sm-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-details">
        <div class="kpi-label">Archived News</div>
        <div class="kpi-value" data-counter="{{ $stats['archived_articles'] }}">{{ $stats['archived_articles'] }}</div>
      </div>
      <div class="kpi-icon red"><i class="fa-solid fa-box-archive"></i></div>
    </div>
  </div>

</div>

<!-- ═══ VISUALIZATION & CHARTS ROW ═══ -->
<div class="row g-4 mb-4">
  
  <!-- User Growth Chart -->
  <div class="col-12 col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-line-chart me-1"></i> User Growth Trend</span>
        <span class="badge bg-light text-dark">7 Days</span>
      </div>
      <div class="admin-card-body">
        <canvas id="chartUserGrowth" style="max-height:220px;"></canvas>
      </div>
    </div>
  </div>

  <!-- News by Category Chart -->
  <div class="col-12 col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-pie-chart me-1"></i> News by Category</span>
        <span class="badge bg-light text-dark">Full cache</span>
      </div>
      <div class="admin-card-body">
        <canvas id="chartNewsCategory" style="max-height:220px;"></canvas>
      </div>
    </div>
  </div>

  <!-- Country Distribution (Ports Count) -->
  <div class="col-12 col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-map-location-dot me-1"></i> Top Ports Count by Country</span>
        <span class="badge bg-light text-dark">Data distribution</span>
      </div>
      <div class="admin-card-body">
        <canvas id="chartCountryDist" style="max-height:220px;"></canvas>
      </div>
    </div>
  </div>

  <!-- Risk Distribution Chart -->
  <div class="col-12 col-lg-6">
    <div class="admin-card">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-triangle-exclamation me-1"></i> Global Risk Level Distribution</span>
        <span class="badge bg-light text-dark">Metrics</span>
      </div>
      <div class="admin-card-body">
        <canvas id="chartRiskDistribution" style="max-height:220px;"></canvas>
      </div>
    </div>
  </div>

</div>

<!-- ═══ SYSTEM SUMMARY & QUICK ACTIONS ═══ -->
<div class="row g-4 mb-4">

  <!-- System Summary Panel -->
  <div class="col-12 col-md-6">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-server me-1"></i> System Summary</span>
      </div>
      <div class="admin-card-body">
        
        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-circle text-danger" style="font-size:10px;"></i> High / Critical Risk Alerts</span>
          <span class="summary-value text-danger">{{ $systemSummary['high_risk'] }}</span>
        </div>

        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-circle text-warning" style="font-size:10px;"></i> Medium Risk Alerts</span>
          <span class="summary-value text-warning">{{ $systemSummary['medium_risk'] }}</span>
        </div>

        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-circle text-success" style="font-size:10px;"></i> Low Risk Alerts</span>
          <span class="summary-value text-success">{{ $systemSummary['low_risk'] }}</span>
        </div>

        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-link"></i> API Gateway Status</span>
          <span class="status-pill online">{{ $systemSummary['api_status'] }}</span>
        </div>

        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-database"></i> Database Connection</span>
          <span class="status-pill {{ $systemSummary['db_status'] === 'Connected' ? 'online' : 'offline' }}">{{ $systemSummary['db_status'] }}</span>
        </div>

        <div class="summary-item">
          <span class="summary-label"><i class="fa-solid fa-code-branch"></i> Environment</span>
          <span class="badge bg-secondary text-uppercase">{{ $systemSummary['env'] }}</span>
        </div>

      </div>
    </div>
  </div>

  <!-- Quick Actions Panel -->
  <div class="col-12 col-md-6">
    <div class="admin-card h-100">
      <div class="admin-card-header">
        <span class="admin-card-title"><i class="fa-solid fa-cubes me-1"></i> Quick Action shortcuts</span>
      </div>
      <div class="admin-card-body d-flex flex-column justify-content-center">
        <p class="text-muted mb-3" style="font-size:13px;">Frequently accessed management consoles:</p>
        <div class="quick-actions-grid">
          
          <a href="{{ route('admin.users') }}" class="quick-action-btn">
            <i class="fa-solid fa-users text-primary"></i>
            <span>Users</span>
          </a>

          <a href="{{ route('admin.countries') }}" class="quick-action-btn">
            <i class="fa-solid fa-globe text-success"></i>
            <span>Countries</span>
          </a>

          <a href="{{ route('admin.ports') }}" class="quick-action-btn">
            <i class="fa-solid fa-anchor text-info"></i>
            <span>Ports</span>
          </a>

          <a href="{{ route('admin.news') }}" class="quick-action-btn">
            <i class="fa-solid fa-newspaper text-warning"></i>
            <span>News</span>
          </a>

          <a href="{{ route('admin.reports') }}" class="quick-action-btn">
            <i class="fa-solid fa-file-contract text-danger"></i>
            <span>Reports</span>
          </a>

          <a href="{{ route('admin.settings') }}" class="quick-action-btn">
            <i class="fa-solid fa-cog text-secondary"></i>
            <span>Settings</span>
          </a>

        </div>
      </div>
    </div>
  </div>

</div>

<!-- ═══ RECENT ACTIVITIES LOG ═══ -->
<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title"><i class="fa-solid fa-list-check me-1"></i> Administrator Activity Log</span>
    <a href="{{ route('admin.logs') }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold" style="font-size:12.5px;">View Full Logs</a>
  </div>
  <div class="admin-card-body p-0">
    <div class="table-responsive">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Timestamp</th>
            <th>Administrator</th>
            <th>Action</th>
            <th>Description</th>
          </tr>
        </thead>
        <tbody>
          @forelse($logs as $log)
            <tr>
              <td style="white-space:nowrap; font-family:'JetBrains Mono', monospace; font-size:12.5px; color:var(--text-muted);">
                {{ $log->created_at->format('Y-m-d H:i:s') }}
              </td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-circle" style="width:26px; height:26px; font-size:11px;">
                    {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                  </div>
                  <span style="font-weight:600;">{{ $log->user?->name ?? 'System' }}</span>
                </div>
              </td>
              <td>
                <span class="badge bg-secondary text-uppercase" style="font-size:10.5px; padding:3px 6px;">{{ $log->action }}</span>
              </td>
              <td style="color:var(--text-muted);">
                {{ $log->description }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center py-4 text-muted">No administrator activities recorded yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
// ── User Growth Chart (Line) ─────────────────────────────────
const trendLabels = @json($userTrend->pluck('date'));
const trendData   = @json($userTrend->pluck('count'));

const days = [];
const counts = [];
for (let i = 6; i >= 0; i--) {
  const d = new Date();
  d.setDate(d.getDate() - i);
  const key = d.toISOString().split('T')[0];
  days.push(d.toLocaleDateString('en-US', { weekday:'short', day:'numeric' }));
  const idx = trendLabels.findIndex(l => l === key);
  counts.push(idx >= 0 ? trendData[idx] : 0);
}

const ctxGrowth = document.getElementById('chartUserGrowth');
if (ctxGrowth) {
  new Chart(ctxGrowth, {
    type: 'line',
    data: {
      labels: days,
      datasets: [{
        label: 'Registrations',
        data: counts,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.05)',
        borderWidth: 2,
        tension: 0.35,
        fill: true,
        pointBackgroundColor: '#2563eb',
        pointRadius: 4,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
      }
    }
  });
}

// ── News by Category Chart (Doughnut/Pie) ───────────────────────────
const rawNews = @json($newsByCategory);
const newsLabels = Object.keys(rawNews);
const newsValues = Object.values(rawNews);

const ctxNews = document.getElementById('chartNewsCategory');
if (ctxNews) {
  new Chart(ctxNews, {
    type: 'doughnut',
    data: {
      labels: newsLabels.map(l => l.toUpperCase()),
      datasets: [{
        data: newsValues.length ? newsValues : [1],
        backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#0ea5e9', '#64748b'],
        borderWidth: 1,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
      }
    }
  });
}

// ── Country Distribution Chart (Bar) ──────────────────────────────
const rawCountries = @json($countryDist);
const countryLabels = rawCountries.map(c => c.country_name);
const countryValues = rawCountries.map(c => c.count);

const ctxCountry = document.getElementById('chartCountryDist');
if (ctxCountry) {
  new Chart(ctxCountry, {
    type: 'bar',
    data: {
      labels: countryLabels,
      datasets: [{
        data: countryValues,
        backgroundColor: '#4f46e5',
        borderRadius: 4,
        barThickness: 16
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
        x: { grid: { display: false } }
      }
    }
  });
}

// ── Risk Level Distribution Chart (Pie) ──────────────────────────
const rawRisk = @json($riskDist);
const riskLabels = ['Critical', 'High', 'Medium', 'Low'];
const riskColors = ['#dc2626', '#f97316', '#f59e0b', '#10b981'];
const riskValues = riskLabels.map(lvl => rawRisk[lvl] || 0);

const ctxRisk = document.getElementById('chartRiskDistribution');
if (ctxRisk) {
  new Chart(ctxRisk, {
    type: 'pie',
    data: {
      labels: riskLabels,
      datasets: [{
        data: riskValues.some(v => v > 0) ? riskValues : [1],
        backgroundColor: riskColors,
        borderWidth: 1,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
      }
    }
  });
}
</script>
@endsection