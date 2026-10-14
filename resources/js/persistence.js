/* Shared response handling for both HumHub versions. No gate is bypassed. */
const orgmapSavedLayouts = new WeakMap();

function orgmapSnapshot(element) {
    const label = element.querySelector('.org-node-label');
    const handle = element.querySelector('.org-node-resize');
    return {
        dataset: Object.fromEntries(['x', 'y', 'labelX', 'labelY', 'radius', 'width', 'height', 'offsetX', 'offsetY'].map(key => [key, element.dataset[key]])),
        style: Object.fromEntries(['left', 'top', 'width', 'height'].map(key => [key, element.style[key]])),
        attributes: Object.fromEntries(['x', 'y', 'transform'].map(key => [key, element.getAttribute(key)])),
        labelStyle: label ? label.getAttribute('style') : null,
        handleStyle: handle ? handle.getAttribute('style') : null,
        handleValue: handle ? handle.getAttribute('aria-valuenow') : null,
    };
}

function orgmapRemember(element) {
    if (!orgmapSavedLayouts.has(element)) { orgmapSavedLayouts.set(element, orgmapSnapshot(element)); }
}

function orgmapRestore(element) {
    const saved = orgmapSavedLayouts.get(element);
    if (!saved) { return; }
    Object.entries(saved.dataset).forEach(([key, value]) => {
        if (value === undefined) { delete element.dataset[key]; } else { element.dataset[key] = value; }
    });
    Object.assign(element.style, saved.style);
    const set = (target, key, value) => {
        if (!target) { return; }
        if (value === null) { target.removeAttribute(key); } else { target.setAttribute(key, value); }
    };
    Object.entries(saved.attributes).forEach(([key, value]) => set(element, key, value));
    set(element.querySelector('.org-node-label'), 'style', saved.labelStyle);
    set(element.querySelector('.org-node-resize'), 'style', saved.handleStyle);
    set(element.querySelector('.org-node-resize'), 'aria-valuenow', saved.handleValue);
    updateLines();
}

async function orgmapRequest(url, payload) {
    const response = await fetch(url, {
        method: 'POST', credentials: 'same-origin',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': yii.getCsrfToken()},
        body: JSON.stringify(payload),
    });
    const json = (response.headers.get('Content-Type') || '').toLowerCase().includes('application/json');
    let data = null;
    if (json) { try { data = await response.json(); } catch (error) { /* Invalid JSON is a failed save. */ } }
    const redirect = response.headers.get('X-Redirect') || (response.redirected ? response.url : null) || (data?.gate ? data.url : null);
    if (redirect || !response.ok || !json || data?.success !== true) {
        const error = new Error('OrgMap save failed');
        error.redirect = redirect;
        throw error;
    }
    return data;
}

async function orgmapPersist(element, url, payload, applyResult) {
    if (element.dataset.orgmapSaving === '1') { return; }
    element.dataset.orgmapSaving = '1';
    try {
        const data = await orgmapRequest(url, payload);
        applyResult(data);
        orgmapSavedLayouts.set(element, orgmapSnapshot(element));
        const status = document.getElementById('orgmap-save-status');
        if (status) { status.textContent = ''; }
    } catch (error) {
        orgmapRestore(element);
        const status = document.getElementById('orgmap-save-status');
        if (status) { status.textContent = status.dataset.errorLabel; }
        if (error.redirect) {
            // Match HumHub's top-level gate/login redirect, after restoring the saved layout.
            try {
                const target = new URL(error.redirect, window.location.href);
                if (target.origin === window.location.origin && ['http:', 'https:'].includes(target.protocol)) {
                    window.location.assign(target.href);
                }
            } catch (invalidUrl) { /* The save has already been visibly rejected and restored. */ }
        }
    } finally {
        delete element.dataset.orgmapSaving;
    }
}

function orgmapResetInteraction() {
    if (state.selectedNode) {
        orgmapRestore(state.selectedNode);
        state.selectedNode.style.zIndex = state.selectedNode.dataset.originalZ || 1;
    }
    if (state.selectedConnectionLabel) { orgmapRestore(state.selectedConnectionLabel); }
    state.selectedNode = null;
    state.selectedConnectionLabel = null;
    state.isDragging = state.isResizing = state.isPanning = false;
}

$(document).on('pjax:beforeReplace', orgmapResetInteraction);
