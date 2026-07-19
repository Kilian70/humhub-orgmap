	const state = {
	
		selectedNode: null,
		
		selectedConnectionLabel: null,
	
		isDragging: false,
	
		isResizing: false,
	
		isPanning: false,
	
		panStartX: 0,
		panStartY: 0,
	
		dragStartX: 0,
		dragStartY: 0,
	
		resizeStartSize: 0
	};
	
	const MIN_NODE_SIZE = 60;
	
	const MAX_NODE_SIZE = 600;

	/*
	--------------------------------------------------
	Z-Index Ebenen
	--------------------------------------------------
	*/
	
	const Z_LAYERS = {
	
		background: 1,
	
		lines: 10,
	
		core: 100,
	
		organ: 200,
	
		space: 300,
	
		tool: 400,
	
		external: 500,
	
		label: 1000,
	
		active: 5000,
	
		dragging: 9999
	};
	
	/*
	--------------------------------------------------
	Workspace / Kamera
	--------------------------------------------------
	*/
	
	const WORKSPACE_SIZE =
		document.querySelector('.orgmap-wrapper')
		?.dataset.workspaceSize || 'medium';
	
	const WRAPPER =
	document.querySelector('.orgmap-wrapper');

	const SCROLL =
		document.querySelector('.orgmap-scroll');
	
	const WORKSPACE_WIDTH =
		WRAPPER.offsetWidth;
	
	const WORKSPACE_HEIGHT =
		WRAPPER.offsetHeight;
	
	const AVAILABLE_WIDTH =
		SCROLL.clientWidth;
	
	const AVAILABLE_HEIGHT =
		SCROLL.clientHeight;
		
	
	/*
	--------------------------------------------------
	Einheitliche Kamera
	--------------------------------------------------
	*/
	
	let DEFAULT_ZOOM = 0.6;

	if (WORKSPACE_SIZE === 'small') {
	
		DEFAULT_ZOOM = 0.8;
	}
	
	if (WORKSPACE_SIZE === 'medium') {
	
		DEFAULT_ZOOM = 0.7;
	}
	
	if (WORKSPACE_SIZE === 'large') {
	
		DEFAULT_ZOOM = 0.6;
	}
	
	const DEFAULT_PAN_X = 0;
	
	const DEFAULT_PAN_Y = 0;
	
	/*
	--------------------------------------------------
	Mobile nutzt dieselbe Kamera
	--------------------------------------------------
	*/
	
	const MOBILE_ZOOM = 0.45;
	
	const MOBILE_PAN_X = 0;
	
	const MOBILE_PAN_Y = 0;
	
	const MOBILE_OFFSET_X = 0;
	
	const MOBILE_OFFSET_Y = 0;
	
	/*
	--------------------------------------------------
	Global View State
	--------------------------------------------------
	*/
	
	window.orgmapView = {
	
		zoom: DEFAULT_ZOOM,
	
		panX: DEFAULT_PAN_X,
	
		panY: DEFAULT_PAN_Y
	};
	
	/*
--------------------------------------------------
Auto-Fit Bounds
--------------------------------------------------
*/

function getMapBounds() {

	const background =
		document.querySelector(
			'.org-node-is-background'
		);

	/*
	--------------------------------------------------
	Hintergrund verwenden
	--------------------------------------------------
	*/

	if (background) {

		const width =
			background.offsetWidth;

		const height =
			background.offsetHeight;

		const centerX =
			parseFloat(background.dataset.x);

		const centerY =
			parseFloat(background.dataset.y);

		return {

			minX:
				centerX - (width / 2),

			minY:
				centerY - (height / 2),

			maxX:
				centerX + (width / 2),

			maxY:
				centerY + (height / 2),

			width,

			height
		};
	}

	return getContentBounds();
}


/*
--------------------------------------------------
Bounds der sichtbaren Nodes
--------------------------------------------------
*/

function getContentBounds() {

	const nodes =
		document.querySelectorAll(
			'.org-node-html:not(.org-node-is-background)'
		);

	if (!nodes.length) {
		return null;
	}

	let minX = Infinity;
	let minY = Infinity;

	let maxX = -Infinity;
	let maxY = -Infinity;

	nodes.forEach(node => {

		const x =
			parseFloat(node.dataset.x);

		const y =
			parseFloat(node.dataset.y);

		const width =
			node.offsetWidth;

		const height =
			node.offsetHeight;

		minX = Math.min(
			minX,
			x - (width / 2)
		);

		minY = Math.min(
			minY,
			y - (height / 2)
		);

		maxX = Math.max(
			maxX,
			x + (width / 2)
		);

		maxY = Math.max(
			maxY,
			y + (height / 2)
		);
	});

	return {

		minX,
		minY,
		maxX,
		maxY,

		width:
			maxX - minX,

		height:
			maxY - minY
	};
}
		
	/*
	--------------------------------------------------
	Mobile Startansicht
	--------------------------------------------------
	*/
	
	if (window.innerWidth < 768) {
	
		window.orgmapView.zoom = MOBILE_ZOOM;
	
		window.orgmapView.panX = MOBILE_PAN_X;
	
		window.orgmapView.panY = MOBILE_PAN_Y;
	
		const bounds =
			getMapBounds();
	
		if (bounds) {
	
			const padding = 0;
	
			const zoomX =
				(window.innerWidth - padding)
				/ bounds.width;
	
			const zoomY =
				(window.innerHeight - padding)
				/ bounds.height;
	
			window.orgmapView.zoom =
			Math.max(
				0.20,
				Math.min(zoomX, zoomY)
			);
	
		}
	
	/*
	--------------------------------------------------
	Auto Center
	--------------------------------------------------
	*/
	
	if (bounds) {

		window.orgmapView.panX =
			(window.innerWidth - (bounds.width * window.orgmapView.zoom)) / 2
			- (bounds.minX * window.orgmapView.zoom);
	
		window.orgmapView.panY =
			40
			- (bounds.minY * window.orgmapView.zoom);
	
	}
	
	}
	
	/*
	--------------------------------------------------
	Initialisierung
	--------------------------------------------------
	*/
	
	window.orgmapInitialized =
	window.orgmapInitialized || {
	
		dragging: false,
	
		interactionMove: false,
	
		mouseUp: false,
	
		panning: false,
	
		zoom: false
	};
