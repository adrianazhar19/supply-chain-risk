const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

const targetPopup = `function buildRiskPopup(r) {
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

const replacementPopup = `function buildRiskPopup(r) {
  const color = riskColor(r.risk_level);
  const scoreText = r.total_score !== null ? parseFloat(r.total_score).toFixed(1) : 'N/A';
  const levelText = r.risk_level || 'UNKNOWN';
  const latText = r.country.latitude !== null ? parseFloat(r.country.latitude).toFixed(4) : 'N/A';
  const lonText = r.country.longitude !== null ? parseFloat(r.country.longitude).toFixed(4) : 'N/A';

  return \`
    <div style="font-family:var(--font-sans,sans-serif);min-width:220px;">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
        <img src="https://flagcdn.com/w30/\${r.country.code.toLowerCase()}.png" style="border-radius:3px;">
        <div>
          <div style="font-weight:700;font-size:.9rem;">\${r.country.name}</div>
          <span style="background:\${color};color:#fff;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700;">\${levelText.toUpperCase()}</span>
        </div>
      </div>
      <div style="font-size:.75rem;line-height:1.8;">
        <div>🎯 <b>Country Name:</b> \${r.country.name}</div>
        <div>🎯 <b>Risk Score:</b> \${scoreText}</div>
        <div>⚠️ <b>Risk Level:</b> \${levelText}</div>
        <div>🌐 <b>Latitude:</b> \${latText}</div>
        <div>🌐 <b>Longitude:</b> \${lonText}</div>
      </div>
      <button onclick="viewCountry(\${r.country_id})"
        style="margin-top:10px;width:100%;background:#2563eb;color:#fff;border:none;padding:6px;border-radius:6px;font-size:.75rem;font-weight:600;cursor:pointer;">
        View Full Profile
      </button>
    </div>
  \`;
}`;

const cleanTarget = targetPopup.replace(/\r?\n/g, '\n');
const cleanReplacement = replacementPopup.replace(/\r?\n/g, '\n');
const cleanContent = content.replace(/\r?\n/g, '\n');

if (cleanContent.includes(cleanTarget)) {
    content = cleanContent.replace(cleanTarget, cleanReplacement);
    console.log("Successfully replaced popup content in buildRiskPopup!");
} else {
    console.log("Could not find cleanTarget in content!");
}

fs.writeFileSync(path, content, 'utf8');
console.log("Saved updated dashboard.blade.php!");
