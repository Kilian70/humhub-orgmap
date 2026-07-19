	console.log('DRAGGING JS LOADED');
	
	/*
	==================================================
	Dragging
	==================================================
	*/
	
	function initDragging() {
	
		if (window.orgmapInitialized.dragging) {
			return;
		}
	
		window.orgmapInitialized.dragging = true;
	
		document.querySelectorAll('.org-node-html').forEach(node => {
			
			/*
			--------------------------------------------------
			Resize Start
			--------------------------------------------------
			*/
			
			const resizeHandle =
				node.querySelector('.org-node-resize');
	
			if (!resizeHandle) {
				return;
			}
	
			resizeHandle.addEventListener('mousedown', function (event) {
	
				event.preventDefault();
				event.stopPropagation();
	
				state.isResizing = true;
	
				state.selectedNode = node;
	
				state.selectedNode.dataset.originalZ =
					state.selectedNode.style.zIndex;
	
				state.selectedNode.style.zIndex = 9999;
	
				state.resizeStartSize =
					node.offsetWidth;
	
				state.dragStartX = event.clientX;
	
			});

			resizeHandle.addEventListener('keydown', function (event) {
				if (!['ArrowUp', 'ArrowRight', 'ArrowDown', 'ArrowLeft'].includes(event.key)) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				state.selectedNode = node;
				const direction = ['ArrowUp', 'ArrowRight'].includes(event.key) ? 1 : -1;
				const step = event.shiftKey ? 1 : 10;
				const size = Math.max(40, node.offsetWidth + (direction * step));
				handleResize(size);
				resizeHandle.setAttribute('aria-valuenow', String(Math.round(size)));
				savePosition();
			});

	/*
	--------------------------------------------------
	Dragging nur Desktop
	--------------------------------------------------
	*/
	
	if (window.innerWidth < 768) {
		return;
	}
	
	document.querySelectorAll(
		'.org-connection-label'
	).forEach(label => {
	
		label.addEventListener(
			'mousedown',
			function (event) {
	
				if (!event.altKey) {
					return;
				}
	
				event.preventDefault();
				event.stopPropagation();
	
				state.selectedConnectionLabel =
					this;
				
				console.log('LABEL DOWN');
				
				state.dragStartX =
					event.clientX;
	
				state.dragStartY =
					event.clientY;
			}
		);
	});
	
	node.addEventListener('mousedown', function (event) {

	if (event.target.closest('.org-node-resize')) {
		return;
	}

	event.preventDefault();
	event.stopPropagation();

	state.selectedNode = this;

	state.selectedNode.dataset.originalZ =
		state.selectedNode.style.zIndex;

	state.selectedNode.style.zIndex = 9999;

	state.isDragging = false;

	state.dragStartX = event.clientX;
	state.dragStartY = event.clientY;

});

	}); 

} 
	
/*
==================================================
Handle Drag
==================================================
*/

function handleDrag(
	x,
	y,
	halfWidth,
	halfHeight
) {

	state.selectedNode.style.left =
		(x - halfWidth) + 'px';
	
	state.selectedNode.style.top =
		(y - halfHeight) + 'px';

	state.selectedNode.dataset.x = x;
	state.selectedNode.dataset.y = y;

	updateLines();
}

/*
==================================================
Save Position
==================================================
*/

function savePosition() {

	const saveUrl = document.querySelector('.orgmap-wrapper')
		?.dataset.savePositionUrl;
	if (!saveUrl) {
		return;
	}

	fetch(saveUrl, {

		method: 'POST',

		headers: {

			'Content-Type': 'application/json',

			'X-CSRF-Token': yii.getCsrfToken()
		},

		body: JSON.stringify({

			id: state.selectedNode.dataset.id,

			x: state.selectedNode.dataset.x,

			y: state.selectedNode.dataset.y,

			label_x:
				state.selectedNode.dataset.labelX,

			label_y:
				state.selectedNode.dataset.labelY,

			radius:
				state.selectedNode.dataset.radius,

			width:
				state.selectedNode.offsetWidth,

			height:
				state.selectedNode.offsetHeight
		})
	})

	.then(response => {
		if (!response.ok) {
			throw new Error(`Position konnte nicht gespeichert werden (${response.status}).`);
		}
		return response.json();
	})

	.then(data => {

		if (!data.success) {
			throw new Error('Position konnte nicht gespeichert werden.');
		}
	})

	.catch(error => {

		console.error(error);
	});
}
	
	initDragging();				
