const fs = require('fs');
const lines = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8').split('\n');
console.log("Total lines in dashboard.blade.php:", lines.length);
const start = Math.max(1, lines.length - 50);
const end = lines.length;
for (let i = start; i <= end; i++) {
    console.log(`${i}: ${lines[i-1]}`);
}
