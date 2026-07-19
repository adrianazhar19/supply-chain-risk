const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

const targetWeatherScript = `async function loadWeatherPage() {
  const grid = document.getElementById('weatherGrid');
  grid.innerHTML = Array(8).fill(0).map(() =>
    \`<div class="col-sm-6 col-lg-4 col-xl-3"><div class="card skeleton" style="height:280px;"></div></div>\`
  ).join('');

  try {
    const res = await fetchJSON('/api/weather');
    if (!res.status) throw new Error();
    STATE.weather = res.data;
    grid.innerHTML = '';

    res.data.forEach(item => {
      const c = item.country;
      const w = item.weather;
      const stormBg = w.storm_risk > 60 ? '#ef444420' : w.storm_risk > 30 ? '#f59e0b20' : '#10b98120';
      const stormColor = w.storm_risk > 60 ? '#ef4444' : w.storm_risk > 30 ? '#f59e0b' : '#10b981';

      grid.innerHTML += \`
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="card p-3 weather-card-grid h-100" onclick="viewCountry(\${c.id})" style="cursor:pointer;">
            <div class="d-flex align-items-center gap-2 mb-3">
              <img src="\${c.flag_url}" width="24" style="border-radius:3px;" alt="">
              <span style="font-weight:700;font-size:.88rem;">\${c.name}</span>
              <span style="font-size:1.5rem;margin-left:auto;"><i class="bi \${w.icon||'bi-cloud'}"></i></span>
            </div>

            <div class="d-flex align-items-end gap-2 mb-3">
              <span style="font-size:2.2rem;font-weight:800;font-family:var(--font-display);color:var(--text-primary);">\${w.temperature?.toFixed(1)||'–'}°C</span>
              <span style="font-size:.78rem;color:var(--text-muted);padding-bottom:4px;">\${w.description||'–'}</span>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Humidity</span><span class="weather-stat-value">\${w.humidity?.toFixed(0)||'–'}%</span></div></div>
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Wind</span><span class="weather-stat-value">\${w.wind_speed?.toFixed(1)||'–'} km/h</span></div></div>
              <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Rainfall</span><span class="weather-stat-value">\${w.rainfall?.toFixed(1)||'–'} mm</span></div></div>
              <div class="col-6">
                <div class="weather-stat">
                  <span class="weather-stat-label">Storm Risk</span>
                  <span class="weather-stat-value" style="color:\${stormColor};">\${w.storm_risk}%</span>
                </div>
              </div>
            </div>

            <div class="stat-progress">
              <div class="stat-progress-bar" style="width:\${w.storm_risk}%;background:\${stormColor};"></div>
            </div>

            \${w.forecast?.length ? \`
              <div class="d-flex gap-1 mt-3 overflow-hidden">
                \${w.forecast.slice(0,5).map(f=>\`
                  <div class="forecast-item flex-fill" style="padding:6px 4px;">
                    <div class="forecast-day">\${new Date(f.date).toLocaleDateString(undefined,{weekday:'short'})}</div>
                    <div class="forecast-icon" style="font-size:1rem;"><i class="bi \${f.icon||'bi-cloud'}"></i></div>
                    <div class="forecast-temp" style="font-size:.68rem;">\${f.temp_max?.toFixed(0)}°</div>
                  </div>
                \`).join('')}
              </div>
            \` : ''}
          </div>
        </div>
      \`;
    });

    if (!res.data.length) {
      grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--text-muted);">No weather data available. Countries need lat/lon coordinates.</div>';
    }
  } catch(e) {
    grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Weather data could not be retrieved.</div></div>';
  }
}`;

