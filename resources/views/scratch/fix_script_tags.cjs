const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

const targetStr = `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js">/* ═══════════════════════════════════════════════════════════`;
const replacementStr = `<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\r\n<script>\r\n/* ═══════════════════════════════════════════════════════════`;

if (content.includes(targetStr)) {
    content = content.replace(targetStr, replacementStr);
    console.log("Successfully fixed script tag!");
} else {
    // Try with LF
    const targetStrLF = targetStr.replace(/\r\n/g, '\n');
    const replacementStrLF = replacementStr.replace(/\r\n/g, '\n');
    if (content.includes(targetStrLF)) {
        content = content.replace(targetStrLF, replacementStrLF);
        console.log("Successfully fixed script tag using LF!");
    } else {
        console.error("Error: Could not find script target!");
    }
}

const finalContent = content.replace(/\r\n/g, '\n').replace(/\n/g, '\r\n');
fs.writeFileSync(path, finalContent, 'utf8');
console.log("Saved file!");
