const fs = require('fs');
const lines = fs.readFileSync('resources/views/scratch/cleaned_js_block_2.js', 'utf8').split('\n');
console.log("Total lines:", lines.length);
const start = Math.max(1, lines.length - 30);
const end = lines.length;
for (let i = start; i <= end; i++) {
    console.log(`${i}: ${lines[i-1]}`);
}
