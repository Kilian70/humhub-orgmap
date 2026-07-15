	/*
	--------------------------------------------------
	Tree Active State
	--------------------------------------------------
	*/
	
	function initTreeNavigation() {

document.addEventListener(
	'mouseenter',
	function (e) {

		const link =
			e.target.closest('.tree-link');

		if (!link) {
			return;
		}
		
		const wrapper =
			document.querySelector(
				'.orgmap-wrapper'
			);
		
		if (wrapper) {
		
			wrapper.classList.add(
				'tree-hover-active'
			);
		}

		const nodeId =
			link.dataset.nodeId;

		const targetNode =
			document.querySelector(
				'.org-node-html[data-id="' +
				nodeId +
				'"]'
			);

		document
			.querySelectorAll('.org-node-html')
			.forEach(node => {

				node.classList.remove(
					'tree-selected-node'
				);
			});

			if (targetNode) {

			targetNode.classList.add(
				'tree-selected-node'
			);
		}
	},
	true
);

document.addEventListener(
	'mouseleave',
	function (e) {

		const link =
			e.target.closest('.tree-link');

		if (!link) {
			return;
		}
		
		const wrapper =
			document.querySelector(
				'.orgmap-wrapper'
			);
		
		if (wrapper) {
		
			wrapper.classList.remove(
				'tree-hover-active'
			);
		}

		document
			.querySelectorAll('.org-node-html')
			.forEach(node => {

				node.classList.remove(
					'tree-selected-node'
				);
			});
	},
	true
);

document.addEventListener(
	'click',
	function (e) {

		const link =
			e.target.closest('.tree-link');

		if (!link) {
			return;
		}

			document
				.querySelectorAll('.tree-card')
			.forEach(item => {

				item.classList.remove(
					'active'
				);
			});

		const parent =
			link.closest('.tree-card');

		if (parent) {

			parent.classList.add(
				'active'
			);
		}

		const nodeId =
			link.dataset.nodeId;

		if (nodeId) {
			localStorage.setItem('orgmapActiveNodeId', nodeId);
		}

		const targetNode =
			document.querySelector(
				'.org-node-html[data-id="' +
				nodeId +
				'"]'
			);

		document
			.querySelectorAll('.org-node-html')
			.forEach(node => {

				node.classList.remove(
					'tree-selected-node'
				);
			});

		if (targetNode) {

			targetNode.classList.add(
				'tree-selected-node'
			);

				targetNode.focus();

			}

			const href = link.getAttribute('href');
			if (
				href
				&& href !== '#'
				&& e.button === 0
				&& !e.ctrlKey
				&& !e.metaKey
				&& !e.shiftKey
				&& !e.altKey
			) {
				e.preventDefault();
				if (link.target === '_blank') {
					window.open(link.href, '_blank', 'noopener,noreferrer');
				} else {
					window.location.assign(link.href);
				}
			}
		}
);

document.addEventListener('focusin', function (event) {
	const link = event.target.closest('.tree-link');
	if (!link) {
		return;
	}

	document.querySelectorAll('.tree-card').forEach(card => {
		card.classList.remove('active');
	});
	link.closest('.tree-card')?.classList.add('active');
});

}

function restoreActiveTreeCard() {
	const activeNodeId = localStorage.getItem('orgmapActiveNodeId');
	if (!activeNodeId) {
		return;
	}

	document.querySelectorAll('.tree-card').forEach(card => {
		card.classList.remove('active');
	});
	document.querySelectorAll('.tree-link[aria-current]').forEach(link => {
		link.removeAttribute('aria-current');
	});

	const activeLink = Array.from(
		document.querySelectorAll('.tree-link[data-node-id]')
	).find(link => link.dataset.nodeId === activeNodeId);

	if (activeLink) {
		const activeCard = activeLink.closest('.tree-card');
		activeCard?.classList.add('active');
		activeLink.setAttribute('aria-current', 'true');
	}
}

window.addEventListener('pageshow', function () {
	restoreActiveTreeCard();
});


/*
--------------------------------------------------
Tree Toggle
--------------------------------------------------
*/

function initTreeToggle() {

	const toggles =
		document.querySelectorAll('.tree-toggle');

	const collapsedGroups =
		JSON.parse(
			localStorage.getItem(
				'orgmapCollapsedGroups'
			) || '[]'
		);

	toggles.forEach(toggle => {

		const organId =
			toggle.dataset.organId;

		const content =
			document.querySelector(
				'[data-organ-content="' +
				organId +
				'"]'
			);

		if (!content) {
			return;
		}

		if (
			collapsedGroups.includes(
				organId
			)
		) {

			content.classList.add(
				'is-collapsed'
			);

			toggle.classList.add(
				'is-collapsed'
			);

		} else {

			content.classList.remove(
				'is-collapsed'
			);

			toggle.classList.remove(
				'is-collapsed'
			);
		}

		toggle.setAttribute(
			'aria-expanded',
			String(!content.classList.contains('is-collapsed'))
		);
	});
}

/*
--------------------------------------------------
Delegated Toggle Click
--------------------------------------------------
*/

