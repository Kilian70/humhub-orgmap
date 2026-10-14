/* Regression for native document mouseenter/mouseleave events observed in MAMP. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const handlers = {};
class Element {
    constructor(link = null) { this.link = link; }
    closest() { return this.link; }
}
const classes = new Set();
const wrapper = {classList: {add: value => classes.add(value), remove: value => classes.delete(value)}};
const document = {
    addEventListener(type, handler) { (handlers[type] ||= []).push(handler); },
    querySelectorAll() { return []; },
    querySelector(selector) { return selector === '.orgmap-wrapper' ? wrapper : null; },
};
const context = vm.createContext({
    document, Element, window: {addEventListener() {}},
    localStorage: {getItem() { return null; }},
    $() { return {on() {}}; },
});
vm.runInContext(fs.readFileSync(require('node:path').join(__dirname, '../resources/js/tree.js'), 'utf8'), context);
let checks = 0;
for (const type of ['mouseenter', 'mouseleave']) {
    for (const target of [document, null, {}]) {
        assert.doesNotThrow(() => handlers[type].forEach(handler => handler({target})));
        checks++;
    }
}
const link = {dataset: {nodeId: '1'}};
handlers.mouseenter.forEach(handler => handler({target: new Element(link)}));
assert(classes.has('tree-hover-active')); checks++;
handlers.mouseleave.forEach(handler => handler({target: new Element(link)}));
assert(!classes.has('tree-hover-active')); checks++;
console.log(JSON.stringify({checks, passed: true}));
