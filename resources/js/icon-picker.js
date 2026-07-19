function initIconPicker() {

	const input =
		document.getElementById(
			'node-icon_class'
		);

	if (!input) {
		return;
	}

	const picker = input.closest('.orgmap-icon-picker');
	if (!picker || picker.dataset.keyboardInitialized === '1') {
		return;
	}
	picker.dataset.keyboardInitialized = '1';

	const panel =
		picker.querySelector(
			'.orgmap-icon-panel'
		);

	const preview =
		picker.querySelector(
			'.orgmap-selected-icon'
		);

	const buttons =
		picker.querySelectorAll(
			'.orgmap-icon-option[data-icon]'
		);

	function selectIcon(button) {

		if (!button) {
			return;
		}

		buttons.forEach(el => {
			el.classList.remove('active');
			el.setAttribute('aria-pressed', 'false');
			el.tabIndex = -1;
		});

		button.classList.add('active');
		button.setAttribute('aria-pressed', 'true');
		button.tabIndex = 0;

		input.value =
			button.dataset.icon;

		if (preview) {
			preview.innerHTML =
				button.innerHTML;
			preview.setAttribute(
				'aria-label',
				button.getAttribute('aria-label') || button.dataset.icon || 'Kein Icon'
			);
		}

		if (panel) {
			panel.hidden = true;
			panel.style.display = 'none';
			openButton?.setAttribute('aria-expanded', 'false');
		}

		openButton?.focus();
	}

	buttons.forEach(button => {

		button.classList.remove('active');
		button.setAttribute('aria-pressed', 'false');
		button.tabIndex = -1;

		if (
				button.dataset.icon === input.value
			) {
			button.classList.add('active');
			button.setAttribute('aria-pressed', 'true');
			button.tabIndex = 0;
		}

		button.onclick = function () {
			selectIcon(this);
		};
	});

	if (!picker.querySelector('.orgmap-icon-option.active') && buttons[0]) {
		buttons[0].tabIndex = 0;
	}

	const openButton =
		picker.querySelector(
			'.orgmap-icon-open'
		);

	if (openButton && panel) {

		openButton.onclick = function () {

			if (
				panel.hidden
				|| panel.style.display === 'none'
				|| panel.style.display === ''
			) {
				panel.hidden = false;
				panel.style.display = 'flex';
				openButton.setAttribute('aria-expanded', 'true');
				const selectedButton = panel.querySelector('.orgmap-icon-option.active') || buttons[0];
				selectedButton?.focus();
			} else {
				panel.hidden = true;
				panel.style.display = 'none';
				openButton.setAttribute('aria-expanded', 'false');
			}
		};
	}

	panel?.addEventListener('keydown', function (event) {
		const current = event.target.closest('.orgmap-icon-option');
		if (!current) {
			return;
		}

		if (event.key === 'Escape') {
			event.preventDefault();
			panel.hidden = true;
			panel.style.display = 'none';
			openButton?.setAttribute('aria-expanded', 'false');
			openButton?.focus();
			return;
		}

		if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'].includes(event.key)) {
			return;
		}

		event.preventDefault();
		const options = Array.from(buttons);
		let nextIndex = options.indexOf(current);

		if (event.key === 'Home') {
			nextIndex = 0;
		} else if (event.key === 'End') {
			nextIndex = options.length - 1;
		} else if (event.key === 'ArrowLeft') {
			nextIndex = Math.max(0, nextIndex - 1);
		} else if (event.key === 'ArrowRight') {
			nextIndex = Math.min(options.length - 1, nextIndex + 1);
		} else {
			const currentTop = current.offsetTop;
			const rowOptions = options.filter(option => option.offsetTop === currentTop);
			const columns = Math.max(1, rowOptions.length);
			nextIndex += event.key === 'ArrowUp' ? -columns : columns;
			nextIndex = Math.max(0, Math.min(options.length - 1, nextIndex));
		}

		options[nextIndex]?.focus();
	});
}

initIconPicker();

$(document).on(
	'pjax:success',
	function () {
		initIconPicker();
	}
);
