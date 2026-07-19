const fs = require('fs');

const orig = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
console.log("Current page-countries occurrences:", orig.split('page-countries').length - 1);
console.log("Current page-country-intelligence occurrences:", orig.split('page-country-intelligence').length - 1);
console.log("Current page-countries tag index:", orig.indexOf('id="page-countries"'));
console.log("Current page-countries index with single quotes:", orig.indexOf("id='page-countries'"));
