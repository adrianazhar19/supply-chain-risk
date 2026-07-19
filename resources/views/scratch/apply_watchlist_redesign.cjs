const fs = require('fs');
const filePath = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(filePath, 'utf8');
const normContent = content.replace(/\r\n/g, '\n');

// ─── PART 1: Replace page-watchlist HTML section ────────────────────────────
const oldHtmlStart = '<!-- ─── PAGE: WATCHLIST ──────────────────────────── -->';
const oldHtmlEnd = '</section>\n\n<!-- ─── PAGE: REPORTS';

const sIdx = normContent.indexOf(oldHtmlStart);
if (sIdx === -1) { console.error("FATAL: Cannot find page-watchlist start!"); process.exit(1); }
const eIdx = normContent.indexOf(oldHtmlEnd, sIdx);
if (eIdx === -1) { console.error("FATAL: Cannot find page-watchlist end!"); process.exit(1); }

const targetHtml = normContent.substring(sIdx, eIdx + '</section>'.length);

const newHtml = `<!-- ─── PAGE: WATCHLIST ──────────────────────────── -->
<section class="content-page" id="page-watchlist">
  <style>
    .watchlist-card {
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 18px 20px;
      background: #fff;
      transition: all 0.2s ease;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    html[data-theme="dark"] .watchlist-card {
      background: var(--card-bg);
      border-color: var(--border-color);
    }
    .watchlist-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      border-color: #3b82f6;
    }
    .wl-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.68rem;
      font-weight: 700;
      padding: 3px 9px;
      border-radius: 12px;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .wl-badge.low    { background:#19875415;color:#198754;border:1px solid #19875430; }
    .wl-badge.medium { background:#fd7e1415;color:#fd7e14;border:1px solid #fd7e1430; }
    .wl-badge.high   { background:#dc354515;color:#dc3545;border:1px solid #dc354530; }
    .wl-badge.critical { background:#6f42c115;color:#6f42c1;border:1px solid #6f42c130; }
    .wl-alert-item {
      border-radius: 10px;
      padding: 10px 14px;
      margin-bottom: 8px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }
    .wl-filter-btn {
      font-size: 0.75rem;
      font-weight: 600;
      padding: 5px 12px;
      border-radius: 20px;
      border: 1px solid #dee2e6;
      background: transparent;
      color: var(--text-secondary, #475569);
      cursor: pointer;
      transition: all 0.15s;
    }
    .wl-filter-btn.active, .wl-filter-btn:hover {
      background: #0d6efd;
      color: #fff;
      border-color: #0d6efd;
    }
    .wl-kpi {
      border-radius: 12px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
  </style>

  <!-- KPI Summary Row -->
  <div class="row g-3 mb-4" id="watchlistKpiRow">
    <div class="col-6 col-md-3">
      <div class="wl-kpi" style="background:rgba(13,110,253,.07);">
        <div style="background:#0d6efd20;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="bi bi-globe" style="color:#0d6efd;font-size:1.1rem;"></i>
        </div>
        <div>
          <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);letter-spacing:.05em;">Watching</div>
          <div style="font-size:1.4rem;font-weight:800;color:var(--text-primary,#0f172a);" id="wlKpiTotal">0</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="wl-kpi" style="background:rgba(220,53,69,.07);">
        <div style="background:#dc354520;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="bi bi-exclamation-triangle" style="color:#dc3545;font-size:1.1rem;"></i>
        </div>
        <div>
          <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);letter-spacing:.05em;">High Risk</div>
          <div style="font-size:1.4rem;font-weight:800;color:#dc3545;" id="wlKpiHighRisk">0</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="wl-kpi" style="background:rgba(245,158,11,.07);">
        <div style="background:#f59e0b20;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="bi bi-speedometer2" style="color:#f59e0b;font-size:1.1rem;"></i>
        </div>
        <div>
          <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);letter-spacing:.05em;">Avg Risk</div>
          <div style="font-size:1.4rem;font-weight:800;color:var(--text-primary,#0f172a);" id="wlKpiAvgRisk">–</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="wl-kpi" style="background:rgba(111,66,193,.07);">
        <div style="background:#6f42c120;width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <i class="bi bi-bell" style="color:#6f42c1;font-size:1.1rem;"></i>
        </div>
        <div>
          <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);letter-spacing:.05em;">Alerts</div>
          <div style="font-size:1.4rem;font-weight:800;color:#6f42c1;" id="wlKpiAlerts">0</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Watchlist Cards -->
    <div class="col-xl-8">
      <div class="card p-3">
        <div class="section-header mb-3">
          <div class="section-title"><span class="stat-icon indigo" style="width:32px;height:32px;font-size:.9rem;border-radius:8px;"><i class="bi bi-bookmark-star"></i></span>My Watchlist</div>
          <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#addWatchlistModal">
            <i class="bi bi-plus me-1"></i>Add Country
          </button>
        </div>

        <!-- Search + Sort Toolbar -->
        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
          <div class="position-relative flex-grow-1" style="max-width:260px;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="font-size:.8rem;"></i>
            <input type="text" id="wlSearch" class="form-control form-control-sm" placeholder="Search countries..." style="padding-left:28px;border-radius:8px;">
          </div>
          <select id="wlSort" class="form-select form-select-sm" style="width:160px;border-radius:8px;">
            <option value="name">Sort: Country Name</option>
            <option value="risk">Sort: Risk Score</option>
            <option value="gdp">Sort: GDP</option>
            <option value="added">Sort: Recently Added</option>
          </select>
        </div>

        <!-- Quick Filter Chips -->
        <div class="d-flex flex-wrap gap-2 mb-3" id="wlFilterChips">
          <button class="wl-filter-btn active" data-filter="all">All</button>
          <button class="wl-filter-btn" data-filter="high">High Risk</button>
          <button class="wl-filter-btn" data-filter="medium">Medium Risk</button>
          <button class="wl-filter-btn" data-filter="low">Low Risk</button>
          <button class="wl-filter-btn" data-filter="Asia">Asia</button>
          <button class="wl-filter-btn" data-filter="Europe">Europe</button>
          <button class="wl-filter-btn" data-filter="Americas">Americas</button>
          <button class="wl-filter-btn" data-filter="Africa">Africa</button>
          <button class="wl-filter-btn" data-filter="Oceania">Oceania</button>
        </div>

        <div id="watchlistItems" class="row g-3">
          <!-- Cards rendered by JS -->
        </div>
      </div>
    </div>

    <!-- Alerts Panel -->
    <div class="col-xl-4">
      <div class="card p-3 h-100">
        <div class="section-header mb-3">
          <div class="section-title" style="font-size:.9rem;"><span class="stat-icon purple" style="width:28px;height:28px;font-size:.8rem;border-radius:7px;"><i class="bi bi-bell"></i></span>Risk Notifications</div>
          <span id="wlAlertCount" class="badge" style="background:#6f42c120;color:#6f42c1;border:1px solid #6f42c130;font-size:.7rem;">0</span>
        </div>
        <div id="watchlistAlerts" style="max-height:600px;overflow-y:auto;">
          <!-- Alerts rendered by JS -->
        </div>
      </div>
    </div>
  </div>
</section>`;

