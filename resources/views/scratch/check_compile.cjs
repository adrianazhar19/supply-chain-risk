const fs = require('fs');
const vm = require('vm');

const content = fs.readFileSync('resources/views/dashboard.blade.php', 'utf8');

let index = 0;
let blockCount = 0;

while ((index = content.indexOf('<script', index)) !== -1) {
    blockCount++;
    const tagEnd = content.indexOf('>', index);
    if (tagEnd === -1) break;
    const blockStart = tagEnd + 1;
    const blockEnd = content.indexOf('</script>', blockStart);
    if (blockEnd === -1) {
        console.log(`Script block #${blockCount} has no closing </script>!`);
        break;
    }
    
    const scriptCode = content.substring(blockStart, blockEnd);
    console.log(`Script block #${blockCount} starts at index ${blockStart}, size: ${scriptCode.length} characters`);
    try {
        new vm.Script(scriptCode);
        console.log(`-> Script #${blockCount} is VALID!`);
    } catch (e) {
        console.log(`-> Script #${blockCount} is INVALID!`);
        console.error(e);
    }
    index = blockEnd + 9;
}
