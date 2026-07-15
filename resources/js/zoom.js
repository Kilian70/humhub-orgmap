	console.log('ZOOM JS LOADED');
	
	/*
	--------------------------------------------------
	Zoom 
	--------------------------------------------------
	*/	
	
	
	function initZoom() {
	
		if (window.orgmapInitialized.zoom) {
			return;
		}
	
		window.orgmapInitialized.zoom = true;
	
		document.addEventListener('click', function (event) {
	
			if (event.target.closest('#zoom-in')) {
	
				window.orgmapView.zoom += 0.1;
	
				if (window.orgmapView.zoom > 3) {
					window.orgmapView.zoom = 3;
				}
	
				applyZoom();
			}
	
			if (event.target.closest('#zoom-out')) {
	
				window.orgmapView.zoom -= 0.1;
	
				if (window.orgmapView.zoom < 0.3) {
					window.orgmapView.zoom = 0.3;
				}
	
				applyZoom();
			}
		});
	}
	
	initZoom();
	
	setTimeout(function () {

		if (window.innerWidth >= 768) {
	
			fitWorkspace();
		}
	
		applyZoom();
	
	}, 50);

	/*
	--------------------------------------------------
	Workspace automatisch einpassen
	--------------------------------------------------
	*/
	
	function fitWorkspace() {

	if (window.innerWidth < 768) {
		return;
	}

	const scroll =
		document.querySelector('.orgmap-scroll');

	if (!scroll) {
		return;
	}

	const mapBounds =
		getMapBounds();
	
	if (!mapBounds) {
		return;
	}
	const availableWidth =
		scroll.clientWidth;
	
	const availableHeight =
		scroll.clientHeight;
	
	const CAMERA_PADDING = 40;
	
	const zoom = Math.min(
	
		(availableWidth - CAMERA_PADDING * 2)
		/ mapBounds.width,
	
		(availableHeight - CAMERA_PADDING * 2)
		/ mapBounds.height
	);

	window.orgmapView.zoom =
		Math.min(zoom, 1);

	
	window.orgmapView.panX =
		CAMERA_PADDING
		- (mapBounds.minX * window.orgmapView.zoom);
	
	window.orgmapView.panY =
		CAMERA_PADDING
	- (mapBounds.minY * window.orgmapView.zoom);
	
	}
		
	/*
	--------------------------------------------------
	Zoom anwenden
	--------------------------------------------------
	*/
	
	function applyZoom() {
	
		const wrapper =
			document.querySelector('.orgmap-wrapper');
	
		if (!wrapper) {
			return;
		}
	
		/*
		--------------------------------------------------
		Transform vollständig zurücksetzen
		--------------------------------------------------
		*/
	
		wrapper.style.transform = '';
		
		/*
		Force Reflow für Safari / PJAX
		*/
		
		wrapper.offsetHeight;
	
		wrapper.style.transformOrigin =
			'top left';
	
		wrapper.style.transform =
			'translate('
			+ window.orgmapView.panX + 'px, '
			+ window.orgmapView.panY + 'px)'
			+ ' scale(' + window.orgmapView.zoom + ')';
		
		}
	

	/*
	--------------------------------------------------
	PJAX Reload
	--------------------------------------------------
	*/
	
	$(document).on('pjax:success', function () {
	
		setTimeout(function () {
	
			fitWorkspace();
	
			applyZoom();
	
		}, 50);
	});
