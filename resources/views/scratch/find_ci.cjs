const fs = require('fs');
const code = fs.readFileSync('resources/views/scratch/isolated_script_18.js', 'utf8');
const lines = code.split('\n');
lines.forEach((line, idx) => {
    if (line.includes('CI =') || line.includes('CI=')) {
        console.log(`${idx+1}: ${line}`);
    }
});
