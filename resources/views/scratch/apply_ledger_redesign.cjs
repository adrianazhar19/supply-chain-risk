const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// Normalize line endings to LF first for reliable search
let normContent = content.replace(/\r\n/g, '\n');

// 1. Replace the page-countries section block
const startStr = '<!-- ─── PAGE: COUNTRIES ──────────────────────────── -->';
const endStr = '</section>';

const startIdx = normContent.indexOf(startStr);
if (startIdx === -1) {
    console.error("Error: Could not find page-countries start in dashboard.blade.php!");
    process.exit(1);
}

const endIdx = normContent.indexOf(endStr, startIdx);
if (endIdx === -1) {
    console.error("Error: Could not find page-countries end in dashboard.blade.php!");
    process.exit(1);
}

const targetHtmlBlock = normContent.substring(startIdx, endIdx + endStr.length);

const replacementHtmlBlock = `<!-- ─── PAGE: COUNTRIES ──────────────────────────── -->
<section class="content-page" id="page-countries">
  <style>
    .ledger-header-btn {
      padding: 8px 16px;
      font-size: 0.8rem;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
    .ledger-kpi-card {
      background: var(--card-bg);
      border-radius: 12px;
      padding: 16px;
      border: 1px solid var(--border-color);
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .ledger-kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05), 0 4px 6px -2px rgba(0,0,0,0.02);
    }
    #countryTable_wrapper .dt-buttons {
      display: none !important;
    }
    #countryTable {
      border-collapse: separate !important;
      border-spacing: 0 !important;
      width: 100% !important;
    }
    #countryTable thead th {
      background: #F8FAFC !important;
      color: #475569 !important;
      font-weight: 700 !important;
      font-size: 0.72rem !important;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      padding: 14px 16px !important;
      border-bottom: 2px solid #E2E8F0 !important;
      position: sticky;
      top: 0;
      z-index: 10;
    }
    html[data-theme="dark"] #countryTable thead th {
      background: #1e293b !important;
      color: #94a3b8 !important;
      border-bottom: 2px solid #334155 !important;
    }
    #countryTable tbody tr {
      transition: background-color 0.15s ease;
    }
    #countryTable tbody tr:nth-of-type(even) {
      background-color: rgba(248, 250, 252, 0.4);
    }
    html[data-theme="dark"] #countryTable tbody tr:nth-of-type(even) {
      background-color: rgba(30, 41, 59, 0.15);
    }
    #countryTable tbody tr:hover {
      background-color: rgba(59, 130, 246, 0.06) !important;
    }
    .col-align-left { text-align: left !important; }
    .col-align-right { text-align: right !important; }
    .col-align-center { text-align: center !important; }
    
    .risk-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 12px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .risk-badge.low { background: #10b98115; color: #10b981; border: 1px solid #10b98130; }
    .risk-badge.medium { background: #f9731615; color: #f97316; border: 1px solid #f9731630; }
    .risk-badge.high { background: #ef444415; color: #ef4444; border: 1px solid #ef444430; }
    .risk-badge.critical { background: #8b5cf615; color: #8b5cf6; border: 1px solid #8b5cf630; }
    
    .btn-icon {
      width: 28px;
      height: 28px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      background: transparent;
      border: none;
      transition: background-color 0.15s ease;
    }
    .btn-icon:hover {
      background-color: rgba(148, 163, 184, 0.12);
    }
  </style>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-3">
    <div>
      <h1 style="font-size:1.6rem;font-weight:700;margin:0;color:var(--text-primary);">🌍 Country Intelligence Ledger</h1>
      <span style="font-size:.85rem;color:var(--text-secondary);">Global Economic Intelligence Database</span>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-sm btn-outline-brand ledger-header-btn" onclick="loadAllData()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button>
      <button class="btn btn-sm btn-brand ledger-header-btn" onclick="exportLedgerTable('csv')"><i class="bi bi-filetype-csv me-1"></i>CSV</button>
      <button class="btn btn-sm btn-brand ledger-header-btn" onclick="exportLedgerTable('excel')"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
      <button class="btn btn-sm btn-brand ledger-header-btn" onclick="exportLedgerTable('pdf')"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
      <button class="btn btn-sm btn-brand ledger-header-btn" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
    </div>
  </div>

  <!-- Filter Bar -->
  <div class="card p-3 mb-4 shadow-sm" style="border-radius:12px; background:var(--card-bg); border-color:var(--border-color);">
    <div class="d-flex align-items-center flex-wrap gap-2">
      <div class="position-relative" style="width: 280px; min-width: 200px;">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input type="text" id="ledgerSearch" class="form-control form-control-sm" placeholder="Search Country..." style="padding-left: 36px; border-radius: 8px; height: 38px; font-size: 0.85rem; border-color:var(--border-color);">
      </div>
      <select id="ledgerRegionFilter" class="form-select form-select-sm" style="width: 140px; border-radius: 8px; height: 38px; font-size: 0.85rem; border-color:var(--border-color);">
        <option value="">All Regions</option>
        <option value="Europe">Europe</option>
        <option value="Americas">Americas</option>
        <option value="Asia">Asia</option>
        <option value="Africa">Africa</option>
        <option value="Oceania">Oceania</option>
      </select>
      <select id="ledgerRiskFilter" class="form-select form-select-sm" style="width: 130px; border-radius: 8px; height: 38px; font-size: 0.85rem; border-color:var(--border-color);">
        <option value="">All Risks</option>
        <option value="Low">Low</option>
        <option value="Medium">Medium</option>
        <option value="High">High</option>
        <option value="Critical">Critical</option>
      </select>
      <select id="ledgerCurrencyFilter" class="form-select form-select-sm" style="width: 140px; border-radius: 8px; height: 38px; font-size: 0.85rem; border-color:var(--border-color);">
        <option value="">All Currencies</option>
      </select>
      <select id="ledgerStatusFilter" class="form-select form-select-sm" style="width: 130px; border-radius: 8px; height: 38px; font-size: 0.85rem; border-color:var(--border-color);">
        <option value="">All Statuses</option>
        <option value="Low">Low Risk</option>
        <option value="Medium">Medium Risk</option>
        <option value="High">High Risk</option>
        <option value="Critical">Critical Risk</option>
      </select>
      <div class="ms-md-auto d-flex gap-2">
        <button id="ledgerResetFiltersBtn" class="btn btn-sm btn-outline-brand" style="border-radius: 8px; height: 38px; font-size: 0.85rem; padding: 0 16px;">Reset</button>
        <button id="ledgerApplyFiltersBtn" class="btn btn-sm btn-brand" style="border-radius: 8px; height: 38px; font-size: 0.85rem; padding: 0 16px;">Apply</button>
      </div>
    </div>
  </div>

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3">
        <div class="stat-icon blue" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-globe"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Countries Monitored</div>
          <div style="font-size:1.35rem;font-weight:700;color:var(--text-primary);" id="kpi-ledger-countries">250</div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3">
        <div class="stat-icon red" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">High / Critical Risk</div>
          <div style="font-size:1.35rem;font-weight:700;color:#ef4444;" id="kpi-ledger-high-risk">0</div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3">
        <div class="stat-icon amber" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-shield-slash"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Medium Risk</div>
          <div style="font-size:1.35rem;font-weight:700;color:#f97316;" id="kpi-ledger-med-risk">0</div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3">
        <div class="stat-icon green" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-shield-check"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Low Risk</div>
          <div style="font-size:1.35rem;font-weight:700;color:#10b981;" id="kpi-ledger-low-risk">0</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-xl-4 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3" style="background:rgba(139,92,246,0.03);">
        <div class="stat-icon purple" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-currency-dollar"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Average GDP</div>
          <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);" id="kpi-ledger-avg-gdp">$0.0B</div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-md-6">
      <div class="ledger-kpi-card d-flex align-items-center gap-3" style="background:rgba(6,182,212,0.03);">
        <div class="stat-icon cyan" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-people"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Average Population</div>
          <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);" id="kpi-ledger-avg-pop">0.0M</div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-md-12">
      <div class="ledger-kpi-card d-flex align-items-center gap-3" style="background:rgba(236,72,153,0.03);">
        <div class="stat-icon pink" style="width:42px;height:42px;font-size:1.2rem;border-radius:10px;"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
          <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-muted);">Average Inflation</div>
          <div style="font-size:1.25rem;font-weight:700;color:var(--text-primary);" id="kpi-ledger-avg-inflation">0.00%</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Table Card -->
  <div class="card p-0 shadow-sm" style="border-radius:12px; border:none; background:transparent;">
    <div id="ledger-table-container">
      <table id="countryTable" class="table w-100">
        <thead>
          <tr>
            <th class="col-align-center" style="width: 50px;">Flag</th>
            <th class="col-align-left">Country</th>
            <th class="col-align-center" style="width: 80px;">ISO</th>
            <th class="col-align-left">Region</th>
            <th class="col-align-left">Currency</th>
            <th class="col-align-right">GDP</th>
            <th class="col-align-right">Population</th>
            <th class="col-align-right">Inflation</th>
            <th class="col-align-center">Risk Score</th>
            <th class="col-align-center">Status</th>
            <th class="col-align-center" style="width: 150px;">Action</th>
          </tr>
        </thead>
        <tbody>
          @if(isset($countries) && count($countries) > 0)
            @foreach($countries as $country)
              @php
                $latestRisk = $country->riskScores->first();
                $riskLevel = $latestRisk ? $latestRisk->risk_level : 'Low';
                $riskScore = $latestRisk ? (float) $latestRisk->total_score : null;
                $gdp = $country->economic ? $country->economic->gdp : null;
                $population = $country->economic ? $country->economic->population : null;
                $inflation = $country->economic ? $country->economic->inflation : null;
              @endphp
              <tr onclick="viewCountry({{ $country->id }})" style="cursor:pointer;">
                <td class="col-align-center">
                  <img src="https://flagcdn.com/w40/{{ strtolower($country->code) }}.png" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt="">
                </td>
                <td class="col-align-left"><strong>{{ $country->name }}</strong></td>
                <td class="col-align-center"><code style="font-size:.75rem;">{{ $country->code }}</code></td>
                <td class="col-align-left" style="color:var(--text-secondary);">{{ $country->region ?? '-' }}</td>
                <td class="col-align-left">{{ $country->currency ?? '-' }}</td>
                <td class="col-align-right">{{ $gdp ? '$'.number_format($gdp/1e9, 1).'B' : '-' }}</td>
                <td class="col-align-right">{{ $population ? number_format($population/1e6, 1).'M' : '-' }}</td>
                <td class="col-align-right {{ $inflation > 5 ? 'text-danger' : '' }}">{{ $inflation ? number_format($inflation, 2).'%' : '-' }}</td>
                <td class="col-align-center"><span style="font-family:var(--font-mono);font-weight:700;">{{ $riskScore ? number_format($riskScore, 1) : '-' }}</span></td>
                <td class="col-align-center">
                  <span class="risk-badge {{ strtolower($riskLevel) }}">{{ $riskLevel }}</span>
                </td>
                <td class="col-align-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn-icon" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="View Profile" data-bs-toggle="tooltip">
                      <i class="bi bi-person-badge text-primary" style="font-size:1.05rem;"></i>
                    </button>
                    <button class="btn-icon" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="View Location Map" data-bs-toggle="tooltip">
                      <i class="bi bi-geo-alt-fill text-success" style="font-size:1.05rem;"></i>
                    </button>
                    <button class="btn-icon" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="View Weather Intelligence" data-bs-toggle="tooltip">
                      <i class="bi bi-cloud-sun-fill text-warning" style="font-size:1.05rem;"></i>
                    </button>
                    <button class="btn-icon" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="View Economic Data" data-bs-toggle="tooltip">
                      <i class="bi bi-bar-chart-fill" style="color:#8b5cf6; font-size:1.05rem;"></i>
                    </button>
                  </div>
                </td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="11" class="text-center py-4 text-muted">No country data available</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>
  </div>
</section>`;

