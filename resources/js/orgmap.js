	/*
	--------------------------------------------------
	View Mode Switcher
	--------------------------------------------------
	*/
	
	function initOrgmapViewSwitcher() {
	
		const layout =
			document.querySelector('.orgmap-layout');
	
		const viewButtons =
			document.querySelectorAll('.orgmap-view-btn');
	
		if (!layout || !viewButtons.length) {
			return;
		}
	
		const savedView =
			localStorage.getItem(
				'orgmapViewMode'
			);
	
		if (savedView) {
	
			layout.dataset.viewMode =
				savedView;
		}

		function updateViewState(mode) {
			viewButtons.forEach(button => {
				const isActive = button.dataset.view === mode;
				button.setAttribute('aria-pressed', String(isActive));
				button.classList.toggle('active', isActive);
			});
		}

		function notifyViewChange(mode) {
			/* Die Kartenbreite ist im Navigationsmodus 0, weil .orgmap-main dort
			   ausgeblendet ist. Erst nach zwei Frames ist der neue View-Modus
			   vollständig im Layout angekommen und kann zuverlässig vermessen
			   werden. */
			window.requestAnimationFrame(function () {
				window.requestAnimationFrame(function () {
					document.dispatchEvent(new CustomEvent('orgmap:viewchange', {
						detail: {mode: mode}
					}));
				});
			});
		}

		updateViewState(layout.dataset.viewMode);
	
		viewButtons.forEach(button => {
	
			button.addEventListener('click', () => {
	
				const mode =
					button.dataset.view;
	
				layout.dataset.viewMode =
					mode;

				updateViewState(mode);
	
				localStorage.setItem(
					'orgmapViewMode',
					mode
				);

				notifyViewChange(mode);
			});
		});
	}
	
	initOrgmapViewSwitcher();

	const fullscreenButton = document.getElementById('orgmap-fullscreen');
	if (fullscreenButton && !document.fullscreenEnabled) {
		fullscreenButton.disabled = true;
	}
	
	initIconPicker();
	
	$(document).on(
		'pjax:success',
		function () {
	
			initOrgmapViewSwitcher();
	
			initIconPicker();
		}
	);
	
	document.addEventListener('click', function (event) {
	
		const isEditMode =
			window.location.search.includes(
				'edit=1'
			);
	
		if (!isEditMode) {
			return;
		}
	
		const connection =
			event.target.closest('.org-connection');
	
		if (!connection) {
			return;
		}
	
		document
			.querySelectorAll('.org-connection.selected')
			.forEach(el => {
	
				el.classList.remove('selected');
			});
	
		connection.classList.add('selected');
	});
	
	document.addEventListener('dblclick', function (event) {
	
		const isEditMode =
			window.location.search.includes(
				'edit=1'
			);
	
		if (!isEditMode) {
			return;
		}
	
		const connection =
			event.target.closest(
				'.org-connection, .org-connection-label'
			);
	
		if (!connection) {
			return;
		}
	
		const editUrl = document.querySelector('.orgmap-wrapper')
			?.dataset.editConnectionUrl;

		if (editUrl) {
			const url = new URL(editUrl, window.location.origin);
			url.searchParams.set('id', connection.dataset.connectionId);
			window.location.href = url.toString();
		}
	});	

	document.addEventListener('click', function (event) {
		const button = event.target.closest('#orgmap-fullscreen');
		if (!button) {
			return;
		}

		const layout = document.querySelector('.orgmap-layout')?.closest('.container');
		if (!layout || !document.fullscreenEnabled) {
			return;
		}

		if (document.fullscreenElement) {
			document.exitFullscreen();
		} else {
			layout.requestFullscreen();
		}
	});

	document.addEventListener('click', async function (event) {
		const button = event.target.closest('#orgmap-print');
		if (!button || button.dataset.printPreparing === '1') {
			return;
		}

		button.dataset.printPreparing = '1';
		button.disabled = true;

		try {
			await waitForOrgMapPrintImages();
			prepareOrgMapPrint();
			await waitForOrgMapPaint();
			window.print();
		} finally {
			button.disabled = false;
			delete button.dataset.printPreparing;
		}
	});

	function waitForOrgMapPaint() {
		return new Promise(function (resolve) {
			requestAnimationFrame(function () {
				requestAnimationFrame(resolve);
			});
		});
	}

	async function waitForOrgMapPrintImages() {
		const images = Array.from(
			document.querySelectorAll('.org-node-print-image')
		);

		await Promise.all(images.map(async function (image) {
			if (!image.complete) {
				await new Promise(function (resolve) {
					let finished = false;
					const done = function () {
						if (finished) {
							return;
						}

						finished = true;
						image.removeEventListener('load', done);
						image.removeEventListener('error', done);
						resolve();
					};

					image.addEventListener('load', done, { once: true });
					image.addEventListener('error', done, { once: true });

					/* Verhindert eine Race Condition, falls das Bild genau
					   zwischen der Prüfung und den Listenern fertig wurde. */
					if (image.complete) {
						done();
					}
				});
			}

			if (image.naturalWidth > 0 && typeof image.decode === 'function') {
				try {
					await image.decode();
				} catch (error) {
					/* Bereits geladene Bilder können in einzelnen Browsern
					   decode() ablehnen und sind trotzdem druckbar. */
				}
			}
		}));
	}

	function prepareOrgMapPrint() {
		const wrapper = document.querySelector('.orgmap-wrapper');
		const stage = document.querySelector('.orgmap-stage');

		if (!wrapper || !stage) {
			return;
		}

		/* A4 quer mit 5 mm Seitenrand. Die Überschrift und Werkzeugleiste
		   werden im Druck ausgeblendet, damit die Karte die Seite ausfüllt. */
		const millimetre = 96 / 25.4;
		const printableWidth = 277 * millimetre;
		const printableHeight = 190 * millimetre;
		const workspaceWidth = wrapper.offsetWidth;
		const workspaceHeight = wrapper.offsetHeight;

		if (workspaceWidth <= 0 || workspaceHeight <= 0) {
			return;
		}

		const scale = Math.min(
			printableWidth / workspaceWidth,
			printableHeight / workspaceHeight
		);

		stage.style.setProperty('--orgmap-print-scale', String(scale));
		stage.style.setProperty(
			'--orgmap-print-width',
			`${Math.ceil(workspaceWidth * scale)}px`
		);
		stage.style.setProperty(
			'--orgmap-print-height',
			`${Math.ceil(workspaceHeight * scale)}px`
		);
	}

	window.addEventListener('beforeprint', prepareOrgMapPrint);

	document.addEventListener('fullscreenchange', function () {
		const button = document.querySelector('#orgmap-fullscreen');
		const icon = button?.querySelector('i');
		if (!button || !icon) {
			return;
		}

		icon.classList.toggle('fa-expand', !document.fullscreenElement);
		icon.classList.toggle('fa-compress', Boolean(document.fullscreenElement));
		button.setAttribute('aria-pressed', String(Boolean(document.fullscreenElement)));
		const label = document.fullscreenElement
			? button.dataset.labelExit
			: button.dataset.labelEnter;
		button.setAttribute('aria-label', label);
		button.setAttribute('title', label);

		setTimeout(function () {
			if (typeof fitWorkspace === 'function') {
				fitWorkspace();
				applyZoom();
			}
		}, 50);
	});
