const fs = require('fs');

const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// We want to find the script block 18.
let index = 0;
let blockCount = 0;
let blockStart = -1;
let blockEnd = -1;

while ((index = content.indexOf('<script', index)) !== -1) {
    blockCount++;
    const tagEnd = content.indexOf('>', index);
    if (tagEnd === -1) break;
    const currentStart = tagEnd + 1;
    const currentEnd = content.indexOf('</script>', currentStart);
    if (currentEnd === -1) break;
    
    if (blockCount === 18) {
        blockStart = currentStart;
        blockEnd = currentEnd;
        break;
    }
    index = currentEnd + 9;
}

if (blockStart === -1 || blockEnd === -1) {
    console.error("Could not find script block 18 in dashboard.blade.php!");
    process.exit(1);
}

const scriptCode = content.substring(blockStart, blockEnd);

// Let's locate the two 'const CI =' occurrences
const searchStr = 'const CI = (() => {';
const firstCIIdx = scriptCode.indexOf(searchStr);
const secondCIIdx = scriptCode.indexOf(searchStr, firstCIIdx + 1);

if (firstCIIdx === -1 || secondCIIdx === -1) {
    console.error("Could not find both CI definitions!");
    console.log("First:", firstCIIdx, "Second:", secondCIIdx);
    process.exit(1);
}

console.log(`First CI starts at index ${firstCIIdx}`);
console.log(`Second CI starts at index ${secondCIIdx}`);

// The new CI module starts at firstCIIdx. Let's find its return statement or end.
// We know that before the second CI module, there is:
// return { init, refresh, applyFilters, resetFilters, filterMap, openProfile, exportTable };
const returnStr = 'return { init, refresh, applyFilters, resetFilters, filterMap, openProfile, exportTable };';
const returnIdx = scriptCode.indexOf(returnStr, firstCIIdx);

if (returnIdx === -1) {
    console.error("Could not find the return statement of the first CI block!");
    process.exit(1);
}

// We want the new script code to be:
// 1. Everything in scriptCode up to returnIdx + returnStr.length
// 2. Plus '\n  };\n})();' to close the first CI module correctly.
// 3. Plus whatever is after the end of the second CI module?
// Wait, the second CI module is at the end of the script block, closed by })(); at the very end of scriptCode.
// So we don't need anything from the second CI module at all!

const newCIBlock = scriptCode.substring(firstCIIdx, returnIdx + returnStr.length) + '\n})();';
const newScriptCode = scriptCode.substring(0, firstCIIdx) + newCIBlock;

// Replace script block 18 content in the file
const newContent = content.substring(0, blockStart) + newScriptCode + content.substring(blockEnd);
fs.writeFileSync(path, newContent, 'utf8');
console.log("Successfully fixed the duplicate CI block inside dashboard.blade.php!");
