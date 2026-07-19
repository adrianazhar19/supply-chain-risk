const fs = require('fs');
const filePath = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(filePath, 'utf8');
const norm = s => s.replace(/\r\n/g, '\n');

// ─── PART 1: Add new CSS styles to existing <style> block inside page-compare ─
const oldStyle = `    .cmp-ai-item { background: rgba(255,255,255,.06); border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; }
  </style>`;

const newStyle = `    .cmp-ai-item { background: rgba(255,255,255,.06); border-radius: 8px; padding: 10px 14px; margin-bottom: 8px; }
    /* Advanced Search */
    .cmp-search-wrap { position: relative; }
    .cmp-search-input {
      border-radius: 12px !important;
      padding: 10px 44px 10px 40px !important;
      font-size: .88rem;
      border: 2px solid #e2e8f0;
      background: #fff;
      transition: border-color .2s, box-shadow .2s;
      width: 100%;
    }
    html[data-theme="dark"] .cmp-search-input { background: var(--card-bg); border-color: var(--border-color); color: var(--text-primary); }
    .cmp-search-input:focus { border-color: #0d6efd !important; box-shadow: 0 0 0 3px rgba(13,110,253,.12) !important; outline: none; }
    .cmp-search-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: .9rem; pointer-events: none; }
    .cmp-search-clear { position: absolute; right: 36px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: .85rem; cursor: pointer; display: none; padding: 0; }
    .cmp-search-voice { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; font-size: .9rem; cursor: pointer; padding: 0; }
    .cmp-autocomplete {
      position: absolute; top: calc(100% + 6px); left: 0; right: 0; z-index: 9999;
      background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0,0,0,.12); max-height: 380px; overflow-y: auto;
      display: none;
    }
    html[data-theme="dark"] .cmp-autocomplete { background: var(--card-bg); border-color: var(--border-color); }
    .cmp-autocomplete-item {
      display: flex; align-items: center; gap: 10px; padding: 10px 14px;
      cursor: pointer; transition: background .12s; border-bottom: 1px solid #f1f5f9;
    }
    html[data-theme="dark"] .cmp-autocomplete-item { border-bottom-color: var(--border-color); }
    .cmp-autocomplete-item:last-child { border-bottom: none; }
    .cmp-autocomplete-item:hover, .cmp-autocomplete-item.active { background: rgba(13,110,253,.06); }
    .cmp-autocomplete-item .highlight { color: #0d6efd; font-weight: 700; }
    .cmp-filter-chip {
      font-size: .72rem; font-weight: 600; padding: 4px 12px; border-radius: 20px;
      border: 1px solid #dee2e6; background: transparent; color: var(--text-secondary, #475569);
      cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .cmp-filter-chip.active, .cmp-filter-chip:hover { background: #0d6efd; color: #fff; border-color: #0d6efd; }
    .cmp-stat-pill { display: inline-flex; align-items: center; gap: 5px; font-size: .72rem; font-weight: 700; padding: 3px 10px; border-radius: 10px; }
    .cmp-popular-btn {
      font-size: .75rem; font-weight: 600; padding: 5px 12px; border-radius: 20px;
      border: 1px solid #0d6efd30; background: #0d6efd08; color: #0d6efd;
      cursor: pointer; transition: all .15s; white-space: nowrap;
    }
    .cmp-popular-btn:hover { background: #0d6efd; color: #fff; }
    .cmp-recent-item { display: flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 8px; transition: background .12s; cursor: pointer; }
    .cmp-recent-item:hover { background: rgba(13,110,253,.06); }
    .cmp-fav-chip { display: inline-flex; align-items: center; gap: 5px; font-size: .72rem; font-weight: 600; padding: 3px 10px; border-radius: 20px; border: 1px solid #f59e0b30; background: #f59e0b08; color: #b45309; cursor: pointer; transition: all .15s; }
    .cmp-fav-chip:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .cmp-suggest-item { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 8px; cursor: pointer; transition: background .12s; font-size: .8rem; }
    .cmp-suggest-item:hover { background: rgba(13,110,253,.06); }
  </style>`;

let n = norm(content);
n = n.replace(norm(oldStyle), () => norm(newStyle));
console.log("Updated CSS styles!");

