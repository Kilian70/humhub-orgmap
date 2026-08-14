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
					updateZoomControls(true);
			}
	
			if (event.target.closest('#zoom-out')) {
	
				window.orgmapView.zoom -= 0.1;
	
				if (window.orgmapView.zoom < 0.3) {
					window.orgmapView.zoom = 0.3;
				}
	
					applyZoom();
					updateZoomControls(true);
			}
			});

			updateZoomControls(false);
		}

		function updateZoomControls(announce) {
			const zoom = window.orgmapView?.zoom;
			if (typeof zoom !== 'number') {
				return;
			}

			const zoomIn = document.getElementById('zoom-in');
			const zoomOut = document.getElementById('zoom-out');
			if (zoomIn) {
				zoomIn.disabled = zoom >= 2.999;
			}
			if (zoomOut) {
				zoomOut.disabled = zoom <= 0.301;
			}

			if (announce) {
				const status = document.getElementById('orgmap-zoom-status');
				if (status) {
					const label = status.dataset.label || 'Zoom';
					status.textContent = `${label}: ${Math.round(zoom * 100)} %`;
				}
			}
		}
	
	initZoom();

	let layoutResizeTimer = null;

	function runWorkspaceFit() {
		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				fitWorkspace();
				applyZoom();

				if (typeof updateLines === 'function') {
					updateLines();
				}
			});
		});
	}

	function scheduleWorkspaceFit(delay = 0) {
		window.setTimeout(runWorkspaceFit, delay);
	}

	function scheduleWorkspaceFitAfterResize() {
		window.clearTimeout(layoutResizeTimer);
		layoutResizeTimer = window.setTimeout(runWorkspaceFit, 120);
	}

	function watchMapImages() {
		document
			.querySelectorAll('.org-node-print-image')
			.forEach(function (image) {
				if (image.complete) {
					return;
				}

				image.addEventListener('load', runWorkspaceFit, {once: true});
				image.addEventListener('error', runWorkspaceFit, {once: true});
			});
	}

	/* HumHub, Webfonts und Bilder können die endgültige Grösse zeitversetzt
	   liefern. Mehrere kurze Messpunkte verhindern die falsche Erstposition. */
	watchMapImages();
	scheduleWorkspaceFit(0);
	scheduleWorkspaceFit(150);
	scheduleWorkspaceFit(500);

	window.addEventListener('load', runWorkspaceFit, {once: true});
	window.addEventListener('resize', scheduleWorkspaceFitAfterResize);

	if (window.visualViewport) {
		window.visualViewport.addEventListener(
			'resize',
			scheduleWorkspaceFitAfterResize
		);
	}

	if (document.fonts && document.fonts.ready) {
		document.fonts.ready.then(runWorkspaceFit);
	}

	/*
	--------------------------------------------------
	Workspace automatisch einpassen
	--------------------------------------------------
	*/
	
	function fitWorkspace() {
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

	if (availableWidth <= 0 || mapBounds.width <= 0 || mapBounds.height <= 0) {
		return;
	}

	const isMobile = window.innerWidth < 768;
	const cameraPadding = isMobile ? 12 : 40;
	const widthZoom =
		(availableWidth - cameraPadding * 2) / mapBounds.width;
	let zoom = widthZoom;

	if (!isMobile && availableHeight > cameraPadding * 2) {
		zoom = Math.min(
			widthZoom,
			(availableHeight - cameraPadding * 2) / mapBounds.height
		);
	}

	window.orgmapView.zoom =
		Math.max(0.20, Math.min(zoom, 1));

	
	const renderedWidth = mapBounds.width * window.orgmapView.zoom;
	const renderedHeight = mapBounds.height * window.orgmapView.zoom;
	const horizontalOffset = Math.max(
		cameraPadding,
		(availableWidth - renderedWidth) / 2
	);
	/* Auf Smartphones ist der Scrollbereich oft wesentlich höher als der
	   sichtbare Viewport. Vertikales Zentrieren würde die Karte deshalb weit
	   nach unten verschieben. Mobil immer oben beginnen; Desktop bleibt
	   innerhalb der verfügbaren Fläche vertikal zentriert. */
	const verticalOffset = isMobile
		? cameraPadding
		: Math.max(
			cameraPadding,
			(availableHeight - renderedHeight) / 2
		);

	/* Kleine Karteninhalte (zum Beispiel der erste neu erstellte Kreis)
	   in der sichtbaren Fläche zentrieren. Grosse Karten behalten den
	   Sicherheitsabstand zum oberen und linken Rand. */
	window.orgmapView.panX =
		horizontalOffset
		- (mapBounds.minX * window.orgmapView.zoom);

	window.orgmapView.panY =
		verticalOffset
		- (mapBounds.minY * window.orgmapView.zoom);

	/* CSS-Transforms verkleinern nur die Darstellung, nicht die im Dokument
	   reservierte Höhe des Workspace. Auf Mobilgeräten würde deshalb unter
	   der Karte die komplette unskalierte Resthöhe als Leerraum bleiben. */
	if (isMobile) {
		scroll.style.height = Math.ceil(
			renderedHeight + (cameraPadding * 2)
		) + 'px';
	} else {
		scroll.style.height = '';
	}
	
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

			updateZoomControls(false);
		
		}
	

	/*
	--------------------------------------------------
	PJAX Reload
	--------------------------------------------------
	*/
	
	$(document).on('pjax:success', function () {
		watchMapImages();
		scheduleWorkspaceFit(0);
		scheduleWorkspaceFit(150);
	});
