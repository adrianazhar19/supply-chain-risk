const fs = require('fs');

const code = fs.readFileSync('resources/views/scratch/isolated_script_18.js', 'utf8');

const stack = [];
let insideString = null;
let escapeNext = false;
let lineNum = 1;
let colNum = 1;

for (let i = 0; i < code.length; i++) {
    const char = code[i];
    
    if (char === '\n') {
        lineNum++;
        colNum = 1;
    } else {
        colNum++;
    }
    
    // Ignore escape characters inside strings
    if (escapeNext) {
        escapeNext = false;
        continue;
    }
    
    if (char === '\\') {
        escapeNext = true;
        continue;
    }
    
    // String matching
    if (insideString) {
        if (char === insideString) {
            insideString = null;
        }
        continue;
    }
    
    if (char === "'" || char === '"' || char === '`') {
        insideString = char;
        continue;
    }
    
    // Comments matching (crude check but useful)
    if (char === '/' && code[i+1] === '/') {
        // Skip till end of line
        while (i < code.length && code[i] !== '\n') {
            i++;
        }
        lineNum++;
        colNum = 1;
        continue;
    }
    
    if (char === '/' && code[i+1] === '*') {
        // Skip till */
        i += 2;
        while (i < code.length && !(code[i] === '*' && code[i+1] === '/')) {
            if (code[i] === '\n') lineNum++;
            i++;
        }
        i++;
        continue;
    }
    
    if (char === '{' || char === '(' || char === '[') {
        stack.push({ char, line: lineNum, col: colNum });
    } else if (char === '}' || char === ')' || char === ']') {
        if (stack.length === 0) {
            console.log(`Unmatched closing char '${char}' at line ${lineNum}, col ${colNum}`);
            continue;
        }
        const top = stack.pop();
        const matches = (top.char === '{' && char === '}') ||
                        (top.char === '(' && char === ')') ||
                        (top.char === '[' && char === ']');
        if (!matches) {
            console.log(`Mismatch: opened '${top.char}' at line ${top.line}, col ${top.col} but closed '${char}' at line ${lineNum}, col ${colNum}`);
        }
    }
}

if (stack.length > 0) {
    console.log(`\nUnclosed tokens remaining: ${stack.length}`);
    stack.forEach(t => {
        console.log(`Token '${t.char}' opened at line ${t.line}, col ${t.col}`);
    });
} else {
    console.log("No brace mismatches detected!");
}
