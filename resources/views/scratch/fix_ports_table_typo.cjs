const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

const targetLine = '<td class="col-align-center">\text-center">\text-center">${typeHtml}</td>';
const targetLineTab = '<td class="col-align-center">\text-center">\text-center">${typeHtml}</td>';

// Find the line that has ${typeHtml} around line 3280
const lines = content.split('\r\n');
for (let i = 0; i < lines.length; i++) {
    if (lines[i].includes('${typeHtml}') && lines[i].includes('portsTable')) {
        // Wait, it is inside portsTable loop!
    }
}

// Let's replace the corrupted line directly by matching the unique substrings:
content = content.replace(/<td class="col-align-center">\\text-center">\\text-center">\$\{typeHtml\}<\/td>/g, '<td class="col-align-center">${typeHtml}</td>');
content = content.replace(/<td class="col-align-center">\\text-center">\\text-center">\$\{typeHtml\}<\/td>/, '<td class="col-align-center">${typeHtml}</td>');
content = content.replace('<td class="col-align-center">\text-center">\text-center">${typeHtml}</td>', '<td class="col-align-center">${typeHtml}</td>');
content = content.replace('<td class="col-align-center">\text-center">\text-center">${typeHtml}</td>', '<td class="col-align-center">${typeHtml}</td>');

// Let's also do a general search and replace of tabs or text-center inside that block
content = content.replace(/<td class="col-align-center">\\text-center">\\text-center">\$\{typeHtml\}/g, '<td class="col-align-center">${typeHtml}');
content = content.replace(/<td class="col-align-center">\\text-center">\$\{typeHtml\}/g, '<td class="col-align-center">${typeHtml}');
content = content.replace(/<td class="col-align-center">\\t\$\{typeHtml\}/g, '<td class="col-align-center">${typeHtml}');

fs.writeFileSync(path, content, 'utf8');
console.log("Typo correction script executed!");
