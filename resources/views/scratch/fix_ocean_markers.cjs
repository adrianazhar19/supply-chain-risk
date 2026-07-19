const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Replace Overview populateMaps circles loop
const targetOverviewLoop = `        // Plot risk circles for all 250 countries
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
          circle.bindTooltip(r.country.name, {
              permanent: true,
              direction: "top",
              offset: [0, -12],
              className: "country-label"
          });
          cluster.addLayer(circle);
          bounds.push([r.country.latitude, r.country.longitude]);
        });`;

const replacementOverviewLoop = `        // Plot risk circles for all 250 countries
        STATE.risks.forEach(r => {
          if (!r.country?.latitude || !r.country?.longitude) return;
          const lat = parseFloat(r.country.latitude);
          const lon = parseFloat(r.country.longitude);
          // Validate coordinates and filter out invalid/ocean-center fallbacks (0,0)
          if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

          const color = riskColor(r.risk_level);
          const score = r.total_score !== null ? parseFloat(r.total_score) : 0;
          const circle = L.circleMarker([lat, lon], {
            radius: 7 + score / 9,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            fillOpacity: 0.65,
          });
          circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
          circle.bindTooltip(r.country.name, {
              permanent: true,
              direction: "top",
              offset: [0, -12],
              className: "country-label"
          });
          cluster.addLayer(circle);
          bounds.push([lat, lon]);
        });`;

// 2. Replace Main initMainMap circles loop
const targetMainLoop = `            // 2. Draw Risk Circles
            if (layerSelect === 'risk' || layerSelect === 'both') {
                STATE.risks.forEach(r => {
                    if (!r.country?.latitude || !r.country?.longitude) return;

                    if (selectedRegion && r.country.region !== selectedRegion) return;
                    if (selectedCountryId && r.country.id != selectedCountryId) return;

                    const color = riskColor(r.risk_level);
                    const score = parseFloat(r.total_score);
                    const circle = L.circleMarker([r.country.latitude, r.country.longitude], {
                        radius: 8 + score / 8,
                        fillColor: color,
                        color: '#fff',
                        weight: 1.5,
                        fillOpacity: 0.55,
                    });
                    circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
                    circle.bindTooltip(r.country.name, {
                        permanent: true,
                        direction: "top",
                        offset: [0, -12],
                        className: "country-label"
                    });
                    riskCirclesGroup.addLayer(circle);
                    visibleItems.push([r.country.latitude, r.country.longitude]);
                });
            }`;

const replacementMainLoop = `            // 2. Draw Risk Circles
            if (layerSelect === 'risk' || layerSelect === 'both') {
                STATE.risks.forEach(r => {
                    if (!r.country?.latitude || !r.country?.longitude) return;
                    const lat = parseFloat(r.country.latitude);
                    const lon = parseFloat(r.country.longitude);
                    // Validate coordinates and filter out invalid/ocean-center fallbacks (0,0)
                    if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

                    if (selectedRegion && r.country.region !== selectedRegion) return;
                    if (selectedCountryId && r.country.id != selectedCountryId) return;

                    const color = riskColor(r.risk_level);
                    const score = parseFloat(r.total_score);
                    const circle = L.circleMarker([lat, lon], {
                        radius: 8 + score / 8,
                        fillColor: color,
                        color: '#fff',
                        weight: 1.5,
                        fillOpacity: 0.55,
                    });
                    circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
                    circle.bindTooltip(r.country.name, {
                        permanent: true,
                        direction: "top",
                        offset: [0, -12],
                        className: "country-label"
                    });
                    riskCirclesGroup.addLayer(circle);
                    visibleItems.push([lat, lon]);
                });
            }`;

// String checks with normalised newlines
const cleanContent = content.replace(/\r?\n/g, '\n');
const cleanTargetOverview = targetOverviewLoop.replace(/\r?\n/g, '\n');
const cleanReplacementOverview = replacementOverviewLoop.replace(/\r?\n/g, '\n');
const cleanTargetMain = targetMainLoop.replace(/\r?\n/g, '\n');
const cleanReplacementMain = replacementMainLoop.replace(/\r?\n/g, '\n');

let updatedContent = cleanContent;

if (updatedContent.includes(cleanTargetOverview)) {
    updatedContent = updatedContent.replace(cleanTargetOverview, cleanReplacementOverview);
    console.log("Replaced Overview loop!");
} else {
    console.log("Could not find cleanTargetOverview in content!");
}

if (updatedContent.includes(cleanTargetMain)) {
    updatedContent = updatedContent.replace(cleanTargetMain, cleanReplacementMain);
    console.log("Replaced Main loop!");
} else {
    console.log("Could not find cleanTargetMain in content!");
}

fs.writeFileSync(path, updatedContent, 'utf8');
console.log("Saved updated dashboard.blade.php!");
