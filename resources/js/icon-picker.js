console.log('ICON PICKER LOADED');

function initIconPicker() {

	const input =
		document.getElementById(
			'node-icon_class'
		);

	if (!input) {
		return;
	}

	const panel =
		document.querySelector(
			'.orgmap-icon-panel'
		);

	const preview =
		document.querySelector(
			'.orgmap-selected-icon'
		);

	const buttons =
		document.querySelectorAll(
			'.orgmap-icon-option[data-icon]'
		);

	function selectIcon(button) {

		if (!button) {
			return;
		}

		buttons.forEach(el => {
			el.classList.remove('active');
		});

		button.classList.add('active');

		input.value =
			button.dataset.icon;

		if (preview) {
			preview.innerHTML =
				button.innerHTML;
		}

		if (panel) {
			panel.hidden = true;
			panel.style.display = 'none';
		}
	}

	buttons.forEach(button => {

		button.classList.remove('active');

		if (
			button.dataset.icon === input.value
		) {
			button.classList.add('active');
		}

		button.onclick = function () {
			selectIcon(this);
		};
	});

	const openButton =
		document.querySelector(
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
			} else {
				panel.hidden = true;
				panel.style.display = 'none';
			}
		};
	}
}

initIconPicker();

$(document).on(
	'pjax:success',
	function () {
		initIconPicker();
	}
);