const fs = require('fs');
const lines = fs.readFileSync('resources/views/scratch/isolated_script_18.js', 'utf8').split('\n');
const start = Math.max(1, lines.length - 100);
const end = lines.length;
for (let i = start; i <= end; i++) {
    console.log(`${i}: ${lines[i-1]}`);
}