// ─── PART 2: Replace the Country Selector Panel with the enhanced version ─────
const oldSelectorPanel = `  <!-- Country Selector Panel -->
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
  </div>`;

const newSelectorPanel = `  <!-- Advanced Search Panel -->
  <div class="card p-4 mb-3" style="border-radius:14px;">

    <!-- Search Statistics Bar -->
    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
      <span class="cmp-stat-pill" style="background:#0d6efd10;color:#0d6efd;"><i class="bi bi-globe"></i><span id="cmpStatCountries">250</span> Countries</span>
      <span class="cmp-stat-pill" style="background:#10b98110;color:#10b981;"><i class="bi bi-geo"></i>6 Regions</span>
      <span class="cmp-stat-pill" style="background:#f59e0b10;color:#b45309;"><i class="bi bi-anchor"></i><span id="cmpStatPorts">556</span> Ports</span>
      <span class="cmp-stat-pill" style="background:#8b5cf610;color:#7c3aed;"><i class="bi bi-currency-exchange"></i>8 Currencies</span>
      <span class="cmp-stat-pill" style="background:#ef444410;color:#dc2626;"><i class="bi bi-newspaper"></i>80+ News</span>
      <span class="ms-auto" style="font-size:.7rem;color:var(--text-muted);">Smart Search · Instant Filter · Keyboard Navigation</span>
    </div>

    <!-- Smart Search Box -->
    <div class="cmp-search-wrap mb-3" id="cmpSearchWrap">
      <i class="bi bi-search cmp-search-icon"></i>
      <input type="text" id="cmpSmartSearch" class="cmp-search-input form-control"
        placeholder="🔍 Search by country name, ISO code, region, currency, or capital..."
        autocomplete="off" spellcheck="false">
      <button class="cmp-search-clear" id="cmpSearchClear" title="Clear"><i class="bi bi-x-circle-fill"></i></button>
      <button class="cmp-search-voice" title="Voice Search (coming soon)"><i class="bi bi-mic"></i></button>
      <!-- Autocomplete Dropdown -->
      <div class="cmp-autocomplete" id="cmpAutocomplete"></div>
    </div>

    <!-- Quick Filters Row -->
    <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
      <span style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Region:</span>
      <button class="cmp-filter-chip active" data-filter-type="region" data-filter-val="">All Regions</button>
      <button class="cmp-filter-chip" data-filter-type="region" data-filter-val="Asia">Asia</button>
      <button class="cmp-filter-chip" data-filter-type="region" data-filter-val="Europe">Europe</button>
      <button class="cmp-filter-chip" data-filter-type="region" data-filter-val="Africa">Africa</button>
      <button class="cmp-filter-chip" data-filter-type="region" data-filter-val="Americas">Americas</button>
      <button class="cmp-filter-chip" data-filter-type="region" data-filter-val="Oceania">Oceania</button>
      <span class="mx-1" style="color:#e2e8f0;">|</span>
      <span style="font-size:.7rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">Risk:</span>
      <button class="cmp-filter-chip active" data-filter-type="risk" data-filter-val="">All</button>
      <button class="cmp-filter-chip" data-filter-type="risk" data-filter-val="Low">Low</button>
      <button class="cmp-filter-chip" data-filter-type="risk" data-filter-val="Medium">Medium</button>
      <button class="cmp-filter-chip" data-filter-type="risk" data-filter-val="High">High</button>
      <button class="cmp-filter-chip" data-filter-type="risk" data-filter-val="Critical">Critical</button>
    </div>

    <!-- Range Filters -->
    <div class="row g-2 mb-3">
      <div class="col-6 col-md-3">
        <label style="font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">GDP Range (T)</label>
        <select id="cmpFilterGdp" class="form-select form-select-sm" style="border-radius:8px;">
          <option value="">Any GDP</option>
          <option value="0,1">Below $1T</option>
          <option value="1,5">$1T – $5T</option>
          <option value="5,20">$5T – $20T</option>
          <option value="20,999">Above $20T</option>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label style="font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Population (M)</label>
        <select id="cmpFilterPop" class="form-select form-select-sm" style="border-radius:8px;">
          <option value="">Any Population</option>
          <option value="0,10">Below 10M</option>
          <option value="10,100">10M – 100M</option>
          <option value="100,500">100M – 500M</option>
          <option value="500,9999">Above 500M</option>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label style="font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Inflation (%)</label>
        <select id="cmpFilterInflation" class="form-select form-select-sm" style="border-radius:8px;">
          <option value="">Any Inflation</option>
          <option value="0,2">Below 2%</option>
          <option value="2,5">2% – 5%</option>
          <option value="5,10">5% – 10%</option>
          <option value="10,999">Above 10%</option>
        </select>
      </div>
      <div class="col-6 col-md-3">
        <label style="font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;">Currency</label>
        <select id="cmpFilterCurrency" class="form-select form-select-sm" style="border-radius:8px;">
          <option value="">Any Currency</option>
          <option value="USD">USD</option>
          <option value="EUR">EUR</option>
          <option value="GBP">GBP</option>
          <option value="JPY">JPY</option>
          <option value="CNY">CNY</option>
          <option value="SGD">SGD</option>
          <option value="IDR">IDR</option>
          <option value="AUD">AUD</option>
        </select>
      </div>
    </div>

    <!-- Popular Comparisons -->
    <div class="mb-3">
      <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;"><i class="bi bi-fire me-1 text-danger"></i>Popular Comparisons</div>
      <div class="d-flex flex-wrap gap-2" id="cmpPopularButtons">
        <button class="cmp-popular-btn" onclick="setPopularComparison('United States','China')">🇺🇸 US vs 🇨🇳 China</button>
        <button class="cmp-popular-btn" onclick="setPopularComparison('Japan','Germany')">🇯🇵 Japan vs 🇩🇪 Germany</button>
        <button class="cmp-popular-btn" onclick="setPopularComparison('Indonesia','Malaysia')">🇮🇩 Indonesia vs 🇲🇾 Malaysia</button>
        <button class="cmp-popular-btn" onclick="setPopularComparison('Singapore','Australia')">🇸🇬 Singapore vs 🇦🇺 Australia</button>
        <button class="cmp-popular-btn" onclick="setPopularComparison('India','Brazil')">🇮🇳 India vs 🇧🇷 Brazil</button>
      </div>
    </div>

    <!-- Recent Searches + Favorites Row -->
    <div class="row g-3">
      <div class="col-md-6">
        <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">
          <i class="bi bi-clock-history me-1"></i>Recent Searches
          <button onclick="clearRecentSearches()" style="background:none;border:none;color:var(--text-muted);font-size:.65rem;cursor:pointer;margin-left:8px;">Clear All</button>
        </div>
        <div id="cmpRecentSearches" class="d-flex flex-wrap gap-1">
          <span style="font-size:.75rem;color:var(--text-muted);">No recent searches</span>
        </div>
      </div>
      <div class="col-md-6">
        <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">
          <i class="bi bi-star-fill text-warning me-1"></i>Favorite Countries
        </div>
        <div id="cmpFavorites" class="d-flex flex-wrap gap-1">
          <span style="font-size:.75rem;color:var(--text-muted);">No favorites yet — star a country to save it here</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Country Selector Panel -->
  <div class="card p-3 mb-3" style="border-radius:14px;">
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

  <!-- Compare Suggestions Panel (shown when Country A is selected) -->
  <div id="cmpSuggestPanel" class="card p-3 mb-3" style="border-radius:14px;display:none;">
    <div style="font-size:.68rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">
      <i class="bi bi-stars me-1 text-primary"></i>Similar Countries to Compare With
    </div>
    <div id="cmpSuggestList" class="row g-2"></div>
  </div>`;

