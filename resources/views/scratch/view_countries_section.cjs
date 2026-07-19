const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const idx = code.indexOf('id="page-countries"');
if (idx !== -1) {
    console.log(code.substring(idx - 100, idx + 1200));
} else {
    console.log("Not found!");
}
