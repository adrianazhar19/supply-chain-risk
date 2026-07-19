const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');

// Extract Script block #2 (excluding <script> and </script> tags)
const block2 = lines.slice(2836, 4075).join('\n');

try {
    new Function(block2);
    console.log("Block 2 is valid!");
} catch (e) {
    console.error("Syntax Error in block 2:", e);
}
