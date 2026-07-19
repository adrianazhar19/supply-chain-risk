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
    #countryTable_wrapper .dt-buttons { display: none !important; }
    #countryTable_filter { display: none !important; }
    #countryTable_length { display: none !important; }
    #countryTable_info { display: none !important; }
    #countryTable_paginate { display: none !important; }
    
    #countryTable {
      border-collapse: separate !important;
      border-spacing: 0 !important;
      font-size: 0.88rem !important;
      color: #334155 !important;
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
    
    /* Column align */
    .col-align-left { text-align: left !important; }
    .col-align-right { text-align: right !important; }
    .col-align-center { text-align: center !important; }
    
    /* Status Badges */
    .risk-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 12px;
      text-transform: uppercase;
      letter-spacing: 0.03em;
    }
    .risk-badge.low { background: #19875415; color: #198754; border: 1px solid #19875430; }
    .risk-badge.medium { background: #fd7e1415; color: #fd7e14; border: 1px solid #fd7e1430; }
    .risk-badge.high { background: #dc354515; color: #dc3545; border: 1px solid #dc354530; }
    .risk-badge.critical { background: #6f42c115; color: #6f42c1; border: 1px solid #6f42c130; }
    
    /* Rounded square buttons */
    .btn-action-sq {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      border: none;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-action-sq:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
  </style>

  <div class="card shadow-sm border-0" style="border-radius: 20px; padding: 30px; background: #ffffff;">
    <!-- Header -->
    <div class="d-flex align-items-start gap-3 mb-4">
      <div style="background: rgba(13, 110, 253, 0.08); padding: 12px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">
        <i class="bi bi-globe-americas text-primary" style="font-size: 1.8rem; line-height: 1;"></i>
      </div>
      <div>
        <h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Country Intelligence Ledger</h2>
        <p class="text-secondary mb-0" style="font-size: 0.85rem; line-height: 1.4;">
          View comprehensive intelligence for 250 countries including GDP, population, inflation, risk score, weather and port information.
        </p>
      </div>
    </div>

    <!-- Top Toolbar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 gap-2">
      <!-- Left toolbar: Entries + Search -->
      <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Show</span>
        <select id="ledgerLength" class="form-select form-select-sm border-light-subtle" style="width: 75px; border-radius: 8px; height: 38px; font-size: 0.85rem; cursor: pointer;">
          <option value="10">10</option>
          <option value="25" selected>25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="text-muted small me-3">entries</span>
        
        <div class="position-relative" style="width: 320px;">
          <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.9rem;"></i>
          <input type="text" id="ledgerSearch" class="form-control form-control-sm border-light-subtle" placeholder="Search Country..." style="padding-left: 36px; border-radius: 8px; height: 38px; font-size: 0.85rem;">
        </div>
      </div>
      
      <!-- Right toolbar: Exports + Refresh -->
      <div class="d-flex gap-1">
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" onclick="exportLedgerTable('csv')" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 14px; border-color: #dee2e6;"><i class="bi bi-filetype-csv" style="font-size: 0.95rem;"></i>CSV</button>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" onclick="exportLedgerTable('excel')" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 14px; border-color: #dee2e6;"><i class="bi bi-file-earmark-excel" style="font-size: 0.95rem;"></i>Excel</button>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" onclick="exportLedgerTable('pdf')" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 14px; border-color: #dee2e6;"><i class="bi bi-file-earmark-pdf" style="font-size: 0.95rem;"></i>PDF</button>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" onclick="window.print()" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 14px; border-color: #dee2e6;"><i class="bi bi-printer" style="font-size: 0.95rem;"></i>Print</button>
        <button class="btn btn-sm btn-primary d-flex align-items-center gap-1.5 ms-2" onclick="loadAllData()" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 16px;"><i class="bi bi-arrow-clockwise" style="font-size: 0.95rem;"></i>Refresh</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
      <table id="countryTable" class="table w-100 mb-0">
        <thead>
          <tr>
            <th class="col-align-center" style="width: 60px;">Flag</th>
            <th class="col-align-left">Country</th>
            <th class="col-align-center" style="width: 90px;">ISO Code</th>
            <th class="col-align-left">Region</th>
            <th class="col-align-left">Currency</th>
            <th class="col-align-right">GDP (B USD)</th>
            <th class="col-align-right">Population (M)</th>
            <th class="col-align-right">Inflation</th>
            <th class="col-align-center">Risk Score</th>
            <th class="col-align-center">Status</th>
            <th class="col-align-center" style="width: 170px;">Action</th>
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
                  @if($country->code)
                    <img src="https://flagcdn.com/w40/{{ strtolower($country->code) }}.png" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt="">
                  @else
                    N/A
                  @endif
                </td>
                <td class="col-align-left"><strong>{{ $country->name ?? 'N/A' }}</strong></td>
                <td class="col-align-center"><code style="font-size:.75rem;">{{ $country->code ?? 'N/A' }}</code></td>
                <td class="col-align-left" style="color:var(--text-secondary);">{{ $country->region ?? 'N/A' }}</td>
                <td class="col-align-left">{{ $country->currency ?? 'N/A' }}</td>
                <td class="col-align-right">{{ $gdp ? number_format($gdp/1e9, 1) : 'N/A' }}</td>
                <td class="col-align-right">{{ $population ? number_format($population/1e6, 1) : 'N/A' }}</td>
                <td class="col-align-right {{ $inflation > 5 ? 'text-danger' : '' }}">{{ $inflation ? number_format($inflation, 2).'%' : 'N/A' }}</td>
                <td class="col-align-center"><span style="font-family:var(--font-mono);font-weight:700;">{{ $riskScore ? number_format($riskScore, 1) : 'N/A' }}</span></td>
                <td class="col-align-center">
                  <span class="risk-badge {{ strtolower($riskLevel) }}">{{ $riskLevel }}</span>
                </td>
                <td class="col-align-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="Profile" data-bs-toggle="tooltip" style="background-color: #0d6efd;">
                      <i class="bi bi-person-badge"></i>
                    </button>
                    <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="Map" data-bs-toggle="tooltip" style="background-color: #198754;">
                      <i class="bi bi-geo-alt"></i>
                    </button>
                    <button class="btn-action-sq text-dark" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="Weather" data-bs-toggle="tooltip" style="background-color: #ffc107;">
                      <i class="bi bi-cloud-sun"></i>
                    </button>
                    <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry({{ $country->id }})" title="Ports" data-bs-toggle="tooltip" style="background-color: #6c757d;">
                      <i class="bi bi-anchor"></i>
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

    <!-- Footer -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 pt-3 border-top border-light-subtle">
      <div id="ledgerInfo" class="text-secondary small">Showing 0 to 0 of 0 countries</div>
      <div id="ledgerPagination" class="d-flex align-items-center gap-1">
        <!-- Dynamic pagination buttons -->
      </div>
    </div>
  </div>
</section>`;

// Apply HTML replacement safely using arrow callback
normContent = normContent.replace(targetHtmlBlock, () => replacementHtmlBlock);
console.log("Successfully replaced page-countries HTML layout block!");

// 2. Find and replace script functions in memory
const jsStart = 'function updateLedgerKPIs() {';
const portsComment = 'PORTS TABLE';

const jsStartIdx = normContent.indexOf(jsStart);
if (jsStartIdx === -1) {
    console.error("Error: Could not find function updateLedgerKPIs() start!");
    process.exit(1);
}

const portsIdx = normContent.indexOf(portsComment, jsStartIdx);
if (portsIdx === -1) {
    console.error("Error: Could not find PORTS TABLE comment block after jsStartIdx!");
    process.exit(1);
}

const jsEndIdx = normContent.lastIndexOf('/*', portsIdx);
if (jsEndIdx === -1 || jsEndIdx <= jsStartIdx) {
    console.error("Error: Could not find end of initCountriesTable() before PORTS TABLE!");
    process.exit(1);
}

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
  
  if (avgGdpEl) avgGdpEl.textContent = avgGdp ? '$' + (avgGdp / 1e9).toFixed(2) + 'B' : 'N/A';
  if (avgPopEl) avgPopEl.textContent = avgPop ? (avgPop / 1e6).toFixed(2) + 'M' : 'N/A';
  if (avgInflEl) avgInflEl.textContent = avgInfl ? avgInfl.toFixed(2) + '%' : 'N/A';
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

window.changeLedgerPage = function(target) {
  const table = STATE.dt.countries;
  if (!table) return;
  if (target === 'prev') {
    table.page('previous').draw('page');
  } else if (target === 'next') {
    table.page('next').draw('page');
  } else {
    table.page(target).draw('page');
  }
};

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

  try {
    tbody.innerHTML = STATE.countries.map(c => {
      if (!c) return '';
      const name = c.name || 'N/A';
      const code = c.code || 'N/A';
      const region = c.region || 'N/A';
      const currency = c.currency || 'N/A';
      const flagUrl = code !== 'N/A' ? \`https://flagcdn.com/w40/\${code.toLowerCase()}.png\` : '';
      const flagHtml = flagUrl ? \`<img src="\${flagUrl}" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt="">\` : 'N/A';
      
      const gdpVal = parseFloat(c.gdp);
      const gdpStr = (isNaN(gdpVal) || gdpVal <= 0) ? 'N/A' : (gdpVal / 1e9).toFixed(1);
      
      const popVal = parseFloat(c.population);
      const popStr = (isNaN(popVal) || popVal <= 0) ? 'N/A' : (popVal / 1e6).toFixed(1);
      
      const inflVal = parseFloat(c.inflation);
      const inflStr = isNaN(inflVal) ? 'N/A' : inflVal.toFixed(2) + '%';
      const inflClass = !isNaN(inflVal) && inflVal > 5 ? 'text-danger' : '';
      
      const riskScoreVal = parseFloat(c.risk_score);
      const riskScoreStr = isNaN(riskScoreVal) ? 'N/A' : riskScoreVal.toFixed(1);
      
      const riskLevel = c.risk_level || 'Low';
      const riskPillHtml = \`<span class="risk-badge \${riskLevel.toLowerCase()}">\${riskLevel}</span>\`;
      
      return \`<tr onclick="viewCountry(\${c.id})" style="cursor:pointer;">
        <td class="col-align-center">\${flagHtml}</td>
        <td class="col-align-left"><strong>\${name}</strong></td>
        <td class="col-align-center"><code style="font-size:.75rem;">\${code}</code></td>
        <td class="col-align-left" style="color:var(--text-secondary);">\${region}</td>
        <td class="col-align-left">\${currency}</td>
        <td class="col-align-right">\${gdpStr}</td>
        <td class="col-align-right">\${popStr}</td>
        <td class="col-align-right \${inflClass}">\${inflStr}</td>
        <td class="col-align-center"><span style="font-family:var(--font-mono);font-weight:700;">\${riskScoreStr}</span></td>
        <td class="col-align-center">\${riskPillHtml}</td>
        <td class="col-align-center">
          <div class="d-flex justify-content-center gap-1">
            <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="Profile" data-bs-toggle="tooltip" style="background-color: #0d6efd;">
              <i class="bi bi-person-badge"></i>
            </button>
            <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="Map" data-bs-toggle="tooltip" style="background-color: #198754;">
              <i class="bi bi-geo-alt"></i>
            </button>
            <button class="btn-action-sq text-dark" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="Weather" data-bs-toggle="tooltip" style="background-color: #ffc107;">
              <i class="bi bi-cloud-sun"></i>
            </button>
            <button class="btn-action-sq text-white" onclick="event.stopPropagation(); viewCountry(\${c.id})" title="Ports" data-bs-toggle="tooltip" style="background-color: #6c757d;">
              <i class="bi bi-anchor"></i>
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

    // Bind custom search
    const ledgerSearchInput = document.getElementById('ledgerSearch');
    if (ledgerSearchInput) {
      ledgerSearchInput.addEventListener('input', function() {
        STATE.dt.countries.search(this.value).draw();
      });
    }

    // Bind custom length
    const ledgerLengthSelect = document.getElementById('ledgerLength');
    if (ledgerLengthSelect) {
      ledgerLengthSelect.addEventListener('change', function() {
        STATE.dt.countries.page.len(parseInt(this.value)).draw();
      });
    }

    // Draw handler for custom pagination info and buttons
    STATE.dt.countries.on('draw', function() {
      const info = STATE.dt.countries.page.info();
      
      // Update info: "Showing 1 to 25 of 250 countries"
      const infoEl = document.getElementById('ledgerInfo');
      if (infoEl) {
        if (info.recordsDisplay === 0) {
          infoEl.textContent = 'Showing 0 to 0 of 0 countries';
        } else {
          infoEl.textContent = \`Showing \${info.start + 1} to \${Math.min(info.start + info.length, info.recordsDisplay)} of \${info.recordsDisplay} countries\`;
        }
      }
      
      // Update pagination links
      const paginationEl = document.getElementById('ledgerPagination');
      if (paginationEl) {
        let html = '';
        
        // Previous Button
        html += \`<button class="btn btn-sm btn-link text-decoration-none fw-semibold \${info.page === 0 ? 'disabled text-muted' : 'text-primary'}" onclick="changeLedgerPage('prev')">Previous</button>\`;
        
        // Page links (e.g. 1 | 2 | 3 | ... | 10)
        const totalPages = info.pages;
        const currentPage = info.page;
        
        if (totalPages > 0) {
          html += \`<span class="mx-2 text-muted">|</span>\`;
          
          let startPage = Math.max(0, currentPage - 1);
          let endPage = Math.min(totalPages - 1, startPage + 2);
          
          if (endPage - startPage < 2) {
            startPage = Math.max(0, endPage - 2);
          }
          
          for (let p = startPage; p <= endPage; p++) {
            html += \`<button class="btn btn-sm \${p === currentPage ? 'btn-primary text-white fw-bold px-2 py-0.5' : 'btn-link text-decoration-none text-secondary'}" style="min-width:26px; border-radius:6px; font-size:0.82rem;" onclick="changeLedgerPage(\${p})">\${p + 1}</button>\`;
            if (p < endPage) {
              html += \`<span class="mx-1 text-muted">|</span>\`;
            }
          }
          
          // Ellipsis to last page if necessary
          if (endPage < totalPages - 1) {
            if (endPage < totalPages - 2) {
              html += \`<span class="mx-1 text-muted">|</span><span class="text-muted">...</span>\`;
            }
            html += \`<span class="mx-1 text-muted">|</span><button class="btn btn-sm btn-link text-decoration-none text-secondary" style="min-width:26px; border-radius:6px; font-size:0.82rem;" onclick="changeLedgerPage(\${totalPages - 1})">\${totalPages}</button>\`;
          }
          
          html += \`<span class="mx-2 text-muted">|</span>\`;
        }
        
        // Next Button
        html += \`<button class="btn btn-sm btn-link text-decoration-none fw-semibold \${currentPage === totalPages - 1 || totalPages === 0 ? 'disabled text-muted' : 'text-primary'}" onclick="changeLedgerPage('next')">Next</button>\`;
        
        paginationEl.innerHTML = html;
      }
    });

    // Trigger initial draw event to build the custom pagination layout
    STATE.dt.countries.draw(false);

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

// Convert line endings back to CRLF
const finalContent = normContent.replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php with Bloomberg redesign!");
