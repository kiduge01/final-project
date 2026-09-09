const fs = require('fs');
const content = fs.readFileSync('C:/wamp64/www/Cmain/app/views/pages/procurement.php', 'utf8');
const scriptMatches = content.match(/<script>([\s\S]*?)<\/script>/g) || [];
let allJS = '';
scriptMatches.forEach(s => { allJS += s.replace(/<script>/g, '').replace(/<\/script>/g, '') + '\n'; });
// Replace PHP expressions with dummy values
allJS = allJS.replace(/const BASE_URL\s*=\s*'[^']*';/, "const BASE_URL = '/Cmain/public';");
allJS = allJS.replace(/const API\s*=.*?;/, "const API = BASE_URL + '/api/v1';");
allJS = allJS.replace(/const CAN_\w+\s*=\s*(?:true|false);/g, '');
try {
    new Function(allJS);
    console.log('JS syntax OK - no errors found');
} catch(e) {
    console.error('JS syntax ERROR:', e.message);
    // Find approximate line
    const lines = allJS.split('\n');
    console.error('Total JS lines:', lines.length);
}
