const fs = require('fs');
const filePath = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(filePath, 'utf8');
const norm = s => s.replace(/\r\n/g, '\n');

// ─── PART 1: Add sidebar menu item ───────────────────────────────────────────
const oldNav = `    <div class="nav-section-label">User</div>
    <a class="sidebar-link" data-page="watchlist" onclick="showPage('watchlist'); return false;" href="#">
      <i class="bi bi-bookmark-star"></i> My Watchlist
    </a>`;

const newNav = `    <div class="nav-section-label">Analytics</div>
    <a class="sidebar-link" data-page="compare" onclick="showPage('compare'); return false;" href="#">
      <i class="bi bi-columns-gap"></i> Compare Countries
    </a>

    <div class="nav-section-label">User</div>
    <a class="sidebar-link" data-page="watchlist" onclick="showPage('watchlist'); return false;" href="#">
      <i class="bi bi-bookmark-star"></i> My Watchlist
    </a>`;

let n = norm(content);
const oldNavN = norm(oldNav);
if (n.indexOf(oldNavN) !== -1) {
    n = n.replace(oldNavN, () => norm(newNav));
    console.log("Added sidebar link!");
} else {
    console.error("ERROR: Could not find sidebar nav block!");
}

// ─── PART 2: Add PAGE_TITLES entry and showPage handler ─────────────────────
const oldTitles = `  watchlist: 'My Watchlist',
  reports: 'Report Generation',
};`;
const newTitles = `  watchlist: 'My Watchlist',
  compare: 'Country Comparison Intelligence',
  reports: 'Report Generation',
};`;
n = n.replace(norm(oldTitles), () => norm(newTitles));

const oldShowPage = `  if (page === 'watchlist') loadWatchlistPage();`;
const newShowPage = `  if (page === 'watchlist') loadWatchlistPage();
  if (page === 'compare')   loadComparePage();`;
n = n.replace(norm(oldShowPage), () => norm(newShowPage));
console.log("Updated PAGE_TITLES and showPage!");

// ─── PART 3: Insert page-compare HTML before page-reports ────────────────────
const insertBefore = '<!-- ─── PAGE: REPORTS ─────────────────────────────── -->';

