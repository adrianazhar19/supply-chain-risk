const fs = require('fs');
const code = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8').replace(/\r\n/g, '\n');

const jsStart = 'function initCountriesTable() {';
const startIdx = code.indexOf(jsStart);
console.log("startIdx:", startIdx);

if (startIdx !== -1) {
    const portsIdx = code.indexOf('PORTS TABLE', startIdx);
    if (portsIdx !== -1) {
        console.log("Found PORTS TABLE at index:", portsIdx);
        console.log("Distance:", portsIdx - startIdx);
        console.log(JSON.stringify(code.substring(portsIdx - 100, portsIdx + 100)));
    } else {
        console.log("PORTS TABLE comment not found after startIdx!");
    }
}
