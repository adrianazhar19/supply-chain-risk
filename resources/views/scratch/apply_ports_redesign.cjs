const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// Normalize line endings to LF first for reliable search
let normContent = content.replace(/\r\n/g, '\n');

// 1. Replace page-ports section block
const startStr = '<!-- ─── PAGE: PORTS ──────────────────────────────── -->';
const endStr = '</section>';

const startIdx = normContent.indexOf(startStr);
if (startIdx === -1) {
    console.error("Error: Could not find page-ports start in dashboard.blade.php!");
    process.exit(1);
}

const endIdx = normContent.indexOf(endStr, startIdx);
if (endIdx === -1) {
    console.error("Error: Could not find page-ports end in dashboard.blade.php!");
    process.exit(1);
}

const targetHtmlBlock = normContent.substring(startIdx, endIdx + endStr.length);

const replacementHtmlBlock = `<!-- ─── PAGE: PORTS ──────────────────────────────── -->
<section class="content-page" id="page-ports">
  <style>
    .port-header-btn {
      padding: 8px 16px;
      font-size: 0.8rem;
      font-weight: 600;
      border-radius: 8px;
      transition: all 0.2s ease;
    }
    #portsTable_wrapper .dt-buttons {
      display: none !important;
    }
    #portsTable {
      border-collapse: separate !important;
      border-spacing: 0 !important;
      width: 100% !important;
      font-size: 0.88rem !important;
      color: #334155 !important;
    }
    #portsTable thead th {
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
    html[data-theme="dark"] #portsTable thead th {
      background: #1e293b !important;
      color: #94a3b8 !important;
      border-bottom: 2px solid #334155 !important;
    }
    #portsTable tbody tr {
      transition: background-color 0.15s ease;
    }
    #portsTable tbody tr:nth-of-type(even) {
      background-color: rgba(248, 250, 252, 0.4);
    }
    html[data-theme="dark"] #portsTable tbody tr:nth-of-type(even) {
      background-color: rgba(30, 41, 59, 0.15);
    }
    #portsTable tbody tr:hover {
      background-color: rgba(59, 130, 246, 0.06) !important;
    }
    
    .badge-size {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 12px;
      text-transform: uppercase;
    }
    .badge-size.small { background: #0d6efd15; color: #0d6efd; border: 1px solid #0d6efd30; }
    .badge-size.medium { background: #fd7e1415; color: #fd7e14; border: 1px solid #fd7e1430; }
    .badge-size.large { background: #19875415; color: #198754; border: 1px solid #19875430; }
    .badge-size.very-large { background: #6f42c115; color: #6f42c1; border: 1px solid #6f42c130; }

    .badge-type {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 12px;
      text-transform: uppercase;
    }
    .badge-type.commercial { background: #0d6efd15; color: #0d6efd; border: 1px solid #0d6efd30; }
    .badge-type.industrial { background: #21252915; color: #212529; border: 1px solid #21252930; }
    .badge-type.fishing { background: #0dcaf015; color: #0dcaf0; border: 1px solid #0dcaf030; }
    .badge-type.military { background: #bb2d3b15; color: #bb2d3b; border: 1px solid #bb2d3b30; }
    .badge-type.container { background: #15734715; color: #157347; border: 1px solid #15734730; }
    .badge-type.oil-terminal { background: #ffca2c15; color: #ffca2c; border: 1px solid #ffca2c30; }
    
    .btn-action-sq-port {
      width: 32px;
      height: 32px;
      padding: 0;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      border: none;
      transition: all 0.2s ease;
      box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .btn-action-sq-port:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
  </style>

  <div class="card shadow-sm border-0" style="border-radius: 20px; padding: 30px; background: #ffffff;">
    <!-- Header -->
    <div class="d-flex align-items-start gap-3 mb-4">
      <div style="background: rgba(25, 135, 84, 0.08); padding: 12px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center;">
        <i class="bi bi-water text-success" style="font-size: 1.8rem; line-height: 1;"></i>
      </div>
      <div>
        <h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">Port Intelligence Database</h2>
        <p class="text-secondary mb-0" style="font-size: 0.85rem; line-height: 1.4;">
          Access comprehensive cargo and logistics datasets for global port hubs, harbors, container locations, and maritime networks.
        </p>
      </div>
    </div>

    <!-- Top Toolbar -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4 gap-2 border-bottom pb-3 border-light-subtle">
      <div class="d-flex align-items-center gap-2">
        <select class="form-select form-select-sm border-light-subtle" id="portCountryFilter" style="width:160px; height: 38px; border-radius: 8px; cursor: pointer;">
          <option value="">All Countries</option>
        </select>
        <select class="form-select form-select-sm border-light-subtle" id="portTypeFilter" style="width:130px; height: 38px; border-radius: 8px; cursor: pointer;">
          <option value="">All Types</option>
          <option value="Major Port">Major Port</option>
          <option value="Canal">Canal</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div>
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1.5" onclick="exportPortsTable()" style="border-radius: 8px; height: 38px; font-size: 0.82rem; padding: 0 16px; border-color: #dee2e6;"><i class="bi bi-filetype-csv" style="font-size: 0.95rem;"></i>CSV Export</button>
      </div>
    </div>

    <!-- Table -->
    <div class="table-responsive" style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
      <table id="portsTable" class="table w-100 mb-0">
        <thead>
          <tr>
            <th class="col-align-left">Port Name</th>
            <th class="col-align-left">Country</th>
            <th class="col-align-right" style="width: 100px;">Latitude</th>
            <th class="col-align-right" style="width: 100px;">Longitude</th>
            <th class="col-align-center">Harbor Size</th>
            <th class="col-align-center">Harbor Type</th>
            <th class="col-align-center" style="width: 110px;">WPI Code</th>
            <th class="col-align-center" style="width: 170px;">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</section>`;

