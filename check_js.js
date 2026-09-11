const fs = require('fs');
const content = fs.readFileSync('C:/wamp64/www/Cmain/app/views/pages/procurement.php', 'utf8');
// Find the script tag
const start = content.indexOf('<script>');
const end = content.lastIndexOf('</script>');
if (start === -1 || end === -1) { console.log('No script tags found'); process.exit(); }
const js = content.slice(start + 8, end);
console.log('JS length:', js.length, 'chars');
console.log('Last 200 chars:');
console.log(js.slice(-200));
