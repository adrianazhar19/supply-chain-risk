const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Replace the tbody of countriesTable with the Blade foreach loop
const tbodyTarget = '<table id="countriesTable" class="table w-100">\r\n        <thead>\r\n          <tr>\r\n            <th>Flag</th><th>Country</th><th>Code</th><th>Region</th>\r\n            <th>Currency</th><th>GDP (B USD)</th><th>Population (M)</th>\r\n            <th>Inflation</th><th>Risk Score</th><th>Status</th><th>Action</th>\r\n          </tr>\r\n        </thead>\r\n        <tbody></tbody>\r\n      </table>';

const tbodyTargetLF = `<table id="countriesTable" class="table w-100">
        <thead>
          <tr>
            <th>Flag</th><th>Country</th><th>Code</th><th>Region</th>
            <th>Currency</th><th>GDP (B USD)</th><th>Population (M)</th>
            <th>Inflation</th><th>Risk Score</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>`;

const tbodyReplacement = `<table id="countriesTable" class="table w-100">
        <thead>
          <tr>
            <th>Flag</th><th>Country</th><th>Code</th><th>Region</th>
            <th>Currency</th><th>GDP (B USD)</th><th>Population (M)</th>
            <th>Inflation</th><th>Risk Score</th><th>Status</th><th>Action</th>
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
                <td>
                  <img src="https://flagcdn.com/w40/{{ strtolower($country->code) }}.png" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt="">
                </td>
                <td><strong>{{ $country->name }}</strong></td>
                <td><code style="font-size:.75rem;">{{ $country->code }}</code></td>
                <td style="color:var(--text-secondary);">{{ $country->region ?? '-' }}</td>
                <td>{{ $country->currency ?? '-' }}</td>
                <td>{{ $gdp ? '$'.number_format($gdp/1e9, 1).'B' : '-' }}</td>
                <td>{{ $population ? number_format($population/1e6, 1).'M' : '-' }}</td>
                <td class="{{ $inflation > 5 ? 'text-danger' : '' }}">{{ $inflation ? number_format($inflation, 2).'%' : '-' }}</td>
                <td><span style="font-family:var(--font-mono);font-weight:700;">{{ $riskScore ? number_format($riskScore, 1) : '-' }}</span></td>
                <td><span class="risk-pill {{ strtolower($riskLevel) }}">{{ $riskLevel }}</span></td>
                <td>
                  <button class="btn-outline-brand" onclick="event.stopPropagation();viewCountry({{ $country->id }})" style="padding:4px 10px;font-size:.72rem;">Profile</button>
                </td>
              </tr>
            @endforeach
          @else
            <tr>
              <td colspan="11" class="text-center py-4 text-muted">No country data available</td>
            </tr>
          @endif
        </tbody>
      </table>`;

if (content.includes(tbodyTarget)) {
    content = content.replace(tbodyTarget, tbodyReplacement);
    console.log("Replaced countries table tbody using CRLF!");
} else if (content.includes(tbodyTargetLF)) {
    content = content.replace(tbodyTargetLF, tbodyReplacement);
    console.log("Replaced countries table tbody using LF!");
} else {
    // Try simple regex replacement
    const regex = /<table id="countriesTable" class="table w-100">[\s\S]*?<\/table>/;
    if (regex.test(content)) {
        content = content.replace(regex, tbodyReplacement);
        console.log("Replaced countries table tbody using Regex!");
    } else {
        console.error("Error: Could not find countriesTable block!");
    }
}

// 2. Replace initCountriesTable() implementation in JS
const jsStart = 'function initCountriesTable() {';
const jsEnd = '}\r\n\r\n/* ═══════════════════════════════════════════════════════════\r\n   PORTS TABLE';
const jsEndLF = '}\n\n/* ═══════════════════════════════════════════════════════════\n   PORTS TABLE';

const startIdx = content.indexOf(jsStart);
if (startIdx !== -1) {
    let endIdx = content.indexOf(jsEnd, startIdx);
    if (endIdx === -1) {
        endIdx = content.indexOf(jsEndLF, startIdx);
    }
    
    if (endIdx !== -1) {
        const targetJsBlock = content.substring(startIdx, endIdx + 1);

        const replacementJsBlock = `function initCountriesTable() {
  if (countriesDtInited) return;

  const tbody = document.querySelector('#countriesTable tbody');
  if (!tbody) return;

  if (!STATE.countries || !STATE.countries.length) {
    // If API returns empty and Blade has no records, show empty state message
    if (tbody.children.length <= 1 && tbody.innerHTML.includes('No country data available')) {
      tbody.innerHTML = '<tr><td colspan="11" class="text-center py-4 text-muted">No country data available</td></tr>';
    }
    return;
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
      const riskPillHtml = \`<span class="risk-pill \${riskLevel.toLowerCase()}">\${riskLevel}</span>\`;
      
      return \`<tr onclick="viewCountry(\${c.id})" style="cursor:pointer;">
        <td><img src="\${flagUrl}" width="28" style="border-radius:4px;" onerror="this.style.display='none'" alt=""></td>
        <td><strong>\${name}</strong></td>
        <td><code style="font-size:.75rem;">\${code}</code></td>
        <td style="color:var(--text-secondary);">\${region}</td>
        <td>\${currency}</td>
        <td>\${gdpStr}</td>
        <td>\${popStr}</td>
        <td class="\${inflClass}">\${inflStr}</td>
        <td><span style="font-family:var(--font-mono);font-weight:700;">\${riskScoreStr}</span></td>
        <td>\${riskPillHtml}</td>
        <td>
          <button class="btn-outline-brand" onclick="event.stopPropagation();viewCountry(\${c.id})" style="padding:4px 10px;font-size:.72rem;">Profile</button>
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
    const tableEl = document.getElementById('countriesTable');
    if (!tableEl) {
      throw new Error("HTML table element #countriesTable not found.");
    }
    
    // Destroy previous instance if it exists to avoid reinitialization errors
    if (STATE.dt.countries) {
      try { STATE.dt.countries.destroy(); } catch(e){}
    }

    STATE.dt.countries = new DataTable('#countriesTable', {
      responsive:true, pageLength:25, lengthMenu:[10,25,50,100],
      columnDefs:[{orderable:false,targets:[0,10]}],
      language:{ search:'', searchPlaceholder:'Filter countries...', lengthMenu:'Show _MENU_' },
      dom: 'lBfrtip',
      buttons: [
        { extend: 'csv', text: '<i class="bi bi-filetype-csv me-1"></i>CSV', className: 'dt-button' },
        { extend: 'excel', text: '<i class="bi bi-file-earmark-excel me-1"></i>Excel', className: 'dt-button' },
        { extend: 'pdf', text: '<i class="bi bi-file-earmark-pdf me-1"></i>PDF', className: 'dt-button' },
        { extend: 'print', text: '<i class="bi bi-printer me-1"></i>Print', className: 'dt-button' }
      ]
    });
    countriesDtInited = true;
  } catch (dtError) {
    console.error("DataTable initialization failed for countriesTable. Showing normal HTML table instead.", dtError);
    countriesDtInited = true;
  }
}`;

        content = content.replace(targetJsBlock, () => replacementJsBlock);
        console.log("Successfully replaced function initCountriesTable()!");
    } else {
        console.error("Error: Could not find end of function initCountriesTable()!");
    }
} else {
    console.error("Error: Could not find start of function initCountriesTable()!");
}

// Convert line endings back to CRLF (since workspace is Windows)
const finalContent = content.replace(/\r\n/g, '\n').replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php!");