n = n.replace(norm(oldSelectorPanel), () => norm(newSelectorPanel));
console.log("Replaced Country Selector Panel with Advanced Search!");

// ─── PART 3: Add Advanced Search JS before loadComparePage ──────────────────
const oldLoadCompare = `async function loadComparePage() {
  populateCompareSelects();

  // If both A and B are already selected (e.g. from previous state), auto-run
  const selA = document.getElementById('cmpCountryA');
  const selB = document.getElementById('cmpCountryB');
  if (selA?.value && selB?.value) {
    runComparison();
  }
}`;

const newLoadCompare = `/* ─── ADVANCED SEARCH ENGINE ──────────────────────────────── */

// Smart search fields — all of these are matched against
const CMP_SMART_MAP = {
  // capital -> country code (subset for demo)
  'Tokyo':'jp','Osaka':'jp','Berlin':'de','Frankfurt':'de','London':'gb',
  'Washington':'us','New York':'us','Beijing':'cn','Shanghai':'cn',
  'Singapore':'sg','Jakarta':'id','Sydney':'au','Canberra':'au',
  'Paris':'fr','Madrid':'es','Rome':'it','Seoul':'kr','Mumbai':'in',
  'Kuala Lumpur':'my','Bangkok':'th','Manila':'ph','Hanoi':'vn',
  'Dubai':'ae','Riyadh':'sa','Cairo':'eg','Lagos':'ng','Nairobi':'ke',
  'Sao Paulo':'br','Buenos Aires':'ar','Mexico City':'mx','Ottawa':'ca',
};

// Port->country mapping for smart search
const CMP_PORT_MAP = {
  'Tanjung Priok':'id','Busan':'kr','Yokohama':'jp','Shanghai':'cn',
  'Singapore':'sg','Rotterdam':'nl','Hamburg':'de','Felixstowe':'gb',
  'Los Angeles':'us','Long Beach':'us','New York':'us','Sydney':'au',
};

let _cmpDebounceTimer = null;
let _cmpAcSelected = -1;
let _cmpLastQuery = '';
let _cmpFilterRegion = '';
let _cmpFilterRisk = '';

function cmpGetAllCountries() {
  const real = STATE.countries || [];
  const demoList = Object.values(CMP_DEMO).map(d => ({
    id: 'demo_' + d.code,
    name: d.name,
    code: d.code,
    region: d.region,
    currency: d.currency,
    risk_level: d.risk_level,
    risk_score: d.risk_score,
    gdp: d.gdp,
    population: d.population,
    inflation: d.inflation,
    temperature: d.temperature,
    weather: d.weather,
  }));
  return real.length > 0 ? real : demoList;
}

function cmpHighlight(text, query) {
  if (!query) return text;
  var safe = query.replace(/[-[\]{}()*+?.,\^$|#]/g, '\\$&');
  return text.replace(new RegExp('(' + safe + ')', 'gi'), '<span class="highlight">$1</span>');
}

function cmpSearchCountries(query) {
  const q = (query || '').trim().toLowerCase();
  let all = cmpGetAllCountries();

  // Apply quick filters
  if (_cmpFilterRegion) all = all.filter(c => (c.region||'').toLowerCase().includes(_cmpFilterRegion.toLowerCase()));
  if (_cmpFilterRisk)   all = all.filter(c => (c.risk_level||'').toLowerCase() === _cmpFilterRisk.toLowerCase());

  // GDP range filter
  const gdpRange = document.getElementById('cmpFilterGdp')?.value;
  if (gdpRange) {
    const [gMin, gMax] = gdpRange.split(',').map(Number);
    all = all.filter(c => {
      const g = parseFloat(c.gdp || 0) / 1e12;
      return g >= gMin && g <= gMax;
    });
  }

  // Population range filter
  const popRange = document.getElementById('cmpFilterPop')?.value;
  if (popRange) {
    const [pMin, pMax] = popRange.split(',').map(Number);
    all = all.filter(c => {
      const p = parseFloat(c.population || 0) / 1e6;
      return p >= pMin && p <= pMax;
    });
  }

  // Inflation filter
  const inflRange = document.getElementById('cmpFilterInflation')?.value;
  if (inflRange) {
    const [iMin, iMax] = inflRange.split(',').map(Number);
    all = all.filter(c => {
      const i = parseFloat(c.inflation || 0);
      return i >= iMin && i <= iMax;
    });
  }

  // Currency filter
  const curr = document.getElementById('cmpFilterCurrency')?.value;
  if (curr) all = all.filter(c => (c.currency || '') === curr);

  if (!q) return all.slice(0, 50);

  // Smart alias lookups
  let aliasCode = null;
  Object.entries(CMP_SMART_MAP).forEach(([k, v]) => {
    if (k.toLowerCase().includes(q)) aliasCode = v;
  });
  Object.entries(CMP_PORT_MAP).forEach(([k, v]) => {
    if (k.toLowerCase().includes(q)) aliasCode = v;
  });

  return all.filter(c => {
    const nameMatch    = (c.name || '').toLowerCase().includes(q);
    const codeMatch    = (c.code || '').toLowerCase().includes(q) || (c.iso_code || '').toLowerCase().includes(q);
    const regionMatch  = (c.region || '').toLowerCase().includes(q);
    const currMatch    = (c.currency || '').toLowerCase().includes(q);
    const aliasMatch   = aliasCode && (c.code || '').toLowerCase() === aliasCode;
    return nameMatch || codeMatch || regionMatch || currMatch || aliasMatch;
  }).slice(0, 20);
}

function cmpRenderAutocomplete(results, query) {
  const ac = document.getElementById('cmpAutocomplete');
  if (!results.length) {
    ac.innerHTML = \`
      <div class="p-3 text-center" style="font-size:.82rem;color:var(--text-muted);">
        <i class="bi bi-search" style="font-size:1.4rem;display:block;margin-bottom:6px;opacity:.4;"></i>
        No countries found for "<b>\${query}</b>"
        <div class="mt-2" style="font-size:.75rem;">Try: United States, Asia, EUR, Tokyo, Busan</div>
      </div>
    \`;
    ac.style.display = 'block';
    return;
  }

  ac.innerHTML = results.map((c, idx) => {
    const flagCode = (c.code || '').toLowerCase();
    const flagHtml = flagCode ? \`<img src="https://flagcdn.com/w20/\${flagCode}.png" width="20" style="border-radius:3px;flex-shrink:0;" alt="">\` : '<span style="width:20px;height:14px;background:#e2e8f0;border-radius:2px;flex-shrink:0;display:inline-block;"></span>';
    const gdpStr   = parseFloat(c.gdp||0) > 0 ? '$' + (parseFloat(c.gdp)/1e12).toFixed(2) + 'T' : 'N/A';
    const tempStr  = c.temperature ? c.temperature + '°C' : '';
    const wIcon    = weatherEmoji(c.weather || 'Clear');
    const nameHl   = cmpHighlight(c.name || '', query);
    const regionHl = cmpHighlight(c.region || '', query);
    const currHl   = cmpHighlight(c.currency || '', query);
    const codeHl   = cmpHighlight((c.code || c.iso_code || '').toUpperCase(), query);
    const riskLvl  = c.risk_level || 'Low';
    const riskColors = { Low:'#198754', Medium:'#fd7e14', High:'#dc3545', Critical:'#6f42c1' };
    const rC = riskColors[riskLvl] || '#6c757d';
    return \`
      <div class="cmp-autocomplete-item" data-idx="\${idx}" data-id="\${c.id}" data-name="\${c.name}">
        \${flagHtml}
        <div style="flex:1;min-width:0;">
          <div style="font-size:.82rem;font-weight:700;">\${nameHl}
            <span style="font-size:.68rem;font-weight:600;color:#94a3b8;margin-left:4px;">\${codeHl}</span>
          </div>
          <div style="font-size:.7rem;color:var(--text-muted);">\${regionHl} · \${currHl}</div>
        </div>
        <div class="text-end" style="flex-shrink:0;min-width:80px;">
          <div style="font-size:.72rem;font-weight:700;font-family:var(--font-mono);">\${gdpStr}</div>
          <div style="font-size:.68rem;color:var(--text-muted);">Risk: <span style="color:\${rC};font-weight:700;">\${parseFloat(c.risk_score||0).toFixed(1)}</span></div>
          <div style="font-size:.68rem;">\${wIcon} \${tempStr}</div>
        </div>
        <div class="d-flex flex-column gap-1 ms-2">
          <button onclick="event.stopPropagation(); setCompareFromSearch('\${c.id}','\${c.name}','A')" class="btn btn-xs" style="font-size:.6rem;padding:2px 6px;border-radius:5px;background:#0d6efd10;color:#0d6efd;border:1px solid #0d6efd20;">A</button>
          <button onclick="event.stopPropagation(); setCompareFromSearch('\${c.id}','\${c.name}','B')" class="btn btn-xs" style="font-size:.6rem;padding:2px 6px;border-radius:5px;background:#ef444410;color:#ef4444;border:1px solid #ef444420;">B</button>
        </div>
      </div>
    \`;
  }).join('');

  _cmpAcSelected = -1;
  ac.style.display = 'block';

  // Click handlers
  ac.querySelectorAll('.cmp-autocomplete-item').forEach(item => {
    item.addEventListener('click', function() {
      const id   = this.dataset.id;
      const name = this.dataset.name;
      setCompareFromSearch(id, name, 'A');
      hideAutocomplete();
    });
  });
}

function hideAutocomplete() {
  const ac = document.getElementById('cmpAutocomplete');
  if (ac) ac.style.display = 'none';
}

function setCompareFromSearch(id, name, slot) {
  const selId = slot === 'A' ? 'cmpCountryA' : slot === 'B' ? 'cmpCountryB' : 'cmpCountryC';
  const sel = document.getElementById(selId);
  if (sel) {
    // Find matching option
    const opt = Array.from(sel.options).find(o => String(o.value) === String(id) || o.textContent.trim().startsWith(name));
    if (opt) sel.value = opt.value;
    else {
      // Add as demo option if not found
      const newOpt = document.createElement('option');
      newOpt.value = id;
      newOpt.textContent = name;
      sel.appendChild(newOpt);
      sel.value = id;
    }
  }

  addToRecentSearches(name, id);
  hideAutocomplete();
  document.getElementById('cmpSmartSearch').value = '';
  const clr = document.getElementById('cmpSearchClear');
  if (clr) clr.style.display = 'none';

  // If A is selected, show suggestions
  if (slot === 'A') showCompareSuggestions(id, name);
}

/* ─── Recent Searches ─────────────────────────────────────── */
function addToRecentSearches(name, id) {
  let recent = JSON.parse(localStorage.getItem('cmp_recent') || '[]');
  recent = recent.filter(r => r.name !== name);
  recent.unshift({ name, id, time: Date.now() });
  recent = recent.slice(0, 10);
  localStorage.setItem('cmp_recent', JSON.stringify(recent));
  renderRecentSearches();
}

function renderRecentSearches() {
  const el = document.getElementById('cmpRecentSearches');
  if (!el) return;
  let recent = JSON.parse(localStorage.getItem('cmp_recent') || '[]');
  if (!recent.length) {
    el.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">No recent searches</span>';
    return;
  }
  el.innerHTML = recent.map(r => \`
    <div class="cmp-recent-item" onclick="setCompareFromSearch('\${r.id}','\${r.name}','A')" title="Search \${r.name}">
      <i class="bi bi-clock-history" style="font-size:.75rem;color:#94a3b8;"></i>
      <span style="font-size:.75rem;font-weight:600;">\${r.name}</span>
      <button onclick="event.stopPropagation(); pinRecent('\${r.name}','\${r.id}')" style="background:none;border:none;padding:0;color:#f59e0b;font-size:.7rem;cursor:pointer;" title="Pin"><i class="bi bi-pin"></i></button>
      <button onclick="event.stopPropagation(); removeRecent('\${r.name}')" style="background:none;border:none;padding:0;color:#94a3b8;font-size:.7rem;cursor:pointer;" title="Delete"><i class="bi bi-x"></i></button>
    </div>
  \`).join('');
}

function removeRecent(name) {
  let recent = JSON.parse(localStorage.getItem('cmp_recent') || '[]');
  recent = recent.filter(r => r.name !== name);
  localStorage.setItem('cmp_recent', JSON.stringify(recent));
  renderRecentSearches();
}

function pinRecent(name, id) {
  let favs = JSON.parse(localStorage.getItem('cmp_favs') || '[]');
  if (!favs.find(f => f.name === name)) {
    favs.push({ name, id });
    localStorage.setItem('cmp_favs', JSON.stringify(favs));
  }
  renderFavorites();
}

function clearRecentSearches() {
  localStorage.removeItem('cmp_recent');
  renderRecentSearches();
}

/* ─── Favorites ───────────────────────────────────────────── */
function renderFavorites() {
  const el = document.getElementById('cmpFavorites');
  if (!el) return;
  let favs = JSON.parse(localStorage.getItem('cmp_favs') || '[]');
  if (!favs.length) {
    el.innerHTML = '<span style="font-size:.75rem;color:var(--text-muted);">No favorites yet — pin a country to save it here</span>';
    return;
  }
  el.innerHTML = favs.map(f => \`
    <span class="cmp-fav-chip" onclick="setCompareFromSearch('\${f.id}','\${f.name}','A')">
      <i class="bi bi-star-fill"></i>\${f.name}
      <button onclick="event.stopPropagation(); removeFav('\${f.name}')" style="background:none;border:none;padding:0;color:inherit;font-size:.7rem;cursor:pointer;"><i class="bi bi-x"></i></button>
    </span>
  \`).join('');
}

function removeFav(name) {
  let favs = JSON.parse(localStorage.getItem('cmp_favs') || '[]');
  favs = favs.filter(f => f.name !== name);
  localStorage.setItem('cmp_favs', JSON.stringify(favs));
  renderFavorites();
}

/* ─── Compare Suggestions ─────────────────────────────────── */
function showCompareSuggestions(countryId, countryName) {
  const panel = document.getElementById('cmpSuggestPanel');
  const list  = document.getElementById('cmpSuggestList');
  if (!panel || !list) return;

  // Find the base country
  const all = cmpGetAllCountries();
  const base = all.find(c => String(c.id) === String(countryId) || c.name === countryName);
  if (!base) { panel.style.display = 'none'; return; }

  // Score by similarity: same region + close GDP + close risk
  const scored = all
    .filter(c => String(c.id) !== String(countryId) && c.name !== countryName)
    .map(c => {
      let score = 0;
      if ((c.region||'') === (base.region||'')) score += 30;
      const gdpDiff = Math.abs(parseFloat(c.gdp||0) - parseFloat(base.gdp||0)) / 1e12;
      if (gdpDiff < 1)  score += 25;
      if (gdpDiff < 5)  score += 10;
      const riskDiff = Math.abs(parseFloat(c.risk_score||0) - parseFloat(base.risk_score||0));
      if (riskDiff < 10)  score += 20;
      if (riskDiff < 20)  score += 10;
      const popDiff = Math.abs(parseFloat(c.population||0) - parseFloat(base.population||0)) / 1e6;
      if (popDiff < 50) score += 15;
      return { ...c, _score: score };
    })
    .sort((a, b) => b._score - a._score)
    .slice(0, 6);

  list.innerHTML = scored.map(c => {
    const flagCode = (c.code || '').toLowerCase();
    const flagHtml = flagCode ? \`<img src="https://flagcdn.com/w20/\${flagCode}.png" width="18" style="border-radius:2px;" alt="">\` : '';
    const gdpStr   = parseFloat(c.gdp||0) > 0 ? '$' + (parseFloat(c.gdp)/1e12).toFixed(2) + 'T' : 'N/A';
    return \`
      <div class="col-6 col-md-4 col-lg-2">
        <div class="cmp-suggest-item" onclick="setCompareFromSearch('\${c.id}','\${c.name}','B')">
          \${flagHtml}
          <div>
            <div style="font-weight:700;font-size:.78rem;">\${c.name}</div>
            <div style="font-size:.65rem;color:var(--text-muted);">\${gdpStr} · Risk \${parseFloat(c.risk_score||0).toFixed(0)}</div>
          </div>
        </div>
      </div>
    \`;
  }).join('');

  panel.style.display = '';
}

/* ─── Popular Comparisons ─────────────────────────────────── */
function setPopularComparison(nameA, nameB) {
  const all = cmpGetAllCountries();
  const cA = all.find(c => c.name === nameA) || { id: 'demo_us', name: nameA };
  const cB = all.find(c => c.name === nameB) || { id: 'demo_cn', name: nameB };

  const selA = document.getElementById('cmpCountryA');
  const selB = document.getElementById('cmpCountryB');

  function setSelect(sel, c) {
    let opt = Array.from(sel.options).find(o => String(o.value) === String(c.id) || o.textContent.trim() === c.name);
    if (!opt) {
      opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      sel.appendChild(opt);
    }
    sel.value = opt.value;
  }

  if (selA) setSelect(selA, cA);
  if (selB) setSelect(selB, cB);

  addToRecentSearches(nameA, cA.id);
  addToRecentSearches(nameB, cB.id);
  showCompareSuggestions(cA.id, cA.name);
  runComparison();
}

/* ─── Wire up Advanced Search Inputs ─────────────────────── */
function initAdvancedSearch() {
  const input  = document.getElementById('cmpSmartSearch');
  const clrBtn = document.getElementById('cmpSearchClear');
  if (!input || input.__wired) return;
  input.__wired = true;

  input.addEventListener('input', function() {
    const q = this.value.trim();
    clrBtn.style.display = q ? 'block' : 'none';
    clearTimeout(_cmpDebounceTimer);
    _cmpDebounceTimer = setTimeout(() => {
      if (!q) { hideAutocomplete(); return; }
      _cmpLastQuery = q;
      const results = cmpSearchCountries(q);
      cmpRenderAutocomplete(results, q);
    }, 300);
  });

  input.addEventListener('keydown', function(e) {
    const ac = document.getElementById('cmpAutocomplete');
    if (!ac || ac.style.display === 'none') return;
    const items = ac.querySelectorAll('.cmp-autocomplete-item');
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      _cmpAcSelected = Math.min(_cmpAcSelected + 1, items.length - 1);
      items.forEach((it, i) => it.classList.toggle('active', i === _cmpAcSelected));
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      _cmpAcSelected = Math.max(_cmpAcSelected - 1, 0);
      items.forEach((it, i) => it.classList.toggle('active', i === _cmpAcSelected));
    } else if (e.key === 'Enter') {
      e.preventDefault();
      if (_cmpAcSelected >= 0 && items[_cmpAcSelected]) items[_cmpAcSelected].click();
    } else if (e.key === 'Escape') {
      hideAutocomplete();
    }
  });

  input.addEventListener('focus', function() {
    if (this.value.trim()) {
      const results = cmpSearchCountries(this.value.trim());
      cmpRenderAutocomplete(results, this.value.trim());
    }
  });

  clrBtn.addEventListener('click', function() {
    input.value = '';
    this.style.display = 'none';
    hideAutocomplete();
    input.focus();
  });

  // Click outside closes autocomplete
  document.addEventListener('click', function(e) {
    if (!document.getElementById('cmpSearchWrap')?.contains(e.target)) hideAutocomplete();
  }, true);

  // Quick filter chips
  document.querySelectorAll('[data-filter-type="region"]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-filter-type="region"]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      _cmpFilterRegion = this.dataset.filterVal;
      if (document.getElementById('cmpSmartSearch').value.trim()) {
        const results = cmpSearchCountries(document.getElementById('cmpSmartSearch').value.trim());
        cmpRenderAutocomplete(results, document.getElementById('cmpSmartSearch').value.trim());
      }
    });
  });

  document.querySelectorAll('[data-filter-type="risk"]').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('[data-filter-type="risk"]').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      _cmpFilterRisk = this.dataset.filterVal;
    });
  });

  // Range filters trigger search
  ['cmpFilterGdp','cmpFilterPop','cmpFilterInflation','cmpFilterCurrency'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('change', () => {
      if (document.getElementById('cmpSmartSearch').value.trim()) {
        const results = cmpSearchCountries(document.getElementById('cmpSmartSearch').value.trim());
        cmpRenderAutocomplete(results, document.getElementById('cmpSmartSearch').value.trim());
      }
    });
  });

  // Update stats
  const all = cmpGetAllCountries();
  const statEl = document.getElementById('cmpStatCountries');
  if (statEl) statEl.textContent = all.length || 250;
  const portStatEl = document.getElementById('cmpStatPorts');
  if (portStatEl && STATE.ports?.length) portStatEl.textContent = STATE.ports.length;
}

async function loadComparePage() {
  populateCompareSelects();
  initAdvancedSearch();
  renderRecentSearches();
  renderFavorites();

  // If both A and B are already selected (e.g. from previous state), auto-run
  const selA = document.getElementById('cmpCountryA');
  const selB = document.getElementById('cmpCountryB');
  if (selA?.value && selB?.value) {
    runComparison();
  }
}`;

n = n.replace(norm(oldLoadCompare), () => norm(newLoadCompare));
console.log("Replaced loadComparePage with advanced search engine!");

const out = n.replace(/\n/g, '\r\n');
fs.writeFileSync(filePath, out, 'utf8');
console.log("\nSaved dashboard.blade.php with Advanced Search!");
