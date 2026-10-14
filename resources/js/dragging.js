/* Bind to each current element; PJAX replacements receive fresh handlers. */
function initDragging() {
    const wrapper = document.querySelector('.orgmap-wrapper');
    if (!wrapper || wrapper.dataset.editMode !== '1') { return; }
    wrapper.querySelectorAll('.org-node-html').forEach(node => {
        if (node.dataset.orgmapDragging === '1') { return; }
        node.dataset.orgmapDragging = '1';
        orgmapRemember(node);
        const resizeHandle = node.querySelector('.org-node-resize');
        if (resizeHandle) {
            resizeHandle.addEventListener('mousedown', event => {
                if (node.dataset.orgmapSaving === '1') { return; }
                event.preventDefault(); event.stopPropagation();
                state.isResizing = true;
                state.selectedNode = node;
                node.dataset.originalZ = node.style.zIndex;
                node.style.zIndex = 9999;
                state.resizeStartSize = node.offsetWidth;
                state.dragStartX = event.clientX;
            });
            resizeHandle.addEventListener('keydown', event => {
                if (!['ArrowUp', 'ArrowRight', 'ArrowDown', 'ArrowLeft'].includes(event.key) || node.dataset.orgmapSaving === '1') { return; }
                event.preventDefault(); event.stopPropagation();
                state.selectedNode = node;
                const direction = ['ArrowUp', 'ArrowRight'].includes(event.key) ? 1 : -1;
                const size = Math.min(MAX_NODE_SIZE, Math.max(MIN_NODE_SIZE, node.offsetWidth + direction * (event.shiftKey ? 1 : 10)));
                handleResize(size);
                resizeHandle.setAttribute('aria-valuenow', String(Math.round(size)));
                savePosition();
                state.selectedNode = null;
            });
        }
        node.addEventListener('mousedown', function (event) {
            if (window.innerWidth < 768 || event.target.closest('.org-node-resize') || node.dataset.orgmapSaving === '1') { return; }
            event.preventDefault(); event.stopPropagation();
            state.selectedNode = node;
            node.dataset.originalZ = node.style.zIndex;
            node.style.zIndex = 9999;
            state.isDragging = false;
            state.dragStartX = event.clientX; state.dragStartY = event.clientY;
        });
    });
    wrapper.querySelectorAll('.org-connection-label').forEach(label => {
        if (label.dataset.orgmapDragging === '1') { return; }
        label.dataset.orgmapDragging = '1';
        orgmapRemember(label);
        label.addEventListener('mousedown', event => {
            if (window.innerWidth < 768 || !event.altKey || label.dataset.orgmapSaving === '1') { return; }
            event.preventDefault(); event.stopPropagation();
            state.selectedConnectionLabel = label;
            state.dragStartX = event.clientX; state.dragStartY = event.clientY;
        });
    });
}

function handleDrag(x, y, halfWidth, halfHeight) {
    state.selectedNode.style.left = (x - halfWidth) + 'px';
    state.selectedNode.style.top = (y - halfHeight) + 'px';
    state.selectedNode.dataset.x = x; state.selectedNode.dataset.y = y;
    updateLines();
}

function savePosition() {
    const node = state.selectedNode;
    const saveUrl = document.querySelector('.orgmap-wrapper')?.dataset.savePositionUrl;
    if (!node || !saveUrl) { return; }
    return orgmapPersist(node, saveUrl, {
        id: node.dataset.id, x: node.dataset.x, y: node.dataset.y,
        label_x: node.dataset.labelX, label_y: node.dataset.labelY,
        radius: Number(node.dataset.radius) > 0 ? node.dataset.radius : undefined, width: node.dataset.width, height: node.dataset.height,
    }, data => {
        const values = {x: data.x, y: data.y, radius: data.radius, width: data.width, height: data.height, labelX: data.label_x, labelY: data.label_y};
        Object.entries(values).forEach(([key, value]) => { if (value !== undefined && value !== null) { node.dataset[key] = value; } });
        node.style.width = node.dataset.width + 'px'; node.style.height = node.dataset.height + 'px';
        node.style.left = (Number(node.dataset.x) - Number(node.dataset.width) / 2) + 'px';
        node.style.top = (Number(node.dataset.y) - Number(node.dataset.height) / 2) + 'px';
        const label = node.querySelector('.org-node-label');
        if (label && data.label_x != null && data.label_y != null) {
            label.style.left = data.label_x + 'px'; label.style.top = data.label_y + 'px';
        }
        const handle = node.querySelector('.org-node-resize');
        if (handle) { handle.setAttribute('aria-valuenow', node.dataset.width); }
        updateLines();
    });
}

initDragging();
$(document).on('pjax:success', initDragging);
