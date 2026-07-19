const fs = require('fs');

const content = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');

let index = 0;
let blockCount = 0;
let scriptCode = '';

while ((index = content.indexOf('<script', index)) !== -1) {
    blockCount++;
    const tagEnd = content.indexOf('>', index);
    if (tagEnd === -1) break;
    const blockStart = tagEnd + 1;
    const blockEnd = content.indexOf('</script>', blockStart);
    if (blockEnd === -1) break;
    
    if (blockCount === 18) {
        scriptCode = content.substring(blockStart, blockEnd);
        break;
    }
    index = blockEnd + 9;
}

if (!scriptCode) {
    console.error("Could not find script block 18!");
    process.exit(1);
}

// Write the isolated script block 18 to a scratch file
fs.writeFileSync('resources/views/scratch/isolated_script_18.js', scriptCode, 'utf8');
console.log("Wrote isolated script 18 to resources/views/scratch/isolated_script_18.js");

// Try running syntax check on isolated_script_18.js using node's child_process (which will report exact syntax error line)
const exec = require('child_process').exec;
exec('node --check resources/views/scratch/isolated_script_18.js', (err, stdout, stderr) => {
    if (err) {
        console.error("Syntax Error reported by Node.js check:");
        console.error(stderr);
    } else {
        console.log("No syntax errors found by Node.js check!");
    }
});