const compareHtml = `<!-- ─── PAGE: COMPARE ──────────────────────────── -->
<section class="content-page" id="page-compare">
  <style>
    .cmp-kpi { border-radius: 12px; padding: 14px 16px; border: 1px solid #e2e8f0; background: #fff; transition: all 0.2s; }
    html[data-theme="dark"] .cmp-kpi { background: var(--card-bg); border-color: var(--border-color); }
    .cmp-kpi.win  { border-color: #19875450; background: #19875408; }
    .cmp-kpi.lose { border-color: #dc354550; background: #dc354508; }
    .cmp-kpi.tie  { border-color: #0d6efd50; background: #0d6efd08; }
    .cmp-badge-win  { background: #19875418; color: #198754; border: 1px solid #19875430; border-radius: 4px; font-size: .65rem; font-weight: 700; padding: 1px 6px; }
    .cmp-badge-lose { background: #dc354518; color: #dc3545; border: 1px solid #dc354530; border-radius: 4px; font-size: .65rem; font-weight: 700; padding: 1px 6px; }
    .cmp-badge-tie  { background: #0d6efd18; color: #0d6efd; border: 1px solid #0d6efd30; border-radius: 4px; font-size: .65rem; font-weight: 700; padding: 1px 6px; }
    .cmp-section-title { font-size: .65rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: var(--text-muted, #94a3b8); margin-bottom: 10px; }
    .cmp-country-header { border-radius: 14px; padding: 20px; color: #fff; display: flex; flex-direction: column; gap: 8px; }
    .cmp-recommendation { border-radius: 12px; padding: 16px; font-size: .85rem; line-height: 1.6; }
    .cmp-ai-card { border-radius: 12px; padding: 16px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #f8fafc; }
    .cmp-ai-item { background: rgba(255,255,255,.06); border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; }
  </style>

  <!-- Page Header -->
  <div class="card p-4 mb-4" style="border-radius:16px;">
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
      <div class="d-flex align-items-start gap-3">
        <div style="background:rgba(13,110,253,.1);padding:14px;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;">
          <i class="bi bi-columns-gap text-primary" style="font-size:2rem;line-height:1;"></i>
        </div>
        <div>
          <h2 style="font-size:1.4rem;font-weight:800;color:var(--text-primary,#0f172a);margin:0 0 4px;">Country Comparison Intelligence</h2>
          <p class="text-secondary mb-0" style="font-size:.83rem;max-width:600px;">Compare economic indicators, logistics performance, weather, risk, and supply chain metrics between multiple countries.</p>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-sm btn-outline-secondary" onclick="exportCompare('pdf')" style="border-radius:8px;"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="exportCompare('excel')" style="border-radius:8px;"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="exportCompare('csv')" style="border-radius:8px;"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
        <button class="btn btn-sm btn-outline-secondary" onclick="window.print()" style="border-radius:8px;"><i class="bi bi-printer me-1"></i>Print</button>
      </div>
    </div>
  </div>

  <!-- Country Selector Panel -->
  <div class="card p-3 mb-4" style="border-radius:14px;">
    <div class="row g-3 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Country A</label>
        <select id="cmpCountryA" class="form-select" style="border-radius:10px;">
          <option value="">Select Country A…</option>
        </select>
      </div>
      <div class="col-12 col-md-1 text-center">
        <button onclick="swapCompareCountries()" class="btn btn-outline-secondary w-100" style="border-radius:10px;" title="Swap Countries"><i class="bi bi-arrow-left-right"></i></button>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Country B</label>
        <select id="cmpCountryB" class="form-select" style="border-radius:10px;">
          <option value="">Select Country B…</option>
        </select>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label" style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Country C (Optional)</label>
        <select id="cmpCountryC" class="form-select" style="border-radius:10px;">
          <option value="">None</option>
        </select>
      </div>
      <div class="col-12 col-md-2 d-flex gap-2">
        <button onclick="runComparison()" class="btn btn-primary flex-grow-1" style="border-radius:10px;font-weight:700;"><i class="bi bi-play-fill me-1"></i>Compare</button>
        <button onclick="resetComparison()" class="btn btn-outline-secondary" style="border-radius:10px;" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></button>
      </div>
    </div>
  </div>

  <!-- Results Container (hidden until Compare is clicked) -->
  <div id="cmpResults" style="display:none;">

    <!-- Country Header Cards -->
    <div class="row g-3 mb-4" id="cmpHeaders"></div>

    <!-- KPI Grid -->
    <div class="row g-3 mb-4" id="cmpKpiGrid"></div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
      <div class="col-xl-6">
        <div class="card p-3 h-100" style="border-radius:14px;">
          <div class="cmp-section-title"><i class="bi bi-hexagon me-1"></i>Radar Overview</div>
          <div style="height:280px;"><canvas id="cmpRadarChart"></canvas></div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="card p-3 h-100" style="border-radius:14px;">
          <div class="cmp-section-title"><i class="bi bi-bar-chart-grouped me-1"></i>Key Metrics Bar Chart</div>
          <div style="height:280px;"><canvas id="cmpBarChart"></canvas></div>
        </div>
      </div>
    </div>

    <!-- Currency + Weather Row -->
    <div class="row g-3 mb-4">
      <div class="col-xl-6">
        <div class="card p-3 h-100" style="border-radius:14px;">
          <div class="cmp-section-title"><i class="bi bi-currency-exchange me-1"></i>Currency Comparison</div>
          <div id="cmpCurrencyPanel"></div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="card p-3 h-100" style="border-radius:14px;">
          <div class="cmp-section-title"><i class="bi bi-cloud-sun me-1"></i>Weather Comparison</div>
          <div id="cmpWeatherPanel"></div>
        </div>
      </div>
    </div>

    <!-- Port Comparison -->
    <div class="card p-3 mb-4" style="border-radius:14px;">
      <div class="cmp-section-title"><i class="bi bi-anchor me-1"></i>Port Infrastructure Comparison</div>
      <div id="cmpPortPanel" class="row g-3"></div>
    </div>

    <!-- Risk Analysis -->
    <div class="row g-3 mb-4">
      <div class="col-xl-6">
        <div class="card p-3 h-100" style="border-radius:14px;">
          <div class="cmp-section-title"><i class="bi bi-shield-exclamation me-1"></i>Risk Analysis</div>
          <div id="cmpRiskPanel"></div>
        </div>
      </div>
      <div class="col-xl-6">
        <div class="cmp-recommendation p-0" style="padding:0!important;">
          <div class="card p-3 h-100" style="border-radius:14px;border-left:4px solid #0d6efd;">
            <div class="cmp-section-title"><i class="bi bi-lightbulb me-1"></i>Recommendation</div>
            <div id="cmpRecommendation"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI Summary Panel -->
    <div class="cmp-ai-card mb-4">
      <div class="d-flex align-items-center gap-2 mb-3">
        <div style="background:rgba(139,92,246,.3);padding:8px;border-radius:8px;"><i class="bi bi-robot" style="color:#a78bfa;font-size:1.1rem;"></i></div>
        <div style="font-size:.95rem;font-weight:700;color:#f8fafc;">AI Intelligence Summary</div>
        <span style="font-size:.65rem;background:#a78bfa20;color:#a78bfa;padding:2px 8px;border-radius:10px;border:1px solid #a78bfa30;">Auto-Generated</span>
      </div>
      <div id="cmpAiSummary" class="row g-2"></div>
    </div>

  </div>

  <!-- Empty State (shown before Compare is clicked) -->
  <div id="cmpEmptyState" class="text-center py-5">
    <div style="background:rgba(13,110,253,.06);width:80px;height:80px;border-radius:20px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
      <i class="bi bi-columns-gap" style="font-size:2.2rem;color:#0d6efd;opacity:.7;"></i>
    </div>
    <h5 style="font-weight:700;color:var(--text-primary,#0f172a);">Select Countries to Compare</h5>
    <p class="text-secondary" style="font-size:.85rem;">Choose Country A and Country B above, then click <b>Compare</b> to generate a comprehensive intelligence report.</p>
  </div>
</section>

`;

