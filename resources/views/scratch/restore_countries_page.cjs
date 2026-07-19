const fs = require('fs');
const execSync = require('child_process').execSync;

const origContent = execSync('git show HEAD:resources/views/dashboard.blade.php', { encoding: 'utf8' });

// Locate the page-countries section in origContent
const startStr = '<!-- ─── PAGE: COUNTRIES ──────────────────────────── -->';
const endStr = '</section>';

const startIdx = origContent.indexOf(startStr);
if (startIdx === -1) {
    console.error("Could not find page-countries start in HEAD version!");
    process.exit(1);
}

const endIdx = origContent.indexOf(endStr, startIdx);
if (endIdx === -1) {
    console.error("Could not find page-countries end in HEAD version!");
    process.exit(1);
}

const countriesPageHtml = origContent.substring(startIdx, endIdx + endStr.length);
console.log("Successfully extracted page-countries HTML block!");

// Read the current modified dashboard.blade.php
const currentPath = 'resources/views/dashboard.blade.php';
let currentContent = fs.readFileSync(currentPath, 'utf8');

// Insert it right before page-ports section:
const portsTarget = '<section class="content-page" id="page-ports">';
if (currentContent.includes(portsTarget)) {
    currentContent = currentContent.replace(portsTarget, countriesPageHtml + '\n\n' + portsTarget);
    fs.writeFileSync(currentPath, currentContent, 'utf8');
    console.log("Successfully restored page-countries HTML section in dashboard.blade.php!");
} else {
    console.error("Could not find page-ports section in current file!");
}
