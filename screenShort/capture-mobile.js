/**
 * Screenshot capture — MOBILE (iPhone 14 Pro)
 * HT ភ្នាក់ Lotto App
 *
 * Viewport: 393 × 852  (iPhone 14 Pro logical size)
 * Device scale: 3x
 * Output:   screenShort/mobile/{admin,master,staff}/<page>.png
 *
 * Usage:
 *   node screenShort/capture-mobile.js
 */

const puppeteer = require('puppeteer');
const path      = require('path');
const fs        = require('fs');

const BASE_URL = 'http://localhost:8080';
const VIEWPORT = { width: 393, height: 852, deviceScaleFactor: 3, isMobile: true, hasTouch: true };
const USER_AGENT = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

// ── Accounts per role ──────────────────────────────────────────
const ROLES = [
    {
        role:     'admin',
        username: 'admin',
        password: 'admin123',
        pages: [
            { name: '01-login',   url: '/login' },
            { name: '02-home',    url: '/home' },
            { name: '03-record',  url: '/record' },
            { name: '04-result',  url: '/result' },
            { name: '05-setting', url: '/setting' },
            { name: '06-account', url: '/account' },
            { name: '07-report',  url: '/report' },
        ],
    },
    {
        role:     'master',
        username: 'master1',
        password: 'master123',
        pages: [
            { name: '01-login',   url: '/login' },
            { name: '02-home',    url: '/home' },
            { name: '03-record',  url: '/record' },
            { name: '04-result',  url: '/result' },
            { name: '05-setting', url: '/setting' },
            { name: '06-account', url: '/account' },
            { name: '07-report',  url: '/report' },
        ],
    },
    {
        role:     'staff',
        username: 'staff1',
        password: 'staff123',
        pages: [
            { name: '01-login',  url: '/login' },
            { name: '02-home',   url: '/home' },
            { name: '03-record', url: '/record' },
            { name: '04-result', url: '/result' },
            { name: '05-account',url: '/account' },
        ],
    },
];

// ── Helpers ────────────────────────────────────────────────────

async function login(page, username, password) {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2', timeout: 15000 });
    await page.waitForSelector('input[placeholder="Username"]', { timeout: 8000 });

    const usernameField = await page.$('input[placeholder="Username"]');
    const passwordField = await page.$('input[type="password"]');

    await usernameField.click({ clickCount: 3 });
    await usernameField.type(username);
    await passwordField.click({ clickCount: 3 });
    await passwordField.type(password);

    // Inertia uses XHR — click then poll for URL change instead of waitForNavigation
    await page.click('button[type="submit"]');
    await page.waitForFunction(
        (base) => !window.location.href.includes('/login'),
        { timeout: 15000 },
        BASE_URL,
    );
    await new Promise(r => setTimeout(r, 800));
}

async function screenshot(page, outPath, label) {
    await page.waitForSelector('nav', { timeout: 8000 }).catch(() => {});
    await new Promise(r => setTimeout(r, 600));
    await page.screenshot({ path: outPath, fullPage: false });
    console.log(`  ✓ ${label}`);
}

// ── Main ───────────────────────────────────────────────────────

(async () => {
    const browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
    });

    const outDir = path.join(__dirname, 'mobile');

    for (const config of ROLES) {
        console.log(`\n── MOBILE │ ${config.role.toUpperCase()} (${config.username}) ──`);

        const roleDir = path.join(outDir, config.role);
        fs.mkdirSync(roleDir, { recursive: true });

        const context = await browser.createBrowserContext();
        const page = await context.newPage();
        await page.setViewport(VIEWPORT);
        await page.setUserAgent(USER_AGENT);

        // Login page (pre-login)
        const loginPage = config.pages.find(p => p.name.includes('login'));
        if (loginPage) {
            await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2', timeout: 15000 });
            await new Promise(r => setTimeout(r, 600));
            await page.screenshot({ path: path.join(roleDir, `${loginPage.name}.png`), fullPage: false });
            console.log(`  ✓ ${loginPage.name} (pre-login)`);
        }

        // Log in
        try {
            await login(page, config.username, config.password);
            console.log(`  ✓ Logged in as ${config.username}`);
        } catch (err) {
            console.error(`  ✗ Login failed for ${config.username}: ${err.message}`);
            await page.close();
            await context.close();
            continue;
        }

        // Authenticated pages
        for (const p of config.pages) {
            if (p.name.includes('login')) continue;
            try {
                await page.goto(`${BASE_URL}${p.url}`, { waitUntil: 'networkidle2', timeout: 15000 });
                await screenshot(page, path.join(roleDir, `${p.name}.png`), p.name);
            } catch (err) {
                console.error(`  ✗ ${p.name}: ${err.message}`);
            }
        }

        await page.close();
        await context.close();
    }

    await browser.close();
    console.log('\n✅ Mobile screenshots saved to screenShort/mobile/{admin,master,staff}/');
})();
