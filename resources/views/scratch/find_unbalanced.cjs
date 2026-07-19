const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');

// Let's print every function declaration in the CI module (lines 2838 to 4075)
// and check if that function is syntactically valid on its own.
for (let i = 2837; i < 4075; i++) {
    const line = lines[i];
    if (line.includes('function ') || line.includes('async function ')) {
        console.log(`${i+1}: ${line.trim()}`);
    }
}
