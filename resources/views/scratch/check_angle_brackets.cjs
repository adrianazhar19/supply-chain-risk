const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');

for (let i = 2835; i < 4075; i++) {
    const l = lines[i];
    if (l.includes('<')) {
        // Check if it's not inside a string
        // Simple heuristic: count of quotes around it or if it's template literal
        if (!l.includes("'") && !l.includes('"') && !l.includes('`')) {
            console.log(`${i + 1}: ${l.trim()}`);
        }
    }
}
