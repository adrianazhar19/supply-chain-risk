const fs = require('fs');

const transcriptPath = "C:\\Users\\ASUS\\.gemini\\antigravity-ide\\brain\\e4f87c13-d1d1-4bdf-b988-49b450247d37\\.system_generated\\logs\\transcript_full.jsonl";
const lines = fs.readFileSync(transcriptPath, 'utf8').split('\n').filter(l => l.trim() !== '');

const steps = lines.map(line => {
    try { return JSON.parse(line); } catch (e) { return null; }
}).filter(x => x !== null);

for (const step of steps) {
    const toolCalls = step.tool_calls || [];
    for (const tc of toolCalls) {
        const name = tc.name;
        let args = tc.args || {};
        if (typeof args === 'string') {
            try { args = JSON.parse(args); } catch (e) {}
        }
        
        const target = args.TargetFile || args.Target || '';
        if (target.includes('dashboard.blade.php')) {
            const replacement = args.ReplacementContent || '';
            const chunks = args.ReplacementChunks || [];
            let chunksStr = JSON.stringify(chunks);
            if (replacement.includes('page-country-intelligence') || chunksStr.includes('page-country-intelligence')) {
                console.log(`Step ${step.step_index} added page-country-intelligence!`);
            }
        }
    }
}