// Replace HTML layout safely using callback
normContent = normContent.replace(targetHtmlBlock, () => replacementHtmlBlock);
console.log("Successfully replaced page-ports HTML layout block!");

// 2. Replace initPortsTable() function
const jsStart = 'function initPortsTable() {';
const newsComment = 'NEWS PAGE';

const jsStartIdx = normContent.indexOf(jsStart);
if (jsStartIdx === -1) {
    console.error("Error: Could not find function initPortsTable() start!");
    process.exit(1);
}

const newsIdx = normContent.indexOf(newsComment, jsStartIdx);
if (newsIdx === -1) {
    console.error("Error: Could not find NEWS PAGE comment block after jsStartIdx!");
    process.exit(1);
}

const jsEndIdx = normContent.lastIndexOf('/*', newsIdx);
if (jsEndIdx === -1 || jsEndIdx <= jsStartIdx) {
    console.error("Error: Could not find end of initPortsTable() before NEWS PAGE!");
    process.exit(1);
}

const targetJsBlock = normContent.substring(jsStartIdx, jsEndIdx);

const replacementJsBlock = `window.exportPortsTable = function() {
  exportTable('portsTable', 'ports');
};

window.zoomToPort = function(lat, lng) {
  showPage('map');
  if (STATE.maps.main) {
    STATE.maps.main.setView([lat, lng], 8);
  }
};

window.showPortWeather = function(lat, lng, portName) {
  showPage('weather');
  Swal.fire({
    title: 'Loading Weather...',
    html: 'Fetching live coordinates forecast for <b>' + portName + '</b>...',
    allowOutsideClick: false,
    didOpen: () => { Swal.showLoading(); }
  });
  fetch(\`https://api.open-meteo.com/v1/forecast?latitude=\${lat}&longitude=\${lng}&current_weather=true\`)
    .then(r => r.json())
    .then(data => {
      const w = data.current_weather;
      Swal.fire({
        title: \`Weather: \${portName}\`,
        icon: 'success',
        html: \`
          <div class="text-start">
            <p class="mb-1"><b>Latitude:</b> \${lat.toFixed(4)}</p>
            <p class="mb-1"><b>Longitude:</b> \${lng.toFixed(4)}</p>
            <p class="mb-1"><b>Temperature:</b> \${w.temperature}°C</p>
            <p class="mb-0"><b>Wind Speed:</b> \${w.windspeed} km/h</p>
          </div>
        \`,
        confirmButtonColor: '#0d6efd'
      });
    })
    .catch(() => {
      Swal.fire('Error', 'Failed to retrieve weather data', 'error');
    });
};

window.viewPortDetails = function(name, countryName, lat, lng, size, type, wpi) {
  Swal.fire({
    title: \`🚢 \${name}\`,
    html: \`
      <table class="table table-sm text-start table-borderless mb-0">
        <tr><th style="width:120px;">Country</th><td>\${countryName}</td></tr>
        <tr><th>Latitude</th><td>\${lat}</td></tr>
        <tr><th>Longitude</th><td>\${lng}</td></tr>
        <tr><th>Harbor Size</th><td>\${size}</td></tr>
        <tr><th>Harbor Type</th><td>\${type}</td></tr>
        <tr><th>WPI Code</th><td>\${wpi}</td></tr>
      </table>
    \`,
    confirmButtonText: 'Close',
    confirmButtonColor: '#6c757d'
  });
};

function initPortsTable() {
  if (!STATE.ports.length) { setTimeout(initPortsTable, 500); return; }
  if (portsDtInited) return;

  const tbody = document.querySelector('#portsTable tbody');
  tbody.innerHTML = STATE.ports.map(p => {
    const portName = p.name || 'Unnamed Port';
    
    // Country fallback
    const countryName = p.country ? p.country.name : 'Unknown';
    const countryCode = p.country ? p.country.code : '';
    const countryFlagHtml = countryCode 
      ? \`<img src="https://flagcdn.com/w20/\${countryCode.toLowerCase()}.png" class="me-1.5" width="16" style="border-radius:2px;" alt="">\${countryName}\` 
      : \`Unknown\`;
      
    // Lat / Lng
    const lat = p.latitude !== null ? parseFloat(p.latitude) : 0;
    const lng = p.longitude !== null ? parseFloat(p.longitude) : 0;
    
    // Size badge
    const size = p.harbor_size || 'Medium';
    let sizeBadgeClass = 'medium';
    if (size.toLowerCase() === 'small') sizeBadgeClass = 'small';
    else if (size.toLowerCase() === 'large') sizeBadgeClass = 'large';
    else if (size.toLowerCase() === 'very large') sizeBadgeClass = 'very-large';
    const sizeHtml = \`<span class="badge-size \${sizeBadgeClass}">\${size}</span>\`;
    
    // Type badge
    let rawType = p.harbor_type || 'Commercial Port';
    let typeClass = 'commercial';
    let displayType = rawType;
    
    if (rawType.toLowerCase().includes('industrial')) { typeClass = 'industrial'; displayType = 'Industrial'; }
    else if (rawType.toLowerCase().includes('fishing')) { typeClass = 'fishing'; displayType = 'Fishing'; }
    else if (rawType.toLowerCase().includes('military')) { typeClass = 'military'; displayType = 'Military'; }
    else if (rawType.toLowerCase().includes('container')) { typeClass = 'container'; displayType = 'Container'; }
    else if (rawType.toLowerCase().includes('oil') || rawType.toLowerCase().includes('terminal')) { typeClass = 'oil-terminal'; displayType = 'Oil Terminal'; }
    else if (rawType.toLowerCase().includes('commercial')) { typeClass = 'commercial'; displayType = 'Commercial'; }
    
    const typeHtml = \`<span class="badge-type \${typeClass}">\${displayType}</span>\`;
    
    // WPI Code
    const wpi = p.wpi_code || 'N/A';
    const wpiHtml = wpi !== 'N/A' ? \`<code style="font-size:.75rem;">\${wpi}</code>\` : \`<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:0.65rem; padding:3px 6px;">N/A</span>\`;

    // Action buttons
    const viewTitle = 'View details';
    const mapTitle = 'Zoom to Map';
    const weatherTitle = 'Get Weather';
    const profileTitle = 'Country Profile';
    
    const actionHtml = \`
      <div class="d-flex justify-content-center gap-1">
        <button class="btn-action-sq-port text-white" onclick="event.stopPropagation(); viewPortDetails('\${portName.replace(/'/g, "\\\\'")}', '\${countryName.replace(/'/g, "\\\\'")}', \${lat}, \${lng}, '\${size}', '\${displayType}', '\${wpi}')" title="\${viewTitle}" data-bs-toggle="tooltip" style="background-color: #0d6efd;">
          <i class="bi bi-eye"></i>
        </button>
        <button class="btn-action-sq-port text-white" onclick="event.stopPropagation(); zoomToPort(\${lat}, \${lng})" title="\${mapTitle}" data-bs-toggle="tooltip" style="background-color: #198754;">
          <i class="bi bi-geo-alt"></i>
        </button>
        <button class="btn-action-sq-port text-dark" onclick="event.stopPropagation(); showPortWeather(\${lat}, \${lng}, '\${portName.replace(/'/g, "\\\\'")}')" title="\${weatherTitle}" data-bs-toggle="tooltip" style="background-color: #ffc107;">
          <i class="bi bi-cloud-sun"></i>
        </button>
        <button class="btn-action-sq-port text-white" \${p.country_id ? \`onclick="event.stopPropagation(); viewCountry(\${p.country_id})"\` : 'disabled'} title="\${profileTitle}" data-bs-toggle="tooltip" style="background-color: #6c757d;">
          <i class="bi bi-globe"></i>
        </button>
      </div>
    \`;

    return \`
      <tr>
        <td class="col-align-left"><strong>\${portName}</strong></td>
        <td class="col-align-left">\${countryFlagHtml}</td>
        <td class="col-align-right" style="font-family:var(--font-mono);font-size:.8rem;">\${lat.toFixed(4)}</td>
        <td class="col-align-right" style="font-family:var(--font-mono);font-size:.8rem;">\${lng.toFixed(4)}</td>
        <td class="col-align-center">\${sizeHtml}</td>
        <td class="col-align-center">\text-center">\text-center">\${typeHtml}</td>
        <td class="col-align-center">\${wpiHtml}</td>
        <td class="col-align-center">\${actionHtml}</td>
      </tr>
    \`;
  }).join('');

  STATE.dt.ports = new DataTable('#portsTable', {
    responsive:true, pageLength:25, lengthMenu:[10,25,50,100,-1],
    columnDefs:[{orderable:false,targets:[7]}],
    language:{ search:'', searchPlaceholder:'Filter ports...', lengthMenu:'Show _MENU_' }
  });
  
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  portsDtInited = true;
}
`;

normContent = normContent.replace(targetJsBlock, () => replacementJsBlock);
console.log("Successfully replaced function initPortsTable()!");

// Convert line endings back to CRLF
const finalContent = normContent.replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php with Port redesign!");
