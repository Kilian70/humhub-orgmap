/* Actual module scripts in a headless browser, synthetic DOM and loopback HTTP only. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const scripts = ['core', 'persistence', 'svg', 'icon-picker', 'orgmap', 'tree', 'zoom', 'panning', 'dragging', 'resize', 'interaction'];
let mode = 'success', requests = [], checks = 0;
const check = (value, message) => { assert(value, message); checks++; };
const markup = edit => `
<div class="orgmap-layout" data-view-mode="split">
<button class="orgmap-view-btn" data-view="map"></button>
<input id="tree-search" data-result-label="Treffer"><p id="tree-search-status"></p>
<div class="tree-organ-content" data-organ-content="1">
<button class="tree-toggle" data-organ-id="1"></button>
<div class="tree-card" data-search="alpha"><a class="tree-link" data-node-id="1" href="#">Alpha</a></div>
<div class="tree-card" data-search="beta"><a class="tree-link" data-node-id="2" href="#">Beta</a></div>
</div>
<p id="orgmap-save-status" role="alert" data-error-label="Save failed; restored"></p>
<div class="orgmap-scroll"><div class="orgmap-stage"><div class="orgmap-wrapper"
data-edit-mode="${edit}" data-workspace-size="small" data-save-position-url="/position" data-save-label-url="/label">
<div class="org-node-html" data-id="1" data-x="300" data-y="300" data-width="160" data-height="160" data-radius="80"
style="position:absolute;left:220px;top:220px;width:160px;height:160px">
<span class="org-node-label" style="left:80px;top:80px">Alpha</span>${edit ? '<div class="org-node-resize" tabindex="0" aria-valuenow="160"></div>' : ''}</div>
<svg><text class="org-connection-label" data-connection-id="1" data-offset-x="50" data-offset-y="60" x="50" y="60" transform="rotate(10)">Label</text></svg>
</div></div></div></div>`;
const html = () => `<html><head><style>.orgmap-stage,.orgmap-wrapper{width:1000px;height:600px;position:relative}.org-node-resize{width:16px;height:16px}</style></head>
<body><div id="mount">${markup(1)}</div><script>
window.testListeners = {}; const add = document.addEventListener.bind(document);
document.addEventListener = (type, handler, options) => { testListeners[type] = (testListeners[type] || 0) + 1; return add(type, handler, options); };
window.$ = () => ({on: (type, handler) => document.addEventListener(type, handler)});
window.yii = {getCsrfToken: () => 'synthetic-test-token'};
</script>${scripts.map(name => `<script src="/js/${name}.js"></script>`).join('')}</body></html>`;
const server = http.createServer(async (req, res) => {
    if (req.url.startsWith('/js/')) {
        const name = req.url.slice(4, -3);
        if (!scripts.includes(name)) { res.writeHead(404).end(); return; }
        res.setHeader('Content-Type', 'text/javascript');
        res.end(fs.readFileSync(path.join(__dirname, '../resources/js/', name + '.js'))); return;
    }
    if (req.method !== 'POST') { res.setHeader('Content-Type', 'text/html'); res.end(html()); return; }
    let body = ''; for await (const chunk of req) { body += chunk; }
    const data = JSON.parse(body); requests.push({url: req.url, headers: req.headers, data});
    res.setHeader('Content-Type', 'application/json');
    if (mode === 'network') { req.socket.destroy(); return; }
    if (mode === 'html') { res.setHeader('Content-Type', 'text/html'); res.end('<html>Login</html>'); return; }
    if (mode === 'invalid-json') { res.end('{broken'); return; }
    if (mode === 'gate') { res.statusCode = 401; res.setHeader('X-Redirect', '/gate'); res.end(JSON.stringify({gate: 'legal', url: '/gate'})); return; }
    if (mode === 'login') { res.writeHead(302, {Location: '/login'}); res.end(); return; }
    if (mode === '401' || mode === '403' || mode === '422') { res.statusCode = Number(mode); res.end(JSON.stringify({success: false})); return; }
    if (mode === 'false') { res.end(JSON.stringify({success: false})); return; }
    if (req.url === '/label') { res.end(JSON.stringify({success: true, label_offset_x: Math.round(data.label_offset_x), label_offset_y: Math.round(data.label_offset_y)})); return; }
    res.end(JSON.stringify({success: true, x: Math.round(Number(data.x)), y: Math.round(Number(data.y)), width: Number(data.width), height: Number(data.height), radius: Number(data.radius), label_x: data.label_x == null ? null : Math.round(Number(data.label_x)), label_y: data.label_y == null ? null : Math.round(Number(data.label_y))}));
});

(async () => {
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const origin = `http://127.0.0.1:${server.address().port}`;
    const browser = await chromium.launch({headless: true, ...(process.env.CHROME_EXECUTABLE ? {executablePath: process.env.CHROME_EXECUTABLE} : {})});
    try {
        const page = await browser.newPage(); const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        const open = async () => { mode = 'success'; await page.goto(origin); await page.waitForTimeout(400); };
        await open();
        await page.locator('#tree-search').fill('alpha');
        check(await page.locator('#tree-search-status').textContent() === 'Treffer: 1', 'Tree result announcement failed');
        check(await page.locator('.tree-card').last().evaluate(e => e.style.display) === 'none', 'Tree filtering failed');
        await page.locator('#tree-search').press('Escape');
        check(await page.locator('#tree-search').inputValue() === '', 'Escape did not clear search');
        check(await page.locator('#tree-search-status').textContent() === '', 'Escape did not clear announcement');

        const listeners = await page.evaluate(() => ({...testListeners}));
        for (let i = 0; i < 3; i++) {
            await page.evaluate(html => {
                document.dispatchEvent(new Event('pjax:beforeReplace'));
                document.getElementById('mount').innerHTML = html;
                document.dispatchEvent(new Event('pjax:success'));
            }, markup(1));
            await page.evaluate(() => {
                const wrapper = document.querySelector('.orgmap-wrapper');
                wrapper.dispatchEvent(new MouseEvent('mousedown', {bubbles: true, clientX: 50, clientY: 60}));
            });
            check(await page.evaluate(() => state.isPanning) === true, 'Replacement map cannot pan');
            await page.evaluate(() => document.dispatchEvent(new MouseEvent('mouseup', {bubbles: true})));
            await page.evaluate(() => document.querySelector('.org-node-html').dispatchEvent(new MouseEvent('mousedown', {bubbles: true, clientX: 300, clientY: 300})));
            check(await page.evaluate(() => state.selectedNode?.dataset.id) === '1', 'Replacement node cannot drag');
            await page.evaluate(() => document.dispatchEvent(new MouseEvent('mouseup', {bubbles: true})));
        }
        assert.deepEqual(await page.evaluate(() => ({...testListeners})), listeners); checks++;
        await page.locator('#tree-search').fill('beta');
        check(await page.locator('#tree-search-status').textContent() === 'Treffer: 1', 'Search failed after repeated PJAX replacements');
        await page.locator('#tree-search').press('Escape');
        check(await page.locator('.tree-card').first().evaluate(e => e.style.display) === '', 'Escape failed after PJAX');
        await page.evaluate(html => {
            document.dispatchEvent(new Event('pjax:beforeReplace'));
            document.getElementById('mount').innerHTML = html;
            document.dispatchEvent(new Event('pjax:success'));
        }, markup(0));
        await page.evaluate(() => document.querySelector('.org-node-html').dispatchEvent(new MouseEvent('mousedown', {bubbles: true})));
        check(await page.evaluate(() => state.selectedNode) === null, 'Read-only view allows dragging');

        await open();
        const move = async x => page.evaluate(async x => {
            state.selectedNode = document.querySelector('.org-node-html');
            handleDrag(x, 300, 80, 80);
            await savePosition(); state.selectedNode = null;
        }, x);
        await move(600.4);
        check(await page.locator('.org-node-html').getAttribute('data-x') === '600', 'Confirmed server position not applied');
        const headers = requests.at(-1).headers;
        check(headers['x-requested-with'] === 'XMLHttpRequest' && headers.accept === 'application/json' && headers['x-csrf-token'] === 'synthetic-test-token', 'AJAX/CSRF/content negotiation headers missing');
        for (const failure of ['false', '401', '403', '422', 'html', 'invalid-json', 'network']) {
            mode = failure; await move(900);
            check(await page.locator('.org-node-html').getAttribute('data-x') === '600', failure + ' did not restore last confirmed position');
            check(await page.locator('#orgmap-save-status').textContent() !== '', failure + ' lacks visible failure message');
        }
        mode = '403';
        await page.evaluate(async () => {
            state.selectedNode = document.querySelector('.org-node-html');
            state.selectedNode.dataset.labelX = '900'; state.selectedNode.dataset.labelY = '800';
            const label = state.selectedNode.querySelector('.org-node-label');
            label.style.left = '900px'; label.style.top = '800px'; label.style.transform = 'none';
            await savePosition(); state.selectedNode = null;
        });
        check(await page.locator('.org-node-label').evaluate(e => e.style.left) === '80px', 'Failed node label move did not restore style');
        check(await page.locator('.org-node-html').getAttribute('data-label-x') === null, 'Failed node label move did not restore data');
        mode = 'success';
        await page.evaluate(async () => {
            state.selectedNode = document.querySelector('.org-node-html');
            handleResize(8000); await savePosition(); state.selectedNode = null;
        });
        check(await page.locator('.org-node-html').getAttribute('data-width') === '8000', 'Large background was reduced');
        mode = '403';
        await page.evaluate(async () => {
            state.selectedNode = document.querySelector('.org-node-html');
            handleResize(100); await savePosition(); state.selectedNode = null;
        });
        check(await page.locator('.org-node-html').getAttribute('data-width') === '8000', 'Failed resize did not restore dimensions');
        mode = 'success';
        await page.evaluate(async () => {
            state.selectedNode = document.querySelector('.org-node-html');
            handleResize(171.6); await savePosition(); state.selectedNode = null;
        });
        check(requests.at(-1).data.width === '172' && requests.at(-1).data.radius === '86', 'Mouse resize sends fractional dimensions');
        mode = 'success'; await open();
        await page.evaluate(() => {
            state.selectedConnectionLabel = document.querySelector('.org-connection-label');
            state.selectedConnectionLabel.setAttribute('x', '200.4'); state.selectedConnectionLabel.setAttribute('transform', '');
            document.dispatchEvent(new MouseEvent('mouseup', {bubbles: true}));
        });
        await page.waitForFunction(() => document.querySelector('.org-connection-label').dataset.orgmapSaving !== '1');
        check(await page.locator('.org-connection-label').getAttribute('x') === '200', 'Confirmed label not synchronized');
        mode = '403';
        await page.evaluate(() => {
            state.selectedConnectionLabel = document.querySelector('.org-connection-label');
            state.selectedConnectionLabel.setAttribute('x', '900');
            document.dispatchEvent(new MouseEvent('mouseup', {bubbles: true}));
        });
        await page.waitForFunction(() => document.querySelector('.org-connection-label').dataset.orgmapSaving !== '1');
        check(await page.locator('.org-connection-label').getAttribute('x') === '200', 'Failed label move did not restore confirmed layout');

        // Prevent parallel submissions while saving: delayed request + another edit.
        await open();
        await page.route('**/position', async route => { await new Promise(resolve => setTimeout(resolve, 150)); await route.continue(); });
        await page.evaluate(() => {
            state.selectedNode = document.querySelector('.org-node-html'); handleDrag(500, 300, 80, 80); savePosition();
            state.selectedNode = null;
            document.querySelector('.org-node-resize').dispatchEvent(new KeyboardEvent('keydown', {key: 'ArrowRight', bubbles: true}));
        });
        check(await page.locator('.org-node-html').getAttribute('data-width') === '160', 'Pending save permitted another resize');
        await page.waitForFunction(() => document.querySelector('.org-node-html').dataset.orgmapSaving !== '1');
        await page.unroute('**/position');

        for (const failure of ['gate', 'login']) {
            await open(); mode = failure;
            const target = failure === 'gate' ? '/gate' : '/login';
            await Promise.all([page.waitForURL(origin + target), move(900)]);
            check(page.url() === origin + target, failure + ' redirect was not handled');
        }
        check(errors.length === 0, 'Browser errors: ' + errors.join('; '));
        console.log(JSON.stringify({browser: await browser.version(), playwright: require((process.env.PLAYWRIGHT_MODULE || 'playwright') + '/package.json').version, checks, result: 'PASS'}));
    } finally { await browser.close(); await new Promise(resolve => server.close(resolve)); }
})().catch(error => { console.error(error); server.close(); process.exitCode = 1; });