let updated = normContent.replace(targetHtml, () => newHtml);

// ─── PART 2: Replace loadWatchlistPage() function ────────────────────────────

const oldFnStart = 'async function loadWatchlistPage() {';
const oldFnEnd   = '  if (STATE.currentPage === \'watchlist\') loadWatchlistPage();\n});\n';

const fnSIdx = updated.indexOf(oldFnStart);
if (fnSIdx === -1) { console.error("FATAL: Cannot find loadWatchlistPage start!"); process.exit(1); }
const fnEIdx = updated.indexOf(oldFnEnd, fnSIdx);
if (fnEIdx === -1) { console.error("FATAL: Cannot find loadWatchlistPage end!"); process.exit(1); }

const targetFn = updated.substring(fnSIdx, fnEIdx + oldFnEnd.length);

const newFn = `async function loadWatchlistPage() {
  const DEMO_WATCHLIST = [
    { id: 0, name: 'United States', code: 'us', region: 'Americas', currency: 'USD', risk_level: 'Low', risk_score: 18.2, gdp: 25.46e12, inflation: 3.2, temperature: 22, weather: 'Clear' },
    { id: 0, name: 'China', code: 'cn', region: 'Asia', currency: 'CNY', risk_level: 'Medium', risk_score: 45.6, gdp: 17.73e12, inflation: 0.9, temperature: 26, weather: 'Cloudy' },
    { id: 0, name: 'Japan', code: 'jp', region: 'Asia', currency: 'JPY', risk_level: 'Low', risk_score: 22.1, gdp: 4.23e12, inflation: 3.1, temperature: 28, weather: 'Partly Cloudy' },
    { id: 0, name: 'Germany', code: 'de', region: 'Europe', currency: 'EUR', risk_level: 'Low', risk_score: 20.4, gdp: 4.07e12, inflation: 2.4, temperature: 17, weather: 'Rainy' },
    { id: 0, name: 'Singapore', code: 'sg', region: 'Asia', currency: 'SGD', risk_level: 'Low', risk_score: 15.8, gdp: 0.497e12, inflation: 2.7, temperature: 31, weather: 'Thunderstorm' },
    { id: 0, name: 'Indonesia', code: 'id', region: 'Asia', currency: 'IDR', risk_level: 'Medium', risk_score: 52.3, gdp: 1.32e12, inflation: 4.8, temperature: 30, weather: 'Cloudy' },
    { id: 0, name: 'United Kingdom', code: 'gb', region: 'Europe', currency: 'GBP', risk_level: 'Low', risk_score: 24.7, gdp: 3.07e12, inflation: 4.0, temperature: 14, weather: 'Rainy' },
    { id: 0, name: 'Australia', code: 'au', region: 'Oceania', currency: 'AUD', risk_level: 'Low', risk_score: 16.2, gdp: 1.69e12, inflation: 3.6, temperature: 19, weather: 'Clear' },
  ];

  const DEMO_ALERTS = [
    { icon: 'bi-graph-up-arrow', country: 'ar', name: 'Argentina', title: 'High Inflation Detected', severity: 'critical', time: '2 hours ago' },
    { icon: 'bi-anchor', country: 'sg', name: 'Singapore', title: 'Port Congestion Warning', severity: 'high', time: '4 hours ago' },
    { icon: 'bi-cloud-lightning-rain', country: 'jp', name: 'Japan', title: 'Storm Warning Issued', severity: 'high', time: '6 hours ago' },
    { icon: 'bi-currency-exchange', country: 'gb', name: 'United Kingdom', title: 'Currency Volatility Alert', severity: 'medium', time: '12 hours ago' },
    { icon: 'bi-truck', country: 'de', name: 'Germany', title: 'Supply Chain Delay Detected', severity: 'medium', time: '1 day ago' },
  ];

  const stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  const watchEl = document.getElementById('watchlistItems');
  const alertEl = document.getElementById('watchlistAlerts');

  // Resolve watchlist items: use real data from STATE.countries, fall back to demo
  let items = [];
  if (stored.length > 0) {
    const ids = stored.map(s => s.countryId);
    items = STATE.countries.filter(c => ids.includes(c.id));
    // If IDs don't match (STATE not loaded yet), show demo
    if (items.length === 0) items = DEMO_WATCHLIST;
  } else {
    // No watchlist saved — show demo data
    items = DEMO_WATCHLIST;
  }

  // Build alerts from real items (High/Critical risk) + fill with demo alerts if needed
  let alerts = [];
  items.forEach(c => {
    const lvl = (c.risk_level || '').toLowerCase();
    if (lvl === 'high' || lvl === 'critical') {
      alerts.push({
        icon: 'bi-exclamation-triangle-fill',
        country: (c.code || '').toLowerCase(),
        name: c.name,
        title: c.risk_level + ' Risk Alert',
        severity: lvl,
        time: 'Just now'
      });
    }
  });
  // Always pad to at least 3 demo alerts
  if (alerts.length < 3) {
    const needed = Math.max(0, 5 - alerts.length);
    alerts = [...alerts, ...DEMO_ALERTS.slice(0, needed)];
  }

  // Update KPI counters
  const totalEl    = document.getElementById('wlKpiTotal');
  const highRiskEl = document.getElementById('wlKpiHighRisk');
  const avgRiskEl  = document.getElementById('wlKpiAvgRisk');
  const alertCntEl = document.getElementById('wlKpiAlerts');
  const alertBadgeEl = document.getElementById('wlAlertCount');

  const highCount = items.filter(c => ['High','Critical'].includes(c.risk_level)).length;
  const avgRisk   = items.length > 0 ? (items.reduce((s,c) => s + parseFloat(c.risk_score||0), 0) / items.length).toFixed(1) : '–';

  if (totalEl)    totalEl.textContent    = items.length;
  if (highRiskEl) highRiskEl.textContent = highCount;
  if (avgRiskEl)  avgRiskEl.textContent  = avgRisk;
  if (alertCntEl) alertCntEl.textContent = alerts.length;
  if (alertBadgeEl) alertBadgeEl.textContent = alerts.length;

  // Weather icon mapping
  function weatherIcon(cond) {
    const c = (cond || '').toLowerCase();
    if (c.includes('storm') || c.includes('thunder')) return '⛈️';
    if (c.includes('rain')) return '🌧️';
    if (c.includes('cloud')) return '⛅';
    if (c.includes('snow')) return '❄️';
    return '☀️';
  }

  // Store current active filter state
  window._wlCurrentFilter = window._wlCurrentFilter || 'all';
  window._wlCurrentSearch = window._wlCurrentSearch || '';

  function renderCards(list) {
    const isDemo = stored.length === 0;
    const demoNote = isDemo
      ? '<div class="col-12"><div style="font-size:.75rem;color:var(--text-muted);padding:6px 10px;background:rgba(245,158,11,.08);border-radius:8px;border:1px solid rgba(245,158,11,.2);"><i class="bi bi-info-circle me-1 text-warning"></i>No countries added yet. Showing sample watchlist data.</div></div>'
      : '';

    if (list.length === 0) {
      watchEl.innerHTML = demoNote + '<div class="col-12"><div class="text-center py-4 text-muted"><i class="bi bi-search" style="font-size:2rem;opacity:.3;display:block;margin-bottom:8px;"></i>No countries match your filter.</div></div>';
      return;
    }

    const cards = list.map(c => {
      const lvl   = (c.risk_level || 'Low').toLowerCase();
      const score = parseFloat(c.risk_score||0).toFixed(1);
      const gdpB  = c.gdp > 0 ? '$' + (c.gdp / 1e12).toFixed(2) + 'T' : 'N/A';
      const flagCode = (c.code || '').toLowerCase();
      const flagHtml = flagCode
        ? \`<img src="https://flagcdn.com/w40/\${flagCode}.png" width="36" style="border-radius:5px;border:1px solid #e2e8f0;" alt="">\`
        : '<span style="width:36px;height:24px;display:inline-block;background:#e2e8f0;border-radius:4px;"></span>';
      const wIcon  = weatherIcon(c.weather);
      const temp   = c.temperature ? c.temperature + '°C' : 'N/A';
      const region = c.region || 'Unknown';
      const curr   = c.currency || 'USD';
      const isReal = (c.id && c.id > 0);

      return \`
        <div class="col-sm-6 col-xl-6 wl-card-item" data-risk="\${lvl}" data-region="\${region}" data-name="\${c.name}" data-score="\${score}" data-gdp="\${c.gdp||0}" data-added="0">
          <div class="watchlist-card">
            <div class="d-flex align-items-start justify-content-between">
              <div class="d-flex align-items-center gap-2">
                \${flagHtml}
                <div>
                  <div style="font-weight:700;font-size:.9rem;color:var(--text-primary,#0f172a);">\${c.name}</div>
                  <div style="font-size:.72rem;color:var(--text-muted,#94a3b8);">\${region} · \${curr}</div>
                </div>
              </div>
              <div class="d-flex align-items-center gap-1">
                <span class="wl-badge \${lvl}">\${c.risk_level||'Low'}</span>
                \${isReal ? \`<button onclick="event.stopPropagation(); removeFromWatchlist(\${c.id})" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem;padding:0 2px;" title="Remove"><i class="bi bi-x-circle"></i></button>\` : ''}
              </div>
            </div>
            <div class="row g-2">
              <div class="col-4">
                <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);">Risk Score</div>
                <div style="font-size:1.05rem;font-weight:800;font-family:var(--font-mono);">\${score}</div>
              </div>
              <div class="col-4">
                <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);">GDP</div>
                <div style="font-size:.85rem;font-weight:600;">\${gdpB}</div>
              </div>
              <div class="col-4">
                <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);">Weather</div>
                <div style="font-size:.85rem;">\${wIcon} \${temp}</div>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-center" style="border-top:1px solid #f1f5f9;padding-top:8px;margin-top:2px;">
              <div style="font-size:.65rem;color:var(--text-muted,#94a3b8);">Updated: \${new Date().toLocaleTimeString()}</div>
              <button class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.72rem;padding:3px 10px;" onclick="\${isReal ? \`viewCountry(\${c.id})\` : '\"'}" \${isReal ? '' : 'disabled'}>
                <i class="bi bi-person-badge me-1"></i>Profile
              </button>
            </div>
          </div>
        </div>
      \`;
    });

    watchEl.innerHTML = demoNote + cards.join('');
  }

  function applyFilters() {
    const filter = window._wlCurrentFilter || 'all';
    const search = (window._wlCurrentSearch || '').toLowerCase();
    const sorted = [...items].sort((a, b) => {
      const sort = document.getElementById('wlSort')?.value || 'name';
      if (sort === 'risk')  return parseFloat(b.risk_score||0) - parseFloat(a.risk_score||0);
      if (sort === 'gdp')   return parseFloat(b.gdp||0) - parseFloat(a.gdp||0);
      if (sort === 'added') return 0;
      return (a.name||'').localeCompare(b.name||'');
    });

    const filtered = sorted.filter(c => {
      const matchSearch = !search || (c.name||'').toLowerCase().includes(search);
      if (!matchSearch) return false;
      if (filter === 'all') return true;
      if (filter === 'high')   return ['high','critical'].includes((c.risk_level||'').toLowerCase());
      if (filter === 'medium') return (c.risk_level||'').toLowerCase() === 'medium';
      if (filter === 'low')    return (c.risk_level||'').toLowerCase() === 'low';
      return (c.region||'').toLowerCase().includes(filter.toLowerCase());
    });
    renderCards(filtered);
  }

  applyFilters();

  // Filter chip events
  const chipContainer = document.getElementById('wlFilterChips');
  if (chipContainer && !chipContainer.__wired) {
    chipContainer.__wired = true;
    chipContainer.addEventListener('click', e => {
      const btn = e.target.closest('.wl-filter-btn');
      if (!btn) return;
      chipContainer.querySelectorAll('.wl-filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      window._wlCurrentFilter = btn.dataset.filter;
      applyFilters();
    });
  }

  // Search
  const searchEl = document.getElementById('wlSearch');
  if (searchEl && !searchEl.__wired) {
    searchEl.__wired = true;
    searchEl.addEventListener('input', function() {
      window._wlCurrentSearch = this.value;
      applyFilters();
    });
  }

  // Sort
  const sortEl = document.getElementById('wlSort');
  if (sortEl && !sortEl.__wired) {
    sortEl.__wired = true;
    sortEl.addEventListener('change', () => applyFilters());
  }

  // Build Alerts panel
  const severityColors = { critical: '#6f42c1', high: '#dc3545', medium: '#fd7e14', low: '#198754' };
  alertEl.innerHTML = alerts.map(a => {
    const color = severityColors[a.severity] || '#94a3b8';
    const flagHtml = a.country
      ? \`<img src="https://flagcdn.com/w20/\${a.country}.png" width="16" style="border-radius:2px;flex-shrink:0;" alt="">\`
      : '';
    return \`
      <div class="wl-alert-item" style="background:\${color}10;border:1px solid \${color}25;">
        <div style="font-size:1rem;color:\${color};flex-shrink:0;">
          <i class="bi \${a.icon}"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div class="d-flex align-items-center gap-1 mb-1">
            \${flagHtml}
            <span style="font-size:.75rem;font-weight:700;color:var(--text-primary,#0f172a);">\${a.name}</span>
            <span class="wl-badge \${a.severity}" style="margin-left:auto;">\${a.severity}</span>
          </div>
          <div style="font-size:.8rem;font-weight:600;color:var(--text-primary,#0f172a);">\${a.title}</div>
          <div style="font-size:.68rem;color:var(--text-muted,#94a3b8);">\${a.time}</div>
        </div>
        <button class="btn btn-sm" style="border-radius:8px;font-size:.65rem;padding:3px 8px;background:\${color}15;color:\${color};border:1px solid \${color}30;flex-shrink:0;">View</button>
      </div>
    \`;
  }).join('');
}

function removeFromWatchlist(id) {
  let stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  stored = stored.filter(s => s.countryId !== id);
  localStorage.setItem('scri_watchlist', JSON.stringify(stored));
  showToast('info', 'Removed from Watchlist', '');
  loadWatchlistPage();
}

document.getElementById('addWatchlistBtn').addEventListener('click', () => {
  const id = parseInt(document.getElementById('watchlistCountrySelect').value);
  const country = STATE.countries.find(c => c.id === id);
  if (!id || !country) return;
  const stored = JSON.parse(localStorage.getItem('scri_watchlist') || '[]');
  if (stored.find(s => s.countryId === id)) {
    showToast('warning', 'Already in Watchlist', country.name + ' is already being monitored.');
    return;
  }
  stored.push({ countryId: id, name: country.name, addedAt: new Date().toISOString() });
  localStorage.setItem('scri_watchlist', JSON.stringify(stored));
  bootstrap.Modal.getInstance(document.getElementById('addWatchlistModal'))?.hide();
  showToast('success', 'Added to Watchlist', country.name + ' is now being monitored.');
  if (STATE.currentPage === 'watchlist') loadWatchlistPage();
});
`;

updated = updated.replace(targetFn, () => newFn);

// Write out
const finalContent = updated.replace(/\n/g, '\r\n');
fs.writeFileSync(filePath, finalContent, 'utf8');
console.log("Saved dashboard.blade.php with Watchlist redesign!");
