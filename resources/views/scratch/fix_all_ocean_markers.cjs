const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Safely output null in portsData if latitude/longitude are empty
const targetPortsData = `        latitude: {{ $port->latitude }},
        longitude: {{ $port->longitude }},`;

const replacementPortsData = `        latitude: {{ $port->latitude ?? 'null' }},
        longitude: {{ $port->longitude ?? 'null' }},`;

// 2. Add validation inside initMainMap for ports loop
const targetMainPorts = `                if (layerSelect === 'ports' || layerSelect === 'both') {
                    STATE.ports.forEach(p => {
                        if (!p.latitude || !p.longitude) return;`;

const replacementMainPorts = `                if (layerSelect === 'ports' || layerSelect === 'both') {
                    STATE.ports.forEach(p => {
                        if (p.latitude === null || p.longitude === null) return;
                        const lat = parseFloat(p.latitude);
                        const lon = parseFloat(p.longitude);
                        if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;`;

const targetMainPortsLMarker = `                        const m = L.marker([p.latitude, p.longitude], { icon: icon });`;
const replacementMainPortsLMarker = `                        const m = L.marker([lat, lon], { icon: icon });`;

const targetMainPortsVisibleItems = `                        visibleItems.push([p.latitude, p.longitude]);`;
const replacementMainPortsVisibleItems = `                        visibleItems.push([lat, lon]);`;

// 3. Update original populateMaps() to use validator for risks and ports
const targetOriginalPopulate = `function populateMaps() {
  const mapsToFill = [];
  if (STATE.maps.dashboard) mapsToFill.push(STATE.maps.dashboard);
  if (STATE.maps.main)      mapsToFill.push(STATE.maps.main);

  mapsToFill.forEach(map => {
    // Clear old layers
    map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });

    const cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 50 });
    const riskLayer = L.layerGroup();

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
      riskLayer.addLayer(circle);
    });

    // Plot port markers
    const portIcon = L.divIcon({
      html: '<div style="width:10px;height:10px;background:#3b82f6;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>',
      className: '',
      iconSize: [10,10],
      iconAnchor: [5,5],
    });

    STATE.ports.slice(0, 600).forEach(p => {
      if (!p.latitude || !p.longitude) return;
      const m = L.marker([p.latitude, p.longitude], { icon: portIcon });`;

const replacementOriginalPopulate = `function populateMaps() {
  const mapsToFill = [];
  if (STATE.maps.dashboard) mapsToFill.push(STATE.maps.dashboard);
  if (STATE.maps.main)      mapsToFill.push(STATE.maps.main);

  mapsToFill.forEach(map => {
    // Clear old layers
    map.eachLayer(l => { if (!(l instanceof L.TileLayer)) map.removeLayer(l); });

    const cluster = L.markerClusterGroup({ showCoverageOnHover: false, maxClusterRadius: 50 });
    const riskLayer = L.layerGroup();

    // Plot risk circles
    STATE.risks.forEach(r => {
      if (!r.country?.latitude || !r.country?.longitude) return;
      const lat = parseFloat(r.country.latitude);
      const lon = parseFloat(r.country.longitude);
      if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

      const color = riskColor(r.risk_level);
      const score = parseFloat(r.total_score);
      const circle = L.circleMarker([lat, lon], {
        radius: 7 + score / 9,
        fillColor: color,
        color: '#fff',
        weight: 1.5,
        fillOpacity: 0.65,
      });
      circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
      riskLayer.addLayer(circle);
    });

    // Plot port markers
    const portIcon = L.divIcon({
      html: '<div style="width:10px;height:10px;background:#3b82f6;border:2px solid #fff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,.3);"></div>',
      className: '',
      iconSize: [10,10],
      iconAnchor: [5,5],
    });

    STATE.ports.slice(0, 600).forEach(p => {
      if (p.latitude === null || p.longitude === null) return;
      const lat = parseFloat(p.latitude);
      const lon = parseFloat(p.longitude);
      if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;
      const m = L.marker([lat, lon], { icon: portIcon });`;

