const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

const targetStr = `  // Lazy-load page specific data
  if (page === 'map') initMainMap();
  if (page === 'risk') loadRiskPage();
  if (page === 'countries') initCountriesTable();
  if (page === 'country-intelligence') CI.init();
  if (page === 'ports') initPortsTable();
  if (page === 'news') loadNewsPage();
  if (page === 'currency') loadCurrencyPage();
  if (page === 'weather') loadWeatherPage();
  if (page === 'watchlist') loadWatchlistPage();`;

const replacementStr = `  // Lazy-load page specific data
  try { if (page === 'map') initMainMap(); } catch(e) { console.error("Map load error:", e); }
  try { if (page === 'risk') loadRiskPage(); } catch(e) { console.error("Risk load error:", e); }
  try { if (page === 'countries') initCountriesTable(); } catch(e) { console.error("Countries table load error:", e); }
  try { if (page === 'country-intelligence') CI.init(); } catch(e) { console.error("CI page load error:", e); }
  try { if (page === 'ports') initPortsTable(); } catch(e) { console.error("Ports table load error:", e); }
  try { if (page === 'news') loadNewsPage(); } catch(e) { console.error("News load error:", e); }
  try { if (page === 'currency') loadCurrencyPage(); } catch(e) { console.error("Currency load error:", e); }
  try { if (page === 'weather') loadWeatherPage(); } catch(e) { console.error("Weather load error:", e); }
  try { if (page === 'watchlist') loadWatchlistPage(); } catch(e) { console.error("Watchlist load error:", e); }`;

// Normalize line endings in targetStr to match the file
const normContent = content.replace(/\r\n/g, '\n');
const normTarget = targetStr.replace(/\r\n/g, '\n');

if (normContent.includes(normTarget)) {
    content = normContent.replace(normTarget, replacementStr);
    console.log("Successfully replaced showPage initializers in dashboard.blade.php!");
} else {
    console.error("Error: Could not find showPage initializers in dashboard.blade.php!");
}

const finalContent = content.replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved dashboard.blade.php!");
