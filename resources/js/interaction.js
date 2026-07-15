console.log('INTERACTION JS LOADED');

/*
==================================================
Interaktionen
==================================================
*/

function initInteractionMove() {

	if (window.orgmapInitialized.interactionMove) {
		return;
	}

	window.orgmapInitialized.interactionMove = true;

	document.addEventListener('mousemove', function (event) {

		/*
		--------------------------------------------------
		Panning
		--------------------------------------------------
		*/

		if (state.isPanning) {

			window.orgmapView.panX =
				event.clientX - state.panStartX;

			window.orgmapView.panY =
				event.clientY - state.panStartY;

			applyZoom();

			return;
		}

		/*
		--------------------------------------------------
		Resize
		--------------------------------------------------
		*/

		if (state.isResizing) {

			const deltaX =
				(event.clientX - state.dragStartX)
				/ window.orgmapView.zoom;

			const size =
				Math.min(
					MAX_NODE_SIZE,
					Math.max(
						MIN_NODE_SIZE,
						state.resizeStartSize + deltaX
					)
				);

			handleResize(size);

			return;
		}

		/*
		--------------------------------------------------
		Dragging
		--------------------------------------------------
		*/

		if (
			!state.selectedNode
			&& !state.selectedConnectionLabel
		) {
			return;
		}

		const moveDistance =
			Math.sqrt(
				Math.pow(event.clientX - state.dragStartX, 2)
				+ Math.pow(event.clientY - state.dragStartY, 2)
			);

		if (moveDistance < 5) {
			return;
		}

		state.isDragging = true;

		const currentWrapper =
			document.querySelector('.orgmap-wrapper');

		if (!currentWrapper) {
			return;
		}

		const rect =
			currentWrapper.getBoundingClientRect();

		const halfWidth =
			state.selectedNode
				? state.selectedNode.offsetWidth / 2
				: 0;
		
		const halfHeight =
			state.selectedNode
				? state.selectedNode.offsetHeight / 2
				: 0;
		
		const x =
			(event.clientX - rect.left)
			/ window.orgmapView.zoom;

		const y =
			(event.clientY - rect.top)
			/ window.orgmapView.zoom;
			
		if (
			event.altKey
			&& state.selectedConnectionLabel
		) {
		
		const dx =
			event.clientX - state.dragStartX;
		
		const dy =
			event.clientY - state.dragStartY;
		
		state.selectedConnectionLabel.setAttribute(
			'x',
			parseFloat(
				state.selectedConnectionLabel.getAttribute('x')
			) + dx
		);
		
		state.selectedConnectionLabel.setAttribute(
			'y',
			parseFloat(
				state.selectedConnectionLabel.getAttribute('y')
			) + dy
		);
		
		state.selectedConnectionLabel.setAttribute(
			'transform',
			''
		);
		
		state.dragStartX = event.clientX;
		state.dragStartY = event.clientY;
		
		return;
		}
		
		if (state.selectedConnectionLabel) {
		
			return;
		}
		
		/*
		--------------------------------------------------
		ALT = Text bewegen
		--------------------------------------------------
		*/

		if (event.altKey) {

			const label =
				state.selectedNode.querySelector('.org-node-label');

			if (!label) {
				return;
			}

			const rect =
				state.selectedNode.getBoundingClientRect();

			const labelX =
				event.clientX - rect.left;

			const labelY =
				event.clientY - rect.top;

			label.style.left = labelX + 'px';
			label.style.top = labelY + 'px';

			state.selectedNode.dataset.labelX = labelX;
			state.selectedNode.dataset.labelY = labelY;

			label.style.transform = 'none';

			return;
		}

		/*
		--------------------------------------------------
		Node bewegen
		--------------------------------------------------
		*/
	
		handleDrag(
			x,
			y,
			halfWidth,
			halfHeight
		);

	});
}

initInteractionMove();

/*
--------------------------------------------------
Mouse Up
--------------------------------------------------
*/

function initMouseUp() {

	if (window.orgmapInitialized.mouseUp) {
		return;
	}

	window.orgmapInitialized.mouseUp = true;

	function resetInteractionState() {
	
	if (state.selectedConnectionLabel) {
	
		console.log(
			'X:',
			state.selectedConnectionLabel.getAttribute('x')
		);
	
		console.log(
			'Y:',
			state.selectedConnectionLabel.getAttribute('y')
		);
	
			const saveLabelUrl = document.querySelector('.orgmap-wrapper')
				?.dataset.saveLabelUrl;

			if (!saveLabelUrl) {
				state.selectedConnectionLabel = null;
				return;
			}

			fetch(
				saveLabelUrl,
		
			{
				method: 'POST',
	
				headers: {
					'Content-Type': 'application/json',
					'X-CSRF-Token': yii.getCsrfToken()
				},
	
				body: JSON.stringify({
	
					id:
						state.selectedConnectionLabel
							.dataset.connectionId,
	
					label_offset_x:
						parseFloat(
							state.selectedConnectionLabel
								.getAttribute('x')
						),
	
					label_offset_y:
						parseFloat(
							state.selectedConnectionLabel
								.getAttribute('y')
						)
				})
			}
			)
			.then(response => {
				if (!response.ok) {
					throw new Error(`Label konnte nicht gespeichert werden (${response.status}).`);
				}
				return response.json();
			})
			.then(data => {
				if (!data.success) {
					throw new Error('Label konnte nicht gespeichert werden.');
				}
			})
			.catch(error => console.error(error));
	
		state.selectedConnectionLabel = null;
	
		return;
	}

	if (state.isPanning) {

		state.isPanning = false;
	}

if (state.isResizing) {

	state.isResizing = false;

	if (state.selectedNode) {

		savePosition();
	}

	if (state.selectedNode) {
				
			state.selectedNode.style.zIndex =
				state.selectedNode.dataset.originalZ || 1;
		
			state.selectedNode = null;
		}

		return;
	}

	if (!state.isDragging) {

		if (state.selectedNode) {
		
			state.selectedNode.style.zIndex =
				state.selectedNode.dataset.originalZ || 1;
		
			state.selectedNode = null;
		}

		return;
	}

	if (state.selectedNode) {

		savePosition();
	}
	
	state.isDragging = false;

	if (state.selectedNode) {
	
		state.selectedNode.style.zIndex =
			state.selectedNode.dataset.originalZ || 1;
	
		state.selectedNode = null;
	}
}

document.addEventListener(
	'mouseup',
	resetInteractionState
);

window.addEventListener(
	'mouseup',
	resetInteractionState
);
}

initMouseUp();
