const fs = require('fs');
const path = 'resources/views/dashboard.blade.php';
let content = fs.readFileSync(path, 'utf8');

content = content.replace('})();</script>', '})();\n</script>');

fs.writeFileSync(path, content, 'utf8');
console.log("Successfully formatted dashboard.blade.php script closure!");
