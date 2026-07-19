const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');
lines.forEach((line, idx) => {
    if (line.includes('<section class="content-page"')) {
        console.log(`${idx+1}: ${line.trim()}`);
    }
});
