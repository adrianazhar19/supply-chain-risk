const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// Target the portsData.forEach loop start
const targetPortsLoopStart = `            // 1. Draw Ports (Cluster)
            if (layerSelect === 'ports' || layerSelect === 'both') {
                if (typeof portsData !== 'undefined' && Array.isArray(portsData)) {
                    portsData.forEach(p => {
                        // Region Filter`;

const replacementPortsLoopStart = `            // 1. Draw Ports (Cluster)
            if (layerSelect === 'ports' || layerSelect === 'both') {
                if (typeof portsData !== 'undefined' && Array.isArray(portsData)) {
                    portsData.forEach(p => {
                        if (p.latitude === null || p.longitude === null) return;
                        const lat = parseFloat(p.latitude);
                        const lon = parseFloat(p.longitude);
                        if (isNaN(lat) || isNaN(lon) || lat === 0 || lon === 0 || lat < -90 || lat > 90 || lon < -180 || lon > 180) return;

                        // Region Filter`;

// String checks with normalised newlines
const cleanContent = content.replace(/\r?\n/g, '\n');
const cleanTarget = targetPortsLoopStart.replace(/\r?\n/g, '\n');
const cleanReplacement = replacementPortsLoopStart.replace(/\r?\n/g, '\n');

if (cleanContent.includes(cleanTarget)) {
    const updated = cleanContent.replace(cleanTarget, cleanReplacement);
    fs.writeFileSync(path, updated, 'utf8');
    console.log("Successfully fixed portsData.forEach loop definition!");
} else {
    console.log("Could not find cleanTarget in content!");
}
