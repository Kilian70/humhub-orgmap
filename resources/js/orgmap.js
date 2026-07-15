	console.log('ORGMAP JS LOADED');
	
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
	
		viewButtons.forEach(button => {
	
			button.addEventListener('click', () => {
	
				const mode =
					button.dataset.view;
	
				layout.dataset.viewMode =
					mode;
	
				localStorage.setItem(
					'orgmapViewMode',
					mode
				);
			});
		});
	}
	
	initOrgmapViewSwitcher();
	
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

	document.addEventListener('click', function (event) {
		if (!event.target.closest('#orgmap-print')) {
			return;
		}

		window.print();
	});

	document.addEventListener('fullscreenchange', function () {
		const icon = document.querySelector('#orgmap-fullscreen i');
		if (!icon) {
			return;
		}

		icon.classList.toggle('fa-expand', !document.fullscreenElement);
		icon.classList.toggle('fa-compress', Boolean(document.fullscreenElement));

		setTimeout(function () {
			if (typeof fitWorkspace === 'function') {
				fitWorkspace();
				applyZoom();
			}
		}, 50);
	});
