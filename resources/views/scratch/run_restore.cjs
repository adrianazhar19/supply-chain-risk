const fs = require('fs');
const path = require('path');

const transcriptPath = "C:\\Users\\ASUS\\.gemini\\antigravity-ide\\brain\\e4f87c13-d1d1-4bdf-b988-49b450247d37\\.system_generated\\logs\\transcript_full.jsonl";
const targetFilePath = "c:\\Users\\ASUS\\supply-chain-risk\\resources\\views\\dashboard.blade.php";

// Revert the file first to a clean state
const execSync = require('child_process').execSync;
execSync(`git checkout resources/views/dashboard.blade.php`, { cwd: "c:\\Users\\ASUS\\supply-chain-risk" });

// Read transcript lines
const lines = fs.readFileSync(transcriptPath, 'utf8').split('\n').filter(l => l.trim() !== '');

const steps = lines.map(line => {
    try {
        return JSON.parse(line);
    } catch (e) {
        return null;
    }
}).filter(x => x !== null);

const edits = [];
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
            edits.push({ step_index: step.step_index, name, args });
        }
    }
}

// Filter to all previous edits (up to step 800)
const prevEdits = edits.filter(e => e.step_index < 800);
console.log(`Filtering down to ${prevEdits.length} edits.`);

// Flatten into sequential chunks
const chunks = [];
for (const edit of prevEdits) {
    if (edit.name === 'replace_file_content') {
        chunks.push({
            step_index: edit.step_index,
            startLine: parseInt(edit.args.StartLine || edit.args.startLine),
            endLine: parseInt(edit.args.EndLine || edit.args.endLine),
            targetContent: edit.args.TargetContent || edit.args.targetContent,
            replacementContent: edit.args.ReplacementContent || edit.args.replacementContent
        });
    } else if (edit.name === 'multi_replace_file_content') {
        let rChunks = edit.args.ReplacementChunks || edit.args.replacementChunks;
        if (typeof rChunks === 'string') {
            try { rChunks = JSON.parse(rChunks); } catch(e) {}
        }
        if (Array.isArray(rChunks)) {
            for (const rc of rChunks) {
                chunks.push({
                    step_index: edit.step_index,
                    startLine: parseInt(rc.StartLine || rc.startLine),
                    endLine: parseInt(rc.EndLine || rc.endLine),
                    targetContent: rc.TargetContent || rc.targetContent,
                    replacementContent: rc.ReplacementContent || rc.replacementContent
                });
            }
        }
    }
}

// Sort chunks ascending by step_index so we apply them in the exact order they were run
chunks.sort((a, b) => a.step_index - b.step_index);

let fileContent = fs.readFileSync(targetFilePath, 'utf8');

function cleanString(str) {
    if (str === undefined || str === null) return '';
    if (typeof str === 'string' && str.startsWith('"') && str.endsWith('"')) {
        try { str = JSON.parse(str); } catch(e) {}
    }
    return str;
}

let success = true;

for (const chunk of chunks) {
    const target = cleanString(chunk.targetContent);
    const replacement = cleanString(chunk.replacementContent);
    
    if (!target) {
        console.warn(`Warning: Target is empty for Step ${chunk.step_index}! Skipping chunk...`);
        continue;
    }
    
    // Normalize line endings to avoid \r\n vs \n issues
    const normFileContent = fileContent.replace(/\r\n/g, '\n');
    const normTarget = target.replace(/\r\n/g, '\n');
    const normReplacement = replacement.replace(/\r\n/g, '\n');
    
    // Find all occurrences of normTarget in normFileContent
    const occurrences = [];
    let idx = normFileContent.indexOf(normTarget);
    while (idx !== -1) {
        occurrences.push(idx);
        idx = normFileContent.indexOf(normTarget, idx + 1);
    }
    
    if (occurrences.length === 0) {
        console.warn(`Warning: Target not found for Step ${chunk.step_index}! Skipping chunk...`);
        continue;
    }
    
    let chosenIdx = occurrences[0];
    if (occurrences.length > 1) {
        console.log(`Multiple occurrences (${occurrences.length}) found for Step ${chunk.step_index}. Choosing closest...`);
        const fileLines = normFileContent.split('\n');
        let estimatedCharIdx = 0;
        const targetLineNum = chunk.startLine - 1;
        for (let i = 0; i < Math.min(targetLineNum, fileLines.length); i++) {
            estimatedCharIdx += fileLines[i].length + 1; // +1 for \n
        }
        
        let minDiff = Infinity;
        for (const occ of occurrences) {
            const diff = Math.abs(occ - estimatedCharIdx);
            if (diff < minDiff) {
                minDiff = diff;
                chosenIdx = occ;
            }
        }
    }
    
    // Perform replacement at chosenIdx
    fileContent = normFileContent.slice(0, chosenIdx) + normReplacement + normFileContent.slice(chosenIdx + normTarget.length);
    console.log(`Successfully applied Step ${chunk.step_index} replacement.`);
}

if (success) {
    console.log("All steps applied successfully! Saving file...");
    const finalContent = fileContent.replace(/\n/g, '\r\n');
    fs.writeFileSync(targetFilePath, finalContent, 'utf8');
} else {
    console.error("Reconstruction failed.");
}
