const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');
const block2 = lines.slice(2836, 4075).join('\n');

console.log("Last 100 chars of block2:");
console.log(JSON.stringify(block2.substring(block2.length - 100)));
