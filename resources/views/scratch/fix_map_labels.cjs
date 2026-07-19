const fs = require('fs');
const path = 'c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

// 1. Add CSS definitions inside the head style tag
const targetStyleStart = '<style>';
const cssToInsert = `
.country-label {
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: white !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-shadow:
        1px 1px 3px #000,
        -1px -1px 3px #000;
}
.hide-country-labels .leaflet-tooltip.country-label {
    display: none !important;
}
`;

const styleStartIdx = content.indexOf(targetStyleStart);
if (styleStartIdx !== -1) {
    const insertPos = styleStartIdx + targetStyleStart.length;
    content = content.substring(0, insertPos) + cssToInsert + content.substring(insertPos);
    console.log("Successfully inserted CSS classes into style tag!");
} else {
    console.log("Could not find style tag start!");
}

// 2. Add bindTooltip and zoomend listener to overview populateMaps
const targetOverviewLoop = `          circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
          cluster.addLayer(circle);`;

const replacementOverviewLoop = `          circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
          circle.bindTooltip(r.country.name, {
              permanent: true,
              direction: "top",
              offset: [0, -12],
              className: "country-label"
          });
          cluster.addLayer(circle);`;

const targetOverviewEnd = `        map.addLayer(cluster);`;
const replacementOverviewEnd = `        map.addLayer(cluster);

        map.on('zoomend', () => {
            if (map.getZoom() < 4) {
                map.getContainer().classList.add('hide-country-labels');
            } else {
                map.getContainer().classList.remove('hide-country-labels');
            }
        });
        if (map.getZoom() < 4) {
            map.getContainer().classList.add('hide-country-labels');
        } else {
            map.getContainer().classList.remove('hide-country-labels');
        }`;

// 3. Add bindTooltip and zoomend listener to main initMainMap
const targetMainLoop = `                    circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
                    riskCirclesGroup.addLayer(circle);`;

const replacementMainLoop = `                    circle.bindPopup(buildRiskPopup(r), { maxWidth: 280 });
                    circle.bindTooltip(r.country.name, {
                        permanent: true,
                        direction: "top",
                        offset: [0, -12],
                        className: "country-label"
                    });
                    riskCirclesGroup.addLayer(circle);`;

const targetMainEnd = `            map.addLayer(clusterGroup);
            map.addLayer(riskCirclesGroup);`;

const replacementMainEnd = `            map.addLayer(clusterGroup);
            map.addLayer(riskCirclesGroup);

            map.on('zoomend', () => {
                if (map.getZoom() < 4) {
                    map.getContainer().classList.add('hide-country-labels');
                } else {
                    map.getContainer().classList.remove('hide-country-labels');
                }
            });
            if (map.getZoom() < 4) {
                map.getContainer().classList.add('hide-country-labels');
            } else {
                map.getContainer().classList.remove('hide-country-labels');
            }`;

// String checks with normalised newlines
const cleanContent = content.replace(/\r?\n/g, '\n');

const cleanTargetOverviewLoop = targetOverviewLoop.replace(/\r?\n/g, '\n');
const cleanReplacementOverviewLoop = replacementOverviewLoop.replace(/\r?\n/g, '\n');

const cleanTargetOverviewEnd = targetOverviewEnd.replace(/\r?\n/g, '\n');
const cleanReplacementOverviewEnd = replacementOverviewEnd.replace(/\r?\n/g, '\n');

const cleanTargetMainLoop = targetMainLoop.replace(/\r?\n/g, '\n');
const cleanReplacementMainLoop = replacementMainLoop.replace(/\r?\n/g, '\n');

const cleanTargetMainEnd = targetMainEnd.replace(/\r?\n/g, '\n');
const cleanReplacementMainEnd = replacementMainEnd.replace(/\r?\n/g, '\n');

let updatedContent = cleanContent;

if (updatedContent.includes(cleanTargetOverviewLoop)) {
    updatedContent = updatedContent.replace(cleanTargetOverviewLoop, cleanReplacementOverviewLoop);
    console.log("Replaced Overview loop!");
}
if (updatedContent.includes(cleanTargetOverviewEnd)) {
    updatedContent = updatedContent.replace(cleanTargetOverviewEnd, cleanReplacementOverviewEnd);
    console.log("Replaced Overview end!");
}
if (updatedContent.includes(cleanTargetMainLoop)) {
    updatedContent = updatedContent.replace(cleanTargetMainLoop, cleanReplacementMainLoop);
    console.log("Replaced Main loop!");
}
if (updatedContent.includes(cleanTargetMainEnd)) {
    updatedContent = updatedContent.replace(cleanTargetMainEnd, cleanReplacementMainEnd);
    console.log("Replaced Main end!");
}

fs.writeFileSync(path, updatedContent, 'utf8');
console.log("Saved updated dashboard.blade.php!");
