/**
 * Screenshot capture — ALL (Web + Mobile)
 * HT ភ្នាក់ Lotto App
 *
 * Runs both capture-web.js and capture-mobile.js sequentially.
 *
 * Usage:
 *   node screenShort/capture.js          # both web + mobile
 *   node screenShort/capture-web.js      # web only  (1440×900)
 *   node screenShort/capture-mobile.js   # mobile only (393×852 @3x)
 *
 * Requirements:
 *   npm install puppeteer (already done at project root)
 */

const { execFileSync } = require('child_process');
const path = require('path');

const scripts = ['capture-web.js', 'capture-mobile.js'];

for (const script of scripts) {
    const scriptPath = path.join(__dirname, script);
    console.log(`\n${'═'.repeat(50)}`);
    console.log(`Running ${script}...`);
    console.log('═'.repeat(50));
    execFileSync('node', [scriptPath], { stdio: 'inherit' });
}

console.log('\n🏁 All done — web + mobile screenshots captured.');