document.addEventListener(
	'click',
	function (e) {

		const toggle =
			e.target.closest(
				'.tree-toggle'
			);

		if (!toggle) {
			return;
		}

		const organId =
			toggle.dataset.organId;

		const content =
			document.querySelector(
				'[data-organ-content="' +
				organId +
				'"]'
			);

		if (!content) {
			return;
		}

		content.classList.toggle(
			'is-collapsed'
		);

		toggle.classList.toggle(
			'is-collapsed'
		);

		toggle.setAttribute(
			'aria-expanded',
			String(!content.classList.contains('is-collapsed'))
		);

		let groups =
			JSON.parse(
				localStorage.getItem(
					'orgmapCollapsedGroups'
				) || '[]'
			);

		if (
			content.classList.contains(
				'is-collapsed'
			)
		) {

			if (
				!groups.includes(
					organId
				)
			) {

				groups.push(
					organId
				);
			}

		} else {

			groups =
				groups.filter(
					id => id !== organId
				);
		}

		localStorage.setItem(
			'orgmapCollapsedGroups',
			JSON.stringify(groups)
		);
	}
);

/*
--------------------------------------------------
Expand All
--------------------------------------------------
*/

document.addEventListener(
	'click',
	function (e) {

		if (
			e.target.id !==
			'tree-expand-all'
		) {
			return;
		}

		document
			.querySelectorAll(
				'.tree-organ-content'
			)
			.forEach(content => {

				content.classList.remove(
					'is-collapsed'
				);
			});

		document
			.querySelectorAll(
				'.tree-toggle'
			)
			.forEach(toggle => {

				toggle.classList.remove(
					'is-collapsed'
				);
			});

		localStorage.setItem(
			'orgmapCollapsedGroups',
			JSON.stringify([])
		);
	}
);

/*
--------------------------------------------------
Collapse All
--------------------------------------------------
*/

document.addEventListener(
	'click',
	function (e) {

		if (
			e.target.id !==
			'tree-collapse-all'
		) {
			return;
		}

		const groups = [];

		document
			.querySelectorAll(
				'.tree-organ-content'
			)
			.forEach(content => {

				content.classList.add(
					'is-collapsed'
				);

				groups.push(
					content.dataset.organContent
				);
			});

		document
			.querySelectorAll(
				'.tree-toggle'
			)
			.forEach(toggle => {

				toggle.classList.add(
					'is-collapsed'
				);
			});

		localStorage.setItem(
			'orgmapCollapsedGroups',
			JSON.stringify(groups)
		);
	}
);


/*
--------------------------------------------------
Tree Search
--------------------------------------------------
*/

document.addEventListener(
	'input',
	function (e) {

		if (
			e.target.id !==
			'tree-search'
		) {
			return;
		}

		const value =
			e.target.value
			.toLowerCase();

		document
			.querySelectorAll('.tree-card')
			.forEach(card => {
		
				const text =
					card.dataset.search || '';
		
				const group =
					card.closest(
						'.tree-organ-content'
					);
		
				if (
					text.includes(value)
				) {
		
					card.style.display =
						'';
						
				if (group) {
				
					group.style.display =
						'';
				
					group.classList.remove(
						'is-collapsed'
					);
				
					group.classList.remove(
						'search-hidden'
					);
				}
		
				} else {
		
					card.style.display =
						'none';
		
					if (group) {
		
						const visibleCards =
							group.querySelectorAll(
								'.tree-card:not([style*="display: none"])'
							).length;
		
						if (visibleCards === 0) {
		
							group.classList.add(
								'search-hidden'
							);
						}
					}
					}
				});

			const visibleCount = Array.from(
				document.querySelectorAll('.tree-card')
			).filter(card => card.style.display !== 'none').length;
			const status = document.getElementById('tree-search-status');
			if (status) {
				status.textContent = value ? `Treffer: ${visibleCount}` : '';
			}
	}
);

document.addEventListener('keydown', function (event) {
	const search = document.getElementById('tree-search');
	if (event.key !== 'Escape' || !search || document.activeElement !== search) {
		return;
	}

	search.value = '';
	search.dispatchEvent(new Event('input', {bubbles: true}));
});

document.addEventListener('keydown', function (event) {
	const toggle = event.target.closest('.tree-toggle');
	if (!toggle || (event.key !== 'Enter' && event.key !== ' ')) {
		return;
	}

	event.preventDefault();
	toggle.click();
});

document.addEventListener('keydown', function (event) {
	const currentLink = event.target.closest('.tree-link');
	if (!currentLink || !['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
		return;
	}

	const visibleLinks = Array.from(
		document.querySelectorAll('.tree-link')
	).filter(link => {
		const card = link.closest('.tree-card');
		const group = link.closest('.tree-organ-content');
		return card
			&& card.style.display !== 'none'
			&& (!group || (!group.classList.contains('is-collapsed') && !group.classList.contains('search-hidden')));
	});

	if (!visibleLinks.length) {
		return;
	}

	event.preventDefault();
	let nextIndex = visibleLinks.indexOf(currentLink);
	if (event.key === 'ArrowDown') {
		nextIndex = Math.min(nextIndex + 1, visibleLinks.length - 1);
	} else if (event.key === 'ArrowUp') {
		nextIndex = Math.max(nextIndex - 1, 0);
	} else if (event.key === 'Home') {
		nextIndex = 0;
	} else if (event.key === 'End') {
		nextIndex = visibleLinks.length - 1;
	}

	visibleLinks[nextIndex]?.focus();
});

/*
--------------------------------------------------
Init
--------------------------------------------------
*/

initTreeNavigation();
initTreeToggle();
restoreActiveTreeCard();

$(document).on(
	'pjax:success',
	function () {

		initTreeNavigation();
		initTreeToggle();
		restoreActiveTreeCard();
	}
);