// 4. Update Country Intelligence (CI) map coordinates loop
const targetCiLoop = `    _countries.forEach(c => {
      if (!c.latitude || !c.longitude) return;
      const level = c.risk_level || 'Low';
      const color = riskColor(level);
      const score = parseFloat(c.risk_score)||0;
      const icon = L.divIcon({
        html: \`<div style="width:\${12+score/10}px;height:\${12+score/10}px;border-radius:50%;background:\${color};border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);opacity:.85;"></div>\`,
        className: '',
        iconSize: [16,16],
        iconAnchor: [8,8],
      });
      const m = L.marker([c.latitude, c.longitude], { icon });`;

const replacementCiLoop = `    _countries.forEach(c => {
      if (c.latitude === null || c.longitude === null) return;
      const lat = parseFloat(c.latitude);
      const lon = parseFloat(c.longitude);
      if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

      const level = c.risk_level || 'Low';
      const color = riskColor(level);
      const score = parseFloat(c.risk_score)||0;
      const icon = L.divIcon({
        html: \`<div style="width:\${12+score/10}px;height:\${12+score/10}px;border-radius:50%;background:\${color};border:2px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,.35);opacity:.85;"></div>\`,
        className: '',
        iconSize: [16,16],
        iconAnchor: [8,8],
      });
      const m = L.marker([lat, lon], { icon });`;

// String checks with normalised newlines
const cleanContent = content.replace(/\r?\n/g, '\n');

const cleanTargetPortsData = targetPortsData.replace(/\r?\n/g, '\n');
const cleanReplacementPortsData = replacementPortsData.replace(/\r?\n/g, '\n');

const cleanTargetMainPorts = targetMainPorts.replace(/\r?\n/g, '\n');
const cleanReplacementMainPorts = replacementMainPorts.replace(/\r?\n/g, '\n');

const cleanTargetMainPortsLMarker = targetMainPortsLMarker.replace(/\r?\n/g, '\n');
const cleanReplacementMainPortsLMarker = replacementMainPortsLMarker.replace(/\r?\n/g, '\n');

const cleanTargetMainPortsVisibleItems = targetMainPortsVisibleItems.replace(/\r?\n/g, '\n');
const cleanReplacementMainPortsVisibleItems = replacementMainPortsVisibleItems.replace(/\r?\n/g, '\n');

const cleanTargetOriginalPopulate = targetOriginalPopulate.replace(/\r?\n/g, '\n');
const cleanReplacementOriginalPopulate = replacementOriginalPopulate.replace(/\r?\n/g, '\n');

const cleanTargetCiLoop = targetCiLoop.replace(/\r?\n/g, '\n');
const cleanReplacementCiLoop = replacementCiLoop.replace(/\r?\n/g, '\n');

let updatedContent = cleanContent;

if (updatedContent.includes(cleanTargetPortsData)) {
    updatedContent = updatedContent.replace(cleanTargetPortsData, cleanReplacementPortsData);
    console.log("Replaced PortsData!");
}
if (updatedContent.includes(cleanTargetMainPorts)) {
    updatedContent = updatedContent.replace(cleanTargetMainPorts, cleanReplacementMainPorts);
    console.log("Replaced MainPorts loop!");
}
if (updatedContent.includes(cleanTargetMainPortsLMarker)) {
    updatedContent = updatedContent.replace(cleanTargetMainPortsLMarker, cleanReplacementMainPortsLMarker);
    console.log("Replaced MainPorts L.marker!");
}
if (updatedContent.includes(cleanTargetMainPortsVisibleItems)) {
    updatedContent = updatedContent.replace(cleanTargetMainPortsVisibleItems, cleanReplacementMainPortsVisibleItems);
    console.log("Replaced MainPorts visibleItems!");
}
if (updatedContent.includes(cleanTargetOriginalPopulate)) {
    updatedContent = updatedContent.replace(cleanTargetOriginalPopulate, cleanReplacementOriginalPopulate);
    console.log("Replaced OriginalPopulate!");
}
if (updatedContent.includes(cleanTargetCiLoop)) {
    updatedContent = updatedContent.replace(cleanTargetCiLoop, cleanReplacementCiLoop);
    console.log("Replaced CI loop!");
}

fs.writeFileSync(path, updatedContent, 'utf8');
console.log("Saved updated dashboard.blade.php!");