const replacementWeatherScript = `let weatherMap = null;
let weatherDistChart = null;

function getWeatherIndicator(w) {
  const desc = (w.description || '').toLowerCase();
  const temp = parseFloat(w.temperature) || 0;
  const wind = parseFloat(w.wind_speed) || 0;
  const rain = parseFloat(w.rainfall) || 0;

  if (temp > 40 || temp < -12 || wind > 75 || rain > 50) {
      return { label: 'Extreme', color: '#7c3aed', pulseClass: 'weather-pulse-purple' };
  }
  if (w.storm_risk > 50 || desc.includes('storm') || wind > 50) {
      return { label: 'Storm', color: '#ef4444', pulseClass: 'weather-pulse-red' };
  }
  if (rain > 0.5 || desc.includes('rain') || desc.includes('drizzle')) {
      return { label: 'Rain', color: '#3b82f6', pulseClass: 'weather-pulse-blue' };
  }
  if (desc.includes('cloud') || desc.includes('overcast')) {
      return { label: 'Cloudy', color: '#f97316', pulseClass: 'weather-pulse-orange' };
  }
  return { label: 'Clear', color: '#10b981', pulseClass: 'weather-pulse-green' };
}

async function loadWeatherPage(silent = false) {
  const grid = document.getElementById('weatherGrid');
  if (!silent) {
    grid.innerHTML = Array(6).fill(0).map(() =>
      \`<div class="col-md-6"><div class="card skeleton" style="height:360px;"></div></div>\`
    ).join('');
  }

  try {
    const res = await fetchJSON('/api/weather');
    if (!res.status) throw new Error();
    STATE.weather = res.data;

    // 1. Calculate & Update Metrics
    updateWeatherMetrics(res.data);

    // 2. Severe Alerts panel
    updateWeatherAlerts(res.data);

    // 3. Render Leaflet Weather Map
    updateWeatherMap(res.data);

    // 4. Render Weather Distribution Chart
    updateWeatherDistribution(res.data);

    // 5. Render Grid Cards
    filterWeather();

    // 6. Setup Search & Filters (once)
    setupWeatherFilters();

    // 7. Setup Auto Refresh (once)
    setupWeatherAutoRefresh();

  } catch(e) {
    console.error('Weather page load failed:', e);
    grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Weather telemetry could not be retrieved.</div></div>';
  }
}

function updateWeatherMetrics(data) {
    if (!data.length) return;
    const temps = data.map(item => item.weather.temperature).filter(t => t !== null);
    const humidities = data.map(item => item.weather.humidity).filter(h => h !== null);
    const winds = data.map(item => item.weather.wind_speed).filter(w => w !== null);

    const count = data.length;
    const avgTemp = temps.reduce((a, b) => a + b, 0) / (temps.length || 1);
    const avgHumid = humidities.reduce((a, b) => a + b, 0) / (humidities.length || 1);
    const avgWind = winds.reduce((a, b) => a + b, 0) / (winds.length || 1);
    const maxTemp = Math.max(...temps);
    const minTemp = Math.min(...temps);

    document.getElementById('stat-monitored-countries').textContent = count;
    document.getElementById('stat-avg-temp').textContent = avgTemp.toFixed(1) + '°C';
    document.getElementById('stat-avg-humidity').textContent = avgHumid.toFixed(0) + '%';
    document.getElementById('stat-avg-wind').textContent = avgWind.toFixed(1) + ' km/h';
    document.getElementById('stat-highest-temp').textContent = maxTemp.toFixed(1) + '°C';
    document.getElementById('stat-lowest-temp').textContent = minTemp.toFixed(1) + '°C';
}

function updateWeatherAlerts(data) {
    const alertsPanel = document.getElementById('weatherAlertsPanel');
    alertsPanel.innerHTML = '';
    
    let activeAlerts = 0;
    data.forEach(item => {
        const c = item.country;
        const w = item.weather;
        const desc = (w.description || '').toLowerCase();
        const temp = parseFloat(w.temperature) || 0;
        const wind = parseFloat(w.wind_speed) || 0;
        const rain = parseFloat(w.rainfall) || 0;

        let alertType = '';
        if (w.storm_risk > 50 || desc.includes('storm')) alertType = 'Storm Risk';
        else if (temp > 38) alertType = 'Extreme Heat';
        else if (wind > 50) alertType = 'Strong Wind';
        else if (rain > 30) alertType = 'Heavy Rain';
        else if (desc.includes('snow') || temp < 0) alertType = 'Freeze/Snow';

        if (alertType) {
            activeAlerts++;
            alertsPanel.innerHTML += \`
                <div class="weather-alert-row">
                    <span class="weather-pulse-dot weather-pulse-red"></span>
                    <div style="flex:1;">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong style="font-size:0.8rem;color:var(--text-primary);">\${c.name}</strong>
                            <span class="weather-alert-badge">\${alertType}</span>
                        </div>
                        <div style="font-size:0.68rem;color:var(--text-muted);margin-top:2px;">
                            Temp: \${w.temperature.toFixed(1)}°C | Wind: \${w.wind_speed.toFixed(0)} km/h | \${w.description}
                        </div>
                    </div>
                </div>
            \`;
        }
    });

    document.getElementById('weatherAlertsCount').textContent = \`\${activeAlerts} Active\`;
    if (activeAlerts === 0) {
        alertsPanel.innerHTML = \`
            <div class="text-center py-4 text-muted" style="font-size:0.75rem;">
                No severe weather alerts active in monitored regions.
            </div>
        \`;
    }
}

function updateWeatherMap(data) {
    const container = document.getElementById('weatherIntelMap');
    if (!container) return;

    if (!weatherMap) {
        weatherMap = L.map('weatherIntelMap', { center: [15, 10], zoom: 2 });
        const theme = STATE.theme;
        L.tileLayer(
            theme === 'dark'
                ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png',
            { attribution: '© CartoDB © OpenStreetMap contributors', maxZoom: 18 }
        ).addTo(weatherMap);
    } else {
        weatherMap.eachLayer(l => { if (!(l instanceof L.TileLayer)) weatherMap.removeLayer(l); });
    }

    const bounds = [];
    data.forEach(item => {
        const c = item.country;
        const w = item.weather;
        if (!c.latitude || !c.longitude) return;
        const lat = parseFloat(c.latitude);
        const lon = parseFloat(c.longitude);
        if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

        const indicator = getWeatherIndicator(w);
        const circle = L.circleMarker([lat, lon], {
            radius: 8 + (parseFloat(w.temperature) || 0) / 10,
            fillColor: indicator.color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.65
        });

        const popupContent = \`
            <div style="font-family:var(--font-sans,sans-serif);min-width:180px;padding:4px;">
                <div style="font-weight:700;font-size:0.85rem;margin-bottom:6px;display:flex;align-items:center;gap:6px;">
                    <img src="\${c.flag_url}" style="border-radius:2px;width:20px;height:14px;object-fit:cover;">
                    <span>\${c.name}</span>
                </div>
                <hr style="margin:6px 0;">
                <div style="font-size:0.75rem;line-height:1.7;">
                    <div>🌡️ <b>Temperature:</b> \${w.temperature.toFixed(1)}°C</div>
                    <div>🌦️ <b>Condition:</b> \${w.description || '–'}</div>
                    <div>💧 <b>Humidity:</b> \${w.humidity.toFixed(0)}%</div>
                    <div>💨 <b>Wind Speed:</b> \${w.wind_speed.toFixed(1)} km/h</div>
                    <div>🌧️ <b>Rainfall:</b> \${w.rainfall.toFixed(1)} mm</div>
                </div>
            </div>
        \`;

        circle.bindPopup(popupContent, { maxWidth: 280 });
        circle.bindTooltip(c.name, { permanent: false, direction: 'top' });
        circle.addTo(weatherMap);
        bounds.push([lat, lon]);
    });

    if (bounds.length > 0) {
        weatherMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 5 });
    }
}

function updateWeatherDistribution(data) {
    const categories = { Clear: 0, Cloudy: 0, Rain: 0, Storm: 0, Extreme: 0 };
    data.forEach(item => {
        const ind = getWeatherIndicator(item.weather);
        categories[ind.label]++;
    });

    if (weatherDistChart) weatherDistChart.destroy();
    
    const ctx = document.getElementById('weatherDistributionChart');
    if (!ctx) return;

    weatherDistChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(categories),
            datasets: [{
                data: Object.values(categories),
                backgroundColor: ['#10b981', '#f97316', '#3b82f6', '#ef4444', '#7c3aed'],
                borderWidth: 2,
                borderColor: STATE.theme === 'dark' ? '#0a0f1e' : '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 10, font: { size: 10 } }
                }
            },
            cutout: '60%'
        }
    });
}

function filterWeather() {
    const query = document.getElementById('weatherSearch').value.toLowerCase().trim();
    const region = document.getElementById('weatherFilterRegion').value;
    const tempFilter = document.getElementById('weatherFilterTemp').value;
    const typeFilter = document.getElementById('weatherFilterType').value;

    const filtered = STATE.weather.filter(item => {
        const c = item.country;
        const w = item.weather;

        // 1. Text Search
        if (query) {
            const matchesText = c.name.toLowerCase().includes(query) ||
                                (c.region || '').toLowerCase().includes(query) ||
                                (w.description || '').toLowerCase().includes(query);
            if (!matchesText) return false;
        }

        // 2. Region Filter
        if (region && c.region !== region) return false;

        // 3. Temp Filter
        if (tempFilter) {
            const t = w.temperature;
            if (tempFilter === 'hot' && t <= 30) return false;
            if (tempFilter === 'warm' && (t < 15 || t > 30)) return false;
            if (tempFilter === 'cold' && t >= 15) return false;
        }

        // 4. Type/Condition Filter
        if (typeFilter) {
            const ind = getWeatherIndicator(w);
            if (ind.label !== typeFilter) return false;
        }

        return true;
    });

    drawWeatherGrid(filtered);
}

function drawWeatherGrid(data) {
    const grid = document.getElementById('weatherGrid');
    grid.innerHTML = '';

    if (!data.length) {
        grid.innerHTML = '<div class="col-12 text-center py-5" style="color:var(--text-muted);">No matching weather data found.</div>';
        return;
    }

    data.forEach(item => {
        const c = item.country;
        const w = item.weather;
        const ind = getWeatherIndicator(w);
        const stormColor = w.storm_risk > 60 ? '#ef4444' : w.storm_risk > 30 ? '#f59e0b' : '#10b981';

        grid.innerHTML += \`
            <div class="col-md-6">
                <div class="card p-3 weather-card-grid h-100" onclick="viewCountry(\${c.id})" style="cursor:pointer;position:relative;border-left: 4px solid \${ind.color}!important;">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="\${c.flag_url}" width="24" style="border-radius:3px;" alt="">
                        <span style="font-weight:700;font-size:.88rem;">\${c.name}</span>
                        <span style="font-size:0.65rem;font-weight:700;background:\&amp;{ind.color}15;color:\${ind.color};padding:2px 8px;border-radius:12px;margin-left:8px;">\${ind.label.toUpperCase()}</span>
                        <span class="animated-weather-icon" style="font-size:1.5rem;margin-left:auto;color:\${ind.color};"><i class="bi \${w.icon||'bi-cloud'}"></i></span>
                    </div>

                    <div class="d-flex align-items-end gap-2 mb-3">
                        <span style="font-size:2.2rem;font-weight:800;font-family:var(--font-display);color:var(--text-primary);">\${w.temperature?.toFixed(1)||'–'}°C</span>
                        <span style="font-size:.78rem;color:var(--text-muted);padding-bottom:4px;">\${w.description||'–'}</span>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Humidity</span><span class="weather-stat-value">\${w.humidity?.toFixed(0)||'–'}%</span></div></div>
                        <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Wind</span><span class="weather-stat-value">\${w.wind_speed?.toFixed(1)||'–'} km/h</span></div></div>
                        <div class="col-6"><div class="weather-stat"><span class="weather-stat-label">Rainfall</span><span class="weather-stat-value">\${w.rainfall?.toFixed(1)||'–'} mm</span></div></div>
                        <div class="col-6">
                            <div class="weather-stat">
                                <span class="weather-stat-label">Storm Risk</span>
                                <span class="weather-stat-value" style="color:\${stormColor};">\${w.storm_risk}%</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-progress mb-3">
                        <div class="stat-progress-bar" style="width:\&amp;{w.storm_risk}%;background:\${stormColor};"></div>
                    </div>

                    \${w.forecast?.length ? \`
                        <div style="font-size:0.68rem;font-weight:600;color:var(--text-muted);margin-bottom:4px;">7-Day Max Temperature Forecast Trend:</div>
                        <div class="forecast-chart-container" style="height:65px;position:relative;width:100%;">
                            <canvas id="weather-forecast-chart-\${c.id}"></canvas>
                        </div>
                    \` : ''}
                </div>
            </div>
        \`;
    });

    data.forEach(item => {
        const c = item.country;
        const w = item.weather;
        const ind = getWeatherIndicator(w);
        
        const canvas = document.getElementById(\`weather-forecast-chart-\${c.id}\`);
        if (canvas && w.forecast?.length) {
            const forecastLabels = w.forecast.slice(0, 7).map(f => new Date(f.date).toLocaleDateString(undefined, {weekday:'short'}));
            const forecastTemps = w.forecast.slice(0, 7).map(f => f.temp_max);

            new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: forecastLabels,
                    datasets: [{
                        data: forecastTemps,
                        borderColor: ind.color,
                        backgroundColor: ind.color + '10',
                        fill: true,
                        tension: 0.3,
                        borderWidth: 1.5,
                        pointRadius: 2,
                        pointBackgroundColor: ind.color,
                        pointHoverRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: true } },
                    scales: {
                        x: { display: true, ticks: { font: { size: 8 }, color: 'var(--text-muted)' }, grid: { display: false } },
                        y: { display: false }
                    }
                }
            });
        }
    });
}

let weatherFiltersRegistered = false;
function setupWeatherFilters() {
    if (weatherFiltersRegistered) return;
    
    document.getElementById('weatherSearch').addEventListener('input', filterWeather);
    document.getElementById('weatherFilterRegion').addEventListener('change', filterWeather);
    document.getElementById('weatherFilterTemp').addEventListener('change', filterWeather);
    document.getElementById('weatherFilterType').addEventListener('change', filterWeather);
    document.getElementById('weatherFilterReset').addEventListener('click', () => {
        document.getElementById('weatherSearch').value = '';
        document.getElementById('weatherFilterRegion').value = '';
        document.getElementById('weatherFilterTemp').value = '';
        document.getElementById('weatherFilterType').value = '';
        filterWeather();
    });

    weatherFiltersRegistered = true;
}

let weatherAutoRefreshInterval = null;
function setupWeatherAutoRefresh() {
    if (weatherAutoRefreshInterval) return;

    weatherAutoRefreshInterval = setInterval(() => {
        if (STATE.currentPage === 'weather') {
            loadWeatherPage(true);
        }
    }, 5 * 60 * 1000);
}`;

// Normalise newlines for string checks
const cleanContent2 = content.replace(/\r?\n/g, '\n');
const cleanTargetScript = targetWeatherScript.replace(/\r?\n/g, '\n');
const cleanReplacementScript = replacementWeatherScript.replace(/\r?\n/g, '\n');

let updatedContent2 = cleanContent2;

if (updatedContent2.includes(cleanTargetScript)) {
    updatedContent2 = updatedContent2.replace(cleanTargetScript, cleanReplacementScript);
    console.log("Successfully replaced Weather javascript functions!");
} else {
    console.log("Could not find cleanTargetScript in content!");
}

// Clean up some escape entity replacements (like \&amp;{ which node script could write in JS literals)
updatedContent2 = updatedContent2.replace(/\\&amp;{/g, '\\${');

fs.writeFileSync(path, updatedContent2, 'utf8');
console.log("Saved updated dashboard.blade.php!");
