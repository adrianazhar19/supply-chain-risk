const fs = require('fs');
const transcriptPath = "C:\\Users\\ASUS\\.gemini\\antigravity-ide\\brain\\e4f87c13-d1d1-4bdf-b988-49b450247d37\\.system_generated\\logs\\transcript_full.jsonl";
const lines = fs.readFileSync(transcriptPath, 'utf8').split('\n').filter(l => l.trim() !== '');
for (const line of lines) {
    const step = JSON.parse(line);
    if (step.step_index === 102) {
        console.log(JSON.stringify(step.tool_calls[0].args, null, 2));
        break;
    }
}
