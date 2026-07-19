const fs = require('fs');

const transcriptPath = "C:\\Users\\ASUS\\.gemini\\antigravity-ide\\brain\\e4f87c13-d1d1-4bdf-b988-49b450247d37\\.system_generated\\logs\\transcript_full.jsonl";
const lines = fs.readFileSync(transcriptPath, 'utf8').split('\n').filter(l => l.trim() !== '');

lines.forEach(line => {
    const step = JSON.parse(line);
    const tcStr = JSON.stringify(step.tool_calls || []);
    if (tcStr.includes('page-countries') || tcStr.includes('page-country-intelligence')) {
        console.log(`Step ${step.step_index}: target/replacement contains the page ID`);
    }
});
