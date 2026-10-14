/* Real handlers and save/rollback code; simulated DOM and JSON responses only. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
let checks = 0;
function check(actual, expected, message) { assert.deepEqual(actual, expected, message); checks++; }
function dataset(values = {}) { return new Proxy({...values}, {set(target, key, value) { target[key] = String(value); return true; }}); }
function element(attrs = {}, style = {}) {
    return {attrs: {...attrs}, style: {...style}, dataset: dataset(), listeners: {},
        getAttribute(key) { return key === 'style' ? JSON.stringify(this.style) : this.attrs[key] ?? null; },
        setAttribute(key, value) { if (key === 'style') { this.style = JSON.parse(value); } else { this.attrs[key] = String(value); } },
        removeAttribute(key) { delete this.attrs[key]; },
        addEventListener(type, fn) { this.listeners[type] = fn; },
    };
}
function setup(zoom, stored = {x: 12, y: 13, cx: 50, cy: 60}) {
    const handlers = {}, requests = [], pending = [];
    const label = element({}, {left: stored.x + 'px', top: stored.y + 'px', transform: 'none'});
    const handle = element({'aria-valuenow': '160'});
    const node = element({}, {left: '220px', top: '220px', width: '160px', height: '160px'});
    node.dataset = dataset({id: '1', x: '300', y: '300', width: '160', height: '160', radius: '80', labelX: String(stored.x), labelY: String(stored.y)});
    node.offsetWidth = node.offsetHeight = 160;
    node.getBoundingClientRect = () => ({left: 20, top: 30});
    node.querySelector = selector => selector === '.org-node-label' ? label : selector === '.org-node-resize' ? handle : null;
    const connection = element({x: String(stored.cx), y: String(stored.cy), transform: ''});
    connection.dataset = dataset({connectionId: '2', offsetX: String(stored.cx), offsetY: String(stored.cy)});
    connection.querySelector = () => null;
    const wrapper = {dataset: {editMode: '1', savePositionUrl: '/position', saveLabelUrl: '/label'}, querySelectorAll: selector => selector === '.org-node-html' ? [node] : [connection], getBoundingClientRect: () => ({left: 0, top: 0})};
    const state = {selectedNode: null, selectedConnectionLabel: null, isPanning: false, isResizing: false, isDragging: false, dragStartX: 0, dragStartY: 0};
    const status = {textContent: '', dataset: {errorLabel: 'Save failed'}};
    const control = {fail: false};
    const context = vm.createContext({state, pending, URL, window: {innerWidth: 1280, orgmapInitialized: {}, orgmapView: {zoom}, addEventListener() {}, location: {href: 'http://localhost/test', origin: 'http://localhost', assign() { throw new Error('Unexpected redirect'); }}},
        document: {addEventListener(type, fn) { handlers[type] = fn; }, querySelector: () => wrapper, getElementById: () => status},
        yii: {getCsrfToken: () => 'isolated-fixture'}, $: () => ({on() {}}), updateLines() {}, MIN_NODE_SIZE: 40, MAX_NODE_SIZE: 10000,
        fetch: async (url, options) => { const data = JSON.parse(options.body); requests.push({url, data}); return {ok: !control.fail, redirected: false, headers: {get: key => key === 'Content-Type' ? 'application/json' : null}, json: async () => url === '/label' ? {success: !control.fail, label_offset_x: Math.round(data.label_offset_x), label_offset_y: Math.round(data.label_offset_y)} : {success: !control.fail, x: 300, y: 300, width: 160, height: 160, radius: 80, label_x: Math.round(data.label_x), label_y: Math.round(data.label_y)}}; },
    });
    for (const script of ['persistence', 'dragging', 'interaction']) { vm.runInContext(fs.readFileSync(path.join(__dirname, '../resources/js/' + script + '.js'), 'utf8'), context); }
    vm.runInContext('const actualPersist = orgmapPersist; orgmapPersist = function(...args) { const result = actualPersist(...args); pending.push(result); return result; };', context);
    return {state, handlers, requests, node, label, connection, control, status, pending};
}
(async () => {
    for (const zoom of [0.5, 1, 2]) {
        const test = setup(zoom);
        const moveNodeLabel = async (x, y) => {
            test.state.selectedNode = test.node; test.state.dragStartX = test.state.dragStartY = 0;
            test.handlers.mousemove({clientX: 20 + x * zoom, clientY: 30 + y * zoom, altKey: true});
            test.handlers.mouseup(); await Promise.all(test.pending.splice(0));
        };
        await moveNodeLabel(60, 40);
        check([test.node.dataset.labelX, test.node.dataset.labelY], ['60', '40'], 'Node confirmation coordinates');
        check([test.requests.at(-1).data.label_x, test.requests.at(-1).data.label_y], ['60', '40'], 'Node saved coordinates');
        check([test.label.style.left, test.label.style.top], ['60px', '40px'], 'Node rendered coordinates');
        const moveConnectionLabel = async (dx, dy) => {
            test.state.selectedConnectionLabel = test.connection; test.state.dragStartX = test.state.dragStartY = 0;
            test.handlers.mousemove({clientX: dx * zoom, clientY: dy * zoom, altKey: true});
            test.handlers.mouseup(); await Promise.all(test.pending.splice(0));
        };
        await moveConnectionLabel(30, 40);
        check([test.requests.at(-1).data.label_offset_x, test.requests.at(-1).data.label_offset_y], [80, 100], 'Connection saved coordinates');
        check([test.connection.getAttribute('x'), test.connection.getAttribute('y')], ['80', '100'], 'Connection rendered coordinates');
        const reloaded = setup(zoom, {x: 60, y: 40, cx: 80, cy: 100});
        check([reloaded.label.style.left, reloaded.label.style.top, reloaded.connection.getAttribute('x'), reloaded.connection.getAttribute('y')], ['60px', '40px', '80', '100'], 'Reload must not rescale persisted values');
        test.control.fail = true;
        await moveNodeLabel(200, 300);
        check([test.label.style.left, test.label.style.top, test.node.dataset.labelX, test.node.dataset.labelY], ['60px', '40px', '60', '40'], 'Failed node move rolls back');
        await moveConnectionLabel(400, 400);
        check([test.connection.getAttribute('x'), test.connection.getAttribute('y')], ['80', '100'], 'Failed connection move rolls back');
        check(test.status.textContent, 'Save failed', 'Failure is announced');
    }
    console.log(JSON.stringify({checks, zooms: [0.5, 1, 2], result: 'PASS'}));
})().catch(error => { console.error(error); process.exitCode = 1; });