// Use safety callbacks for replacements to avoid pattern expansions
normContent = normContent.replace(targetHtmlBlock, () => replacementHtmlBlock);
console.log("Successfully replaced the ledger HTML content block!");

// 2. Replace initCountriesTable() and updateLedgerKPIs()/exportLedgerTable()
const jsStart = 'function initCountriesTable() {';
const portsComment = 'PORTS TABLE';

const jsStartIdx = normContent.indexOf(jsStart);
if (jsStartIdx === -1) {
    console.error("Error: Could not find function initCountriesTable() start!");
    process.exit(1);
}

// Find index of PORTS TABLE comment block
const portsIdx = normContent.indexOf(portsComment, jsStartIdx);
if (portsIdx === -1) {
    console.error("Error: Could not find PORTS TABLE comment after jsStartIdx!");
    process.exit(1);
}

// Find the last /* before PORTS TABLE to get the comment block start
const jsEndIdx = normContent.lastIndexOf('/*', portsIdx);
if (jsEndIdx === -1 || jsEndIdx <= jsStartIdx) {
    console.error("Error: Could not find start of PORTS TABLE comment block!");
    process.exit(1);
}

// Extract the target block up to the comment block
const targetJsBlock = normContent.substring(jsStartIdx, jsEndIdx);

const replacementJsBlock = `function updateLedgerKPIs() {
  const countries = STATE.countries || [];
  const total = countries.length;
  
  let highCritical = 0;
  let medium = 0;
  let low = 0;
  
  let totalGdp = 0;
  let gdpCount = 0;
  
  let totalPop = 0;
  let popCount = 0;
  
  let totalInfl = 0;
  let inflCount = 0;
  
  countries.forEach(c => {
    const risk = c.risk_level || 'Low';
    if (risk === 'High' || risk === 'Critical') highCritical++;
    else if (risk === 'Medium') medium++;
    else if (risk === 'Low') low++;
    
    const gdpVal = parseFloat(c.gdp);
    if (!isNaN(gdpVal) && gdpVal > 0) {
      totalGdp += gdpVal;
      gdpCount++;
    }
    
    const popVal = parseFloat(c.population);
    if (!isNaN(popVal) && popVal > 0) {
      totalPop += popVal;
      popCount++;
    }
    
    const inflVal = parseFloat(c.inflation);
    if (!isNaN(inflVal)) {
      totalInfl += inflVal;
      inflCount++;
    }
  });
  
  const avgGdp = gdpCount > 0 ? totalGdp / gdpCount : 0;
  const avgPop = popCount > 0 ? totalPop / popCount : 0;
  const avgInfl = inflCount > 0 ? totalInfl / inflCount : 0;
  
  const countriesEl = document.getElementById('kpi-ledger-countries');
  const highRiskEl = document.getElementById('kpi-ledger-high-risk');
  const medRiskEl = document.getElementById('kpi-ledger-med-risk');
  const lowRiskEl = document.getElementById('kpi-ledger-low-risk');
  const avgGdpEl = document.getElementById('kpi-ledger-avg-gdp');
  const avgPopEl = document.getElementById('kpi-ledger-avg-pop');
  const avgInflEl = document.getElementById('kpi-ledger-avg-inflation');

  if (countriesEl) countriesEl.textContent = total;
  if (highRiskEl) highRiskEl.textContent = highCritical;
  if (medRiskEl) medRiskEl.textContent = medium;
  if (lowRiskEl) lowRiskEl.textContent = low;
  
  if (avgGdpEl) avgGdpEl.textContent = avgGdp ? '$' + (avgGdp / 1e9).toFixed(2) + 'B' : '–';
  if (avgPopEl) avgPopEl.textContent = avgPop ? (avgPop / 1e6).toFixed(2) + 'M' : '–';
  if (avgInflEl) avgInflEl.textContent = avgInfl ? avgInfl.toFixed(2) + '%' : '–';
}

function exportLedgerTable(format) {
  const table = STATE.dt.countries;
  if (!table) return;
  
  if (format === 'csv') {
    table.button('.buttons-csv').trigger();
  } else if (format === 'excel') {
    table.button('.buttons-excel').trigger();
  } else if (format === 'pdf') {
    table.button('.buttons-pdf').trigger();
  } else if (format === 'print') {
    table.button('.buttons-print').trigger();
  }
}

function initCountriesTable() {
  if (countriesDtInited) return;

  const tbody = document.querySelector('#countryTable tbody');
  if (!tbody) return;

  updateLedgerKPIs();

  if (!STATE.countries || !STATE.countries.length) {
    if (tbody.children.length <= 1 && tbody.innerHTML.includes('No country data available')) {
      tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">No country data available</td></tr>';
    }
    return;
  }

  // Populate dynamic currency filter dropdown
  const curFilter = document.getElementById('ledgerCurrencyFilter');
  if (curFilter) {
    const currencies = [...new Set(STATE.countries.map(c => c.currency).filter(Boolean))].sort();
    curFilter.innerHTML = '<option value="">All Currencies</option>' + 
      currencies.map(curr => \`<option value="\${curr}">\${curr}</option>\`).join('');
  }

  try {
    tbody.innerHTML = STATE.countries.map(c => {
      if (!c) return '';
      const name = c.name || '-';
      const code = c.code || '-';
      const region = c.region || '-';
      const currency = c.currency || '-';
      const flagUrl = c.flag_url || \`https://flagcdn.com/w40/\${(code !== '-' ? code.toLowerCase() : 'un')}.png\`;
      
      const gdpVal = parseFloat(c.gdp);
      const gdpStr = isNaN(gdpVal) ? '-' : '$' + (gdpVal / 1e9).toFixed(1) + 'B';
      
      const popVal = parseFloat(c.population);
      const popStr = isNaN(popVal) ? '-' : (popVal / 1e6).toFixed(1) + 'M';
      
      const inflVal = parseFloat(c.inflation);
      const inflStr = isNaN(inflVal) ? '-' : inflVal.toFixed(2) + '%';
      const inflClass = !isNaN(inflVal) && inflVal > 5 ? 'text-danger' : '';
      
      const riskScoreVal = parseFloat(c.risk_score);
      const riskScoreStr = isNaN(riskScoreVal) ? '-' : riskScoreVal.toFixed(1);
      
      const riskLevel = c.risk_level || 'Low';
      const riskPillHtml = \`<span class="risk-badge \${riskLevel.toLowerCase()}">\${riskLevel}</span>\`;
      
      return \`<tr onclick="viewCountry(\${c.id})" style="cursor:pointer;">
        <td class="col-align-center"><img src="\${flagUrl}" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt=""></td>
        <td class="col-align-left"><strong>\${name}</strong></td>
        <td class="col-align-center"><code style="font-size:.75rem;">\${code}</code></td>
        <td class="col-align-left" style="color:var(--text-secondary);">\${region}</td>
        <td class="col-align-left">\${currency}</td>
        <td class="col-align-right">\${gdpStr}</td>
        <td class="col-align-right">\${popStr}</td>
        <td class="col-align-right \${inflClass}">\${inflStr}</td>
        <td class="col-align-center"><span style="font-family:var(--font-mono);font-weight:700;">\${riskScoreStr}</span></td>
        <td class="col-align-center">\text-center">\${riskPillHtml}</td>
        <td class="col-align-center">
          <div class="d-flex justify-content-center gap-1">
            <button class="btn-icon" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="View Profile" data-bs-toggle="tooltip">
              <i class="bi bi-person-badge text-primary" style="font-size:1.05rem;"></i>
            </button>
            <button class="btn-icon" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="View Location Map" data-bs-toggle="tooltip">
              <i class="bi bi-geo-alt-fill text-success" style="font-size:1.05rem;"></i>
            </button>
            <button class="btn-icon" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="View Weather Intelligence" data-bs-toggle="tooltip">
              <i class="bi bi-cloud-sun-fill text-warning" style="font-size:1.05rem;"></i>
            </button>
            <button class="btn-icon" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="View Economic Data" data-bs-toggle="tooltip">
              <i class="bi bi-bar-chart-fill" style="color:#8b5cf6; font-size:1.05rem;"></i>
            </button>
          </div>
        </td>
      </tr>\`;
    }).join('');
  } catch (err) {
    console.error("Error populating countries table body:", err);
  }

  try {
    if (typeof DataTable === 'undefined') {
      throw new Error("DataTable library is not defined.");
    }
    const tableEl = document.getElementById('countryTable');
    if (!tableEl) {
      throw new Error("HTML table element #countryTable not found.");
    }
    
    if (STATE.dt.countries) {
      try { STATE.dt.countries.destroy(); } catch(e){}
    }

    STATE.dt.countries = new DataTable('#countryTable', {
      responsive:true, pageLength:25, lengthMenu:[10,25,50,100],
      columnDefs:[{orderable:false,targets:[0,10]}],
      language:{ search:'', searchPlaceholder:'Filter countries...', lengthMenu:'Show _MENU_' },
      dom: 'lBfrtip',
      buttons: [
        { extend: 'csv', className: 'buttons-csv d-none' },
        { extend: 'excel', className: 'buttons-excel d-none' },
        { extend: 'pdf', className: 'buttons-pdf d-none' },
        { extend: 'print', className: 'buttons-print d-none' }
      ]
    });
    countriesDtInited = true;

    // Bind Apply filters
    document.getElementById('ledgerApplyFiltersBtn').addEventListener('click', () => {
      const searchVal = document.getElementById('ledgerSearch').value;
      const regionVal = document.getElementById('ledgerRegionFilter').value;
      const riskVal = document.getElementById('ledgerRiskFilter').value;
      const currencyVal = document.getElementById('ledgerCurrencyFilter').value;
      const statusVal = document.getElementById('ledgerStatusFilter').value;
      
      const table = STATE.dt.countries;
      if (table) {
        table.search(searchVal);
        table.column(3).search(regionVal);
        table.column(4).search(currencyVal);
        table.column(9).search(riskVal || statusVal);
        table.draw();
      }
    });

    // Bind Reset filters
    document.getElementById('ledgerResetFiltersBtn').addEventListener('click', () => {
      document.getElementById('ledgerSearch').value = '';
      document.getElementById('ledgerRegionFilter').value = '';
      document.getElementById('ledgerRiskFilter').value = '';
      document.getElementById('ledgerCurrencyFilter').value = '';
      document.getElementById('ledgerStatusFilter').value = '';
      
      const table = STATE.dt.countries;
      if (table) {
        table.search('');
        table.column(3).search('');
        table.column(4).search('');
        table.column(9).search('');
        table.draw();
      }
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

  } catch (dtError) {
    console.error("DataTable initialization failed for countryTable. Showing normal HTML table instead.", dtError);
    countriesDtInited = true;
  }
}

`;

normContent = normContent.replace(targetJsBlock, () => replacementJsBlock);
console.log("Successfully replaced function initCountriesTable()!");

// Convert line endings to CRLF and save
const finalContent = normContent.replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php!");
