const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');
const lines = code.split('\n');
const block2 = lines.slice(2835, 4075).join('\n');

const evalCode = `
try {
    eval(${JSON.stringify(block2)});
} catch (e) {
    console.error(e);
}
`;

fs.writeFileSync('resources/views/scratch/temp_eval.js', block2, 'utf8');

try {
    require('child_process').execSync('node resources/views/scratch/temp_eval.js');
    console.log("No syntax errors when running node directly!");
} catch (e) {
    console.error("Syntax Error when running node directly:");
    console.error(e.stderr.toString());
}
