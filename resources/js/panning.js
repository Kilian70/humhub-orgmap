/*
==================================================
Panning
==================================================
*/

function initPanning() {

	if (window.orgmapInitialized.panning) {
		return;
	}

	window.orgmapInitialized.panning = true;

	const currentWrapper =
		document.querySelector('.orgmap-wrapper');

	if (!currentWrapper) {
		return;
	}

	currentWrapper.addEventListener('mousedown', function (event) {

		/*
		--------------------------------------------------
		Keine Nodes
		--------------------------------------------------
		*/
		
		if (event.target.closest('.org-node-html')) {
			return;
		}

		/*
		--------------------------------------------------
		Panning starten
		--------------------------------------------------
		*/

		state.isPanning = true;

		state.panStartX =
			event.clientX - window.orgmapView.panX;

		state.panStartY =
			event.clientY - window.orgmapView.panY;
	});

	/*
	--------------------------------------------------
	Sicherheit MouseUp
	--------------------------------------------------
	*/

	document.addEventListener('mouseup', function () {

		if (!state.isPanning) {
			return;
		}

		state.isPanning = false;
	});

	window.addEventListener('mouseup', function () {

		if (!state.isPanning) {
			return;
		}

		state.isPanning = false;
	});
}

initPanning();
