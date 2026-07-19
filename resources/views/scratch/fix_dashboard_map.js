const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Replace the populateMaps override
const targetPopulateOverride = `    window.populateMaps = function() {
        const map = STATE.maps.dashboard;
        if (!map) return;
        
        // Clear old layers except TileLayers
        map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });
        
        // Plot risk circles
        STATE.risks.forEach(r => {
          if (!r.country?.latitude || !r.country?.longitude) return;
          const color = riskColor(r.risk_level);
          const score = parseFloat(r.total_score);
          const circle = L.circleMarker([r.country.latitude, r.country.longitude], {
            radius: 7 + score / 9,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.65,
          });
          circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
          map.addLayer(circle);
        });
    };`;

const replacementPopulateOverride = `    window.populateMaps = function() {
        const map = STATE.maps.dashboard;
        if (!map) return;
        
        // Clear old layers except TileLayers
        map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });
        
        const cluster = L.markerClusterGroup({
            showCoverageOnHover: false,
            maxClusterRadius: 50,
            animate: true
        });

        const bounds = [];
        
        // Plot risk circles for all 250 countries
        STATE.risks.forEach(r => {
          if (!r.country?.latitude || !r.country?.longitude) return;
          const color = riskColor(r.risk_level);
          const score = r.total_score !== null ? parseFloat(r.total_score) : 0;
          const circle = L.circleMarker([r.country.latitude, r.country.longitude], {
            radius: 7 + score / 9,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.65,
          });
          circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
          cluster.addLayer(circle);
          bounds.push([r.country.latitude, r.country.longitude]);
        });

        map.addLayer(cluster);

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [30, 30], maxZoom: 6 });
        }

        setTimeout(() => { map.invalidateSize(); }, 200);
    };`;

// Normalise newlines for string checks
const cleanTarget = targetPopulateOverride.replace(/\r?\n/g, '\n');
const cleanReplacement = replacementPopulateOverride.replace(/\r?\n/g, '\n');
const cleanContent = content.replace(/\r?\n/g, '\n');

if (cleanContent.includes(cleanTarget)) {
    content = cleanContent.replace(cleanTarget, cleanReplacement);
    console.log("Successfully replaced populateMaps override!");
} else {
    console.log("Could not find cleanTarget in content!");
}

// 2. Replace buildRiskPopup function
const targetPopupFunc = `function buildRiskPopup(r) {
  const color = riskColor(r.risk_level);
  return \`
    <div style="font-family:var(--font-sans,sans-serif);min-width:220px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <img src="https://flagcdn.com/w30/\${r.country.code.toLowerCase()}.png" style="border-radius:3px;">
        <div>
          <div style="font-weight:700;font-size:.9rem;">\${r.country.name}</div>
          <span style="background:\${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700;">\${r.risk_level.toUpperCase()}</span>
        </div>
      </div>
      <div style="font-size:.75rem;line-height:1.8;">
        <div>🎯 <b>Total Score:</b> \${parseFloat(r.total_score).toFixed(1)}</div>
        <div>🌦️ <b>Weather:</b> \${parseFloat(r.weather_score).toFixed(0)}</div>
        <div>📊 <b>Inflation:</b> \${parseFloat(r.inflation_score).toFixed(0)}</div>
        <div>📰 <b>Political:</b> \${parseFloat(r.political_score).toFixed(0)}</div>
      </div>
      <button onclick="viewCountry(\${r.country_id})"
        style="margin-top:10px;width:100%;background:#2563eb;color:#fff;border:none;padding:6px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;">
        View Full Profile
      </button>
    </div>
  \`;
}`;

const replacementPopupFunc = `function buildRiskPopup(r) {
  const color = riskColor(r.risk_level);
  const scoreText = r.total_score !== null ? parseFloat(r.total_score).toFixed(1) : 'N/A';
  const weatherText = r.weather_score !== null ? parseFloat(r.weather_score).toFixed(0) : 'N/A';
  const inflationText = r.inflation_score !== null ? parseFloat(r.inflation_score).toFixed(0) : 'N/A';
  const politicalText = r.political_score !== null ? parseFloat(r.political_score).toFixed(0) : 'N/A';

  return \`
    <div style="font-family:var(--font-sans,sans-serif);min-width:220px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <img src="https://flagcdn.com/w30/\${r.country.code.toLowerCase()}.png" style="border-radius:3px;">
        <div>
          <div style="font-weight:700;font-size:.9rem;">\${r.country.name}</div>
          <span style="background:\${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700;">\${(r.risk_level || 'UNKNOWN').toUpperCase()}</span>
        </div>
      </div>
      <div style="font-size:.75rem;line-height:1.8;">
        <div>🎯 <b>Total Score:</b> \${scoreText}</div>
        <div>🌦️ <b>Weather:</b> \${weatherText}</div>
        <div>📊 <b>Inflation:</b> \${inflationText}</div>
        <div>📰 <b>Political:</b> \${politicalText}</div>
      </div>
      <button onclick="viewCountry(\${r.country_id})"
        style="margin-top:10px;width:100%;background:#2563eb;color:#fff;border:none;padding:6px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;">
        View Full Profile
      </button>
    </div>
  \`;
}`;

const cleanTargetPopup = targetPopupFunc.replace(/\r?\n/g, '\n');
const cleanReplacementPopup = replacementPopupFunc.replace(/\r?\n/g, '\n');
const currentCleanContent = content.replace(/\r?\n/g, '\n');

if (currentCleanContent.includes(cleanTargetPopup)) {
    content = currentCleanContent.replace(cleanTargetPopup, cleanReplacementPopup);
    console.log("Successfully replaced buildRiskPopup!");
} else {
    console.log("Could not find cleanTargetPopup in content!");
}

// 3. Replace riskColor function
const targetRiskColor = `function riskColor(level) {
  const m = { Low:'#10b981', Medium:'#f59e0b', High:'#ef4444', Critical:'#7c3aed' };
  return m[level] || '#10b981';
}`;

const replacementRiskColor = `function riskColor(level) {
  const m = { 
    Low: '#10b981',      // Green
    Medium: '#f97316',   // Orange
    High: '#ef4444',     // Red
    Critical: '#7c3aed'  // Purple
  };
  return m[level] || '#94a3b8'; // Gray for countries without a score
}`;

const cleanTargetRiskColor = targetRiskColor.replace(/\r?\n/g, '\n');
const cleanReplacementRiskColor = replacementRiskColor.replace(/\r?\n/g, '\n');
const currentCleanContent2 = content.replace(/\r?\n/g, '\n');

if (currentCleanContent2.includes(cleanTargetRiskColor)) {
    content = currentCleanContent2.replace(cleanTargetRiskColor, cleanReplacementRiskColor);
    console.log("Successfully replaced riskColor!");
} else {
    console.log("Could not find cleanTargetRiskColor in content!");
}

fs.writeFileSync(path, content, 'utf8');
console.log("Saved updated dashboard.blade.php!");