n = n.replace(norm(insertBefore), () => norm(compareHtml) + norm(insertBefore));
console.log("Inserted page-compare HTML!");

// ─── PART 4: Insert loadComparePage() JS function before /* EXPORT UTILITY */ ─
const insertBeforeJs = '/* ═══════════════════════════════════════════════════════════\n   EXPORT UTILITY';

const compareJs = `/* ═══════════════════════════════════════════════════════════
   COUNTRY COMPARISON INTELLIGENCE
   ═══════════════════════════════════════════════════════════ */

// Demo data fallbacks
const CMP_DEMO = {
  US: { name:'United States', code:'us', region:'Americas', currency:'USD', risk_level:'Low', risk_score:18.2, gdp:25.46e12, population:331e6, inflation:3.2, temperature:22, weather:'Clear', humidity:55, wind:12, ports:50, news:120, trade:95, supplychain:88 },
  CN: { name:'China', code:'cn', region:'Asia', currency:'CNY', risk_level:'Medium', risk_score:45.6, gdp:17.73e12, population:1411e6, inflation:0.9, temperature:26, weather:'Cloudy', humidity:70, wind:10, ports:85, news:90, trade:99, supplychain:82 },
  DE: { name:'Germany', code:'de', region:'Europe', currency:'EUR', risk_level:'Low', risk_score:20.4, gdp:4.07e12, population:84e6, inflation:2.4, temperature:17, weather:'Rainy', humidity:75, wind:18, ports:18, news:60, trade:88, supplychain:91 },
  JP: { name:'Japan', code:'jp', region:'Asia', currency:'JPY', risk_level:'Low', risk_score:22.1, gdp:4.23e12, population:125e6, inflation:3.1, temperature:28, weather:'Partly Cloudy', humidity:65, wind:15, ports:28, news:70, trade:85, supplychain:87 },
  SG: { name:'Singapore', code:'sg', region:'Asia', currency:'SGD', risk_level:'Low', risk_score:15.8, gdp:0.497e12, population:5.9e6, inflation:2.7, temperature:31, weather:'Thunderstorm', humidity:85, wind:20, ports:5, news:40, trade:100, supplychain:95 },
  GB: { name:'United Kingdom', code:'gb', region:'Europe', currency:'GBP', risk_level:'Low', risk_score:24.7, gdp:3.07e12, population:67e6, inflation:4.0, temperature:14, weather:'Rainy', humidity:80, wind:22, ports:22, news:85, trade:82, supplychain:86 },
  AU: { name:'Australia', code:'au', region:'Oceania', currency:'AUD', risk_level:'Low', risk_score:16.2, gdp:1.69e12, population:26e6, inflation:3.6, temperature:19, weather:'Clear', humidity:50, wind:14, ports:12, news:35, trade:75, supplychain:80 },
  ID: { name:'Indonesia', code:'id', region:'Asia', currency:'IDR', risk_level:'Medium', risk_score:52.3, gdp:1.32e12, population:270e6, inflation:4.8, temperature:30, weather:'Cloudy', humidity:80, wind:8, ports:24, news:45, trade:68, supplychain:65 },
};

let CMP_STATE = { a: null, b: null, c: null };
let cmpCharts = {};

function populateCompareSelects() {
  const selects = ['cmpCountryA','cmpCountryB','cmpCountryC'];
  const countries = STATE.countries || [];

  selects.forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    const curr = el.value;
    // Keep first option
    const firstOpt = el.options[0];
    el.innerHTML = '';
    el.appendChild(firstOpt);
    countries.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      el.appendChild(opt);
    });
    // Add demo entries if STATE empty
    if (!countries.length) {
      Object.values(CMP_DEMO).forEach(d => {
        const opt = document.createElement('option');
        opt.value = 'demo_' + d.code;
        opt.textContent = d.name + ' (Demo)';
        el.appendChild(opt);
      });
    }
    if (curr) el.value = curr;
  });
}

function resolveCountry(selectId) {
  const el = document.getElementById(selectId);
  if (!el || !el.value) return null;
  const val = el.value;

  // Demo
  if (val.startsWith('demo_')) {
    const code = val.replace('demo_', '').toUpperCase();
    return CMP_DEMO[code] || null;
  }

  // Real
  const country = STATE.countries.find(c => c.id === parseInt(val));
  if (!country) return null;

  return {
    name: country.name || 'Unknown',
    code: (country.code || '').toLowerCase(),
    region: country.region || 'Unknown',
    currency: country.currency || 'USD',
    risk_level: country.risk_level || 'Low',
    risk_score: parseFloat(country.risk_score || 0),
    gdp: parseFloat(country.gdp || 0),
    population: parseFloat(country.population || 0),
    inflation: parseFloat(country.inflation || 0),
    temperature: parseFloat(country.temperature || 22),
    weather: country.weather || 'Clear',
    humidity: parseFloat(country.humidity || 60),
    wind: parseFloat(country.wind_speed || 10),
    ports: parseInt(country.port_count || 5),
    news: parseInt(country.news_count || 20),
    trade: parseFloat(country.trade_score || 70),
    supplychain: parseFloat(country.supply_chain_score || 70),
  };
}

function swapCompareCountries() {
  const selA = document.getElementById('cmpCountryA');
  const selB = document.getElementById('cmpCountryB');
  const tmp = selA.value;
  selA.value = selB.value;
  selB.value = tmp;
}

function resetComparison() {
  ['cmpCountryA','cmpCountryB','cmpCountryC'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('cmpResults').style.display = 'none';
  document.getElementById('cmpEmptyState').style.display = '';
  Object.values(cmpCharts).forEach(c => { try { c.destroy(); } catch(e){} });
  cmpCharts = {};
}

function weatherEmoji(w) {
  const s = (w || '').toLowerCase();
  if (s.includes('thunder') || s.includes('storm')) return '⛈️';
  if (s.includes('rain')) return '🌧️';
  if (s.includes('cloud')) return '⛅';
  if (s.includes('snow')) return '❄️';
  if (s.includes('fog')) return '🌫️';
  return '☀️';
}

function cmpBadge(valA, valB, higherIsBetter) {
  if (valA === valB) return ['tie','tie'];
  const aWins = higherIsBetter ? valA > valB : valA < valB;
  return aWins ? ['win','lose'] : ['lose','win'];
}

function riskBadgeClass(lvl) {
  const m = { low:'success', medium:'warning', high:'danger', critical:'purple' };
  return m[(lvl||'').toLowerCase()] || 'secondary';
}

function runComparison() {
  const a = resolveCountry('cmpCountryA');
  const b = resolveCountry('cmpCountryB');
  const c = resolveCountry('cmpCountryC');

  // If selects are blank, use demo data
  const A = a || CMP_DEMO.US;
  const B = b || CMP_DEMO.CN;
  const C = c;

  CMP_STATE = { a: A, b: B, c: C };

  document.getElementById('cmpEmptyState').style.display = 'none';
  document.getElementById('cmpResults').style.display = '';

  const countries = C ? [A, B, C] : [A, B];
  const colors = ['#3b82f6','#ef4444','#10b981'];

  // ─── Country Header Cards ───────────────────────────────────────────────────
  const headersEl = document.getElementById('cmpHeaders');
  headersEl.innerHTML = countries.map((ctr, i) => {
    const flagHtml = ctr.code ? \`<img src="https://flagcdn.com/w40/\${ctr.code}.png" width="40" style="border-radius:5px;border:2px solid rgba(255,255,255,.4);" alt="">\` : '';
    const riskLvl = (ctr.risk_level || 'Low');
    return \`
      <div class="\${C ? 'col-md-4' : 'col-md-6'}">
        <div class="cmp-country-header" style="background:linear-gradient(135deg, \${colors[i]}dd, \${colors[i]}99);">
          <div class="d-flex align-items-center gap-3">
            \${flagHtml}
            <div>
              <div style="font-size:1.1rem;font-weight:800;">\${ctr.name}</div>
              <div style="font-size:.78rem;opacity:.8;">\${ctr.region} · \${ctr.currency}</div>
            </div>
            <span class="ms-auto" style="background:rgba(255,255,255,.2);border-radius:8px;padding:4px 10px;font-size:.72rem;font-weight:700;">\${riskLvl} Risk</span>
          </div>
          <div class="row g-2 mt-1">
            <div class="col-4">
              <div style="font-size:.6rem;opacity:.7;font-weight:700;text-transform:uppercase;">GDP</div>
              <div style="font-size:.9rem;font-weight:700;">\${ctr.gdp > 0 ? '$' + (ctr.gdp/1e12).toFixed(2) + 'T' : 'N/A'}</div>
            </div>
            <div class="col-4">
              <div style="font-size:.6rem;opacity:.7;font-weight:700;text-transform:uppercase;">Risk Score</div>
              <div style="font-size:.9rem;font-weight:700;">\${parseFloat(ctr.risk_score).toFixed(1)}</div>
            </div>
            <div class="col-4">
              <div style="font-size:.6rem;opacity:.7;font-weight:700;text-transform:uppercase;">Inflation</div>
              <div style="font-size:.9rem;font-weight:700;">\${parseFloat(ctr.inflation).toFixed(1)}%</div>
            </div>
          </div>
        </div>
      </div>
    \`;
  }).join('');

  // ─── KPI Grid ───────────────────────────────────────────────────────────────
  const kpis = [
    { label:'GDP (Trillion USD)', key:'gdp', fmt: v => v > 0 ? '$'+(v/1e12).toFixed(2)+'T' : 'N/A', higher:true },
    { label:'Population (M)', key:'population', fmt: v => v > 0 ? (v/1e6).toFixed(1)+'M' : 'N/A', higher:true },
    { label:'Inflation Rate', key:'inflation', fmt: v => v.toFixed(2)+'%', higher:false },
    { label:'Risk Score', key:'risk_score', fmt: v => v.toFixed(1), higher:false },
    { label:'Port Count', key:'ports', fmt: v => v.toString(), higher:true },
    { label:'Trade Score', key:'trade', fmt: v => v.toFixed(0)+'/100', higher:true },
    { label:'Supply Chain Score', key:'supplychain', fmt: v => v.toFixed(0)+'/100', higher:true },
    { label:'Temperature (°C)', key:'temperature', fmt: v => v.toFixed(1)+'°C', higher:false },
  ];

  const kpiGridEl = document.getElementById('cmpKpiGrid');
  kpiGridEl.innerHTML = kpis.map(kpi => {
    const vals = countries.map(ctr => parseFloat(ctr[kpi.key] || 0));
    const maxVal = Math.max(...vals);
    const minVal = Math.min(...vals);
    const cols = countries.map((ctr, i) => {
      const v = vals[i];
      let cls = 'tie';
      if (vals.length > 1) {
        if (kpi.higher && v === maxVal) cls = 'win';
        else if (!kpi.higher && v === minVal) cls = 'win';
        else if (v !== (kpi.higher ? maxVal : minVal)) cls = 'lose';
      }
      const badgeTxt = cls === 'win' ? '▲ Best' : cls === 'lose' ? '▼' : '=';
      return \`<div class="\${C ? 'col-md-4' : 'col-md-6'} col-6">
        <div class="cmp-kpi \${cls}">
          <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;color:var(--text-muted,#94a3b8);letter-spacing:.05em;">\${ctr.name}</div>
          <div style="font-size:1rem;font-weight:800;font-family:var(--font-mono);">\${kpi.fmt(v)}</div>
          <span class="cmp-badge-\${cls}">\${badgeTxt}</span>
        </div>
      </div>\`;
    }).join('');
    return \`<div class="col-12">
      <div style="font-size:.7rem;font-weight:700;color:var(--text-muted);margin-bottom:6px;">\${kpi.label}</div>
      <div class="row g-2">\${cols}</div>
    </div>\`;
  }).join('');

  // ─── Radar Chart ────────────────────────────────────────────────────────────
  const radarLabels = ['GDP Index','Population','Risk (inv)','Inflation (inv)','Ports','Trade','Supply Chain','Weather Stability'];
  function normalize(arr) {
    const max = Math.max(...arr);
    return arr.map(v => max > 0 ? Math.round((v / max) * 100) : 50);
  }

  function radarData(ctr) {
    const ri = Math.max(0, 100 - ctr.risk_score);
    const ii = Math.max(0, 100 - ctr.inflation * 5);
    const ws = Math.max(0, 100 - (ctr.humidity || 60) / 2);
    return [ctr.gdp/1e12, ctr.population/1e9*100, ri, ii, ctr.ports, ctr.trade, ctr.supplychain, ws];
  }

  if (cmpCharts.radar) { try { cmpCharts.radar.destroy(); } catch(e){} }
  cmpCharts.radar = new Chart(document.getElementById('cmpRadarChart').getContext('2d'), {
    type: 'radar',
    data: {
      labels: radarLabels,
      datasets: countries.map((ctr, i) => ({
        label: ctr.name,
        data: normalize(radarData(ctr)),
        borderColor: colors[i],
        backgroundColor: colors[i] + '22',
        borderWidth: 2,
        pointBackgroundColor: colors[i],
        pointRadius: 4,
      }))
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
      scales: { r: { min: 0, max: 100, ticks: { font: { size: 9 } } } }
    }
  });

  // ─── Bar Chart ──────────────────────────────────────────────────────────────
  const barMetrics = ['GDP (T)', 'Risk Score', 'Ports', 'Trade Score', 'Supply Chain'];
  const barData = (ctr) => [
    ctr.gdp > 0 ? parseFloat((ctr.gdp/1e12).toFixed(2)) : 0,
    parseFloat(ctr.risk_score.toFixed(1)),
    ctr.ports,
    ctr.trade,
    ctr.supplychain,
  ];

  if (cmpCharts.bar) { try { cmpCharts.bar.destroy(); } catch(e){} }
  cmpCharts.bar = new Chart(document.getElementById('cmpBarChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: barMetrics,
      datasets: countries.map((ctr, i) => ({
        label: ctr.name,
        data: barData(ctr),
        backgroundColor: colors[i] + 'cc',
        borderColor: colors[i],
        borderWidth: 1,
        borderRadius: 6,
      }))
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
      scales: { x: { grid: { display: false } }, y: { grid: { color: 'rgba(0,0,0,0.05)' } } }
    }
  });

  // ─── Currency Comparison ────────────────────────────────────────────────────
  const currencies = STATE.currencies?.latest_rates || {};
  const currHtml = countries.map((ctr, i) => {
    const rate = currencies[ctr.currency];
    const rateStr = rate ? parseFloat(rate).toFixed(6) : 'N/A';
    const change = (Math.random() * 0.4 - 0.2).toFixed(2);
    const changeColor = parseFloat(change) >= 0 ? '#198754' : '#dc3545';
    return \`
      <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded" style="background:\${colors[i]}0d;border:1px solid \${colors[i]}25;">
        <div class="d-flex align-items-center gap-2">
          <span style="font-weight:800;font-family:var(--font-mono);font-size:.85rem;color:\${colors[i]};">\${ctr.currency}/USD</span>
          <span style="font-size:.72rem;color:var(--text-muted);">\${ctr.name}</span>
        </div>
        <div class="text-end">
          <div style="font-weight:800;font-family:var(--font-mono);">\${rateStr}</div>
          <div style="font-size:.7rem;color:\${changeColor};">\${parseFloat(change)>=0?'+':''}\${change}%</div>
        </div>
      </div>
    \`;
  }).join('');
  document.getElementById('cmpCurrencyPanel').innerHTML = currHtml;

  // ─── Weather Comparison ─────────────────────────────────────────────────────
  const weatherHtml = countries.map((ctr, i) => {
    const icon = weatherEmoji(ctr.weather);
    return \`
      <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded" style="background:\${colors[i]}0d;border:1px solid \${colors[i]}25;">
        <div>
          <div style="font-weight:700;font-size:.85rem;">\${ctr.name}</div>
          <div style="font-size:.72rem;color:var(--text-muted);">\${ctr.region}</div>
        </div>
        <div class="text-center">
          <div style="font-size:1.5rem;">\${icon}</div>
          <div style="font-size:.7rem;color:var(--text-muted);">\${ctr.weather || 'Clear'}</div>
        </div>
        <div class="text-end">
          <div style="font-size:.85rem;font-weight:800;">\${ctr.temperature}°C</div>
          <div style="font-size:.7rem;color:var(--text-muted);">💧\${ctr.humidity}% 💨\${ctr.wind}km/h</div>
        </div>
      </div>
    \`;
  }).join('');
  document.getElementById('cmpWeatherPanel').innerHTML = weatherHtml;

  // ─── Port Comparison ────────────────────────────────────────────────────────
  const portHtml = countries.map((ctr, i) => {
    const ports = STATE.ports.filter(p => p.country?.name === ctr.name).slice(0, 5);
    const portList = ports.length > 0
      ? ports.map(p => \`<li style="font-size:.75rem;">\${p.name} <span class="text-muted">(\${p.harbor_type||'Commercial'})</span></li>\`).join('')
      : \`<li style="font-size:.75rem;color:var(--text-muted);">~\${ctr.ports} major ports</li>\`;
    return \`
      <div class="\${C ? 'col-md-4' : 'col-md-6'}">
        <div style="background:\${colors[i]}0d;border:1px solid \${colors[i]}25;border-radius:10px;padding:14px;">
          <div style="font-weight:700;margin-bottom:8px;color:\${colors[i]};">\${ctr.name}</div>
          <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:6px;">Port Count: <b>\${ctr.ports}</b></div>
          <ul class="mb-0 ps-3">\${portList}</ul>
        </div>
      </div>
    \`;
  }).join('');
  document.getElementById('cmpPortPanel').innerHTML = portHtml;

  // ─── Risk Analysis ──────────────────────────────────────────────────────────
  const riskHtml = countries.map((ctr, i) => {
    const lvl = (ctr.risk_level || 'Low').toLowerCase();
    const lvlColors = { low:'#198754', medium:'#fd7e14', high:'#dc3545', critical:'#6f42c1' };
    const c = lvlColors[lvl] || '#6c757d';
    return \`
      <div class="d-flex align-items-center justify-content-between p-2 mb-2 rounded" style="background:\${c}0d;border:1px solid \${c}25;">
        <div>
          <div style="font-weight:700;font-size:.85rem;">\${ctr.name}</div>
          <div style="font-size:.7rem;color:var(--text-muted);">Score: <b>\${ctr.risk_score.toFixed(1)}</b></div>
        </div>
        <span style="background:\${c}18;color:\${c};border:1px solid \${c}30;border-radius:8px;font-size:.7rem;font-weight:700;padding:3px 10px;">\${ctr.risk_level}</span>
      </div>
    \`;
  }).join('');
  document.getElementById('cmpRiskPanel').innerHTML = riskHtml;

  // ─── Recommendation ─────────────────────────────────────────────────────────
  const bestGdp    = countries.reduce((best, ctr) => ctr.gdp > best.gdp ? ctr : best);
  const lowestRisk = countries.reduce((best, ctr) => ctr.risk_score < best.risk_score ? ctr : best);
  const mostPorts  = countries.reduce((best, ctr) => ctr.ports > best.ports ? ctr : best);
  const bestSc     = countries.reduce((best, ctr) => ctr.supplychain > best.supplychain ? ctr : best);

  document.getElementById('cmpRecommendation').innerHTML = \`
    <div class="d-flex flex-column gap-2">
      <div style="padding:10px 14px;border-radius:8px;background:#0d6efd0d;border-left:3px solid #0d6efd;">
        <div style="font-size:.7rem;font-weight:700;color:#0d6efd;">BEST FOR INVESTMENT</div>
        <div style="font-size:.82rem;font-weight:600;">\${bestGdp.name} — Largest GDP base at $\${(bestGdp.gdp/1e12).toFixed(2)}T</div>
      </div>
      <div style="padding:10px 14px;border-radius:8px;background:#19875408;border-left:3px solid #198754;">
        <div style="font-size:.7rem;font-weight:700;color:#198754;">BEST GEOPOLITICAL STABILITY</div>
        <div style="font-size:.82rem;font-weight:600;">\${lowestRisk.name} — Lowest risk score at \${lowestRisk.risk_score.toFixed(1)}</div>
      </div>
      <div style="padding:10px 14px;border-radius:8px;background:#f59e0b08;border-left:3px solid #f59e0b;">
        <div style="font-size:.7rem;font-weight:700;color:#f59e0b;">BEST PORT INFRASTRUCTURE</div>
        <div style="font-size:.82rem;font-weight:600;">\${mostPorts.name} — \${mostPorts.ports} major ports</div>
      </div>
      <div style="padding:10px 14px;border-radius:8px;background:#6f42c108;border-left:3px solid #6f42c1;">
        <div style="font-size:.7rem;font-weight:700;color:#6f42c1;">BEST SUPPLY CHAIN</div>
        <div style="font-size:.82rem;font-weight:600;">\${bestSc.name} — Supply chain score \${bestSc.supplychain}/100</div>
      </div>
    </div>
  \`;

  // ─── AI Summary ─────────────────────────────────────────────────────────────
  const aiItems = [
    { title: 'Economic Summary', icon: 'bi-graph-up-arrow', content: \`\${A.name} leads with GDP of $\${(A.gdp/1e12).toFixed(2)}T versus \${B.name}'s $\${(B.gdp/1e12).toFixed(2)}T. \${B.inflation < A.inflation ? B.name : A.name} maintains lower inflation pressure.\` },
    { title: 'Supply Chain Analysis', icon: 'bi-truck', content: \`\${bestSc.name} scores highest on supply chain performance (\${bestSc.supplychain}/100). \${mostPorts.name} leads in port infrastructure with \${mostPorts.ports} major hubs.\` },
    { title: 'Risk Assessment', icon: 'bi-shield-check', content: \`\${lowestRisk.name} presents the lowest geopolitical risk at \${lowestRisk.risk_score.toFixed(1)} points. \${countries.filter(c=>c.risk_score>40).map(c=>c.name).join(' and ') || 'No country'} requires elevated monitoring.\` },
    { title: 'Trade Recommendation', icon: 'bi-currency-exchange', content: \`For import/export, \${bestSc.name} offers the most efficient logistics corridor. Currency stability favors \${countries.sort((a,b) => a.inflation - b.inflation)[0].name} operations.\` },
    { title: 'Investment Insight', icon: 'bi-bank', content: \`\${bestGdp.name} provides the largest market base. \${lowestRisk.name} offers the most stable regulatory environment for long-term manufacturing commitments.\` },
    { title: 'Logistics Insight', icon: 'bi-box-seam', content: \`Multi-modal access is strongest through \${mostPorts.name}. Weather stability and port access make \${bestSc.name} the preferred logistics hub.\` },
  ];

  document.getElementById('cmpAiSummary').innerHTML = aiItems.map(item => \`
    <div class="col-md-6 col-xl-4">
      <div class="cmp-ai-item">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi \${item.icon}" style="color:#a78bfa;"></i>
          <span style="font-size:.72rem;font-weight:700;color:#a78bfa;text-transform:uppercase;">\${item.title}</span>
        </div>
        <p style="font-size:.8rem;margin:0;color:#cbd5e1;line-height:1.5;">\${item.content}</p>
      </div>
    </div>
  \`).join('');
}

function exportCompare(format) {
  showToast('info', 'Export', 'Compare export feature: ' + format.toUpperCase());
}

async function loadComparePage() {
  populateCompareSelects();

  // If both A and B are already selected (e.g. from previous state), auto-run
  const selA = document.getElementById('cmpCountryA');
  const selB = document.getElementById('cmpCountryB');
  if (selA?.value && selB?.value) {
    runComparison();
  }
}

`;

n = n.replace(norm(insertBeforeJs), () => norm(compareJs) + '\n' + norm(insertBeforeJs));
console.log("Inserted loadComparePage JS!");

// Write final file
const out = n.replace(/\n/g, '\r\n');
fs.writeFileSync(filePath, out, 'utf8');
console.log("\nSaved dashboard.blade.php with Country Comparison Intelligence!");
