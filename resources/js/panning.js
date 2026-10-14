/*
==================================================
Panning
==================================================
*/

function initPanning() {

    const currentWrapper = document.querySelector('.orgmap-wrapper');
    if (!currentWrapper || currentWrapper.dataset.orgmapPanning === '1') { return; }
    currentWrapper.dataset.orgmapPanning = '1';

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

}

initPanning();
$(document).on('pjax:success', initPanning);
