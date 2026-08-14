	/*
	==================================================
	SVG Linien
	==================================================
	*/
	
	function updateLines() {
		const svg =
			document.getElementById(
				'orgmap-svg'
			);

	            if (!svg) {
	                return;
	            }

	            const lines =
	                svg.querySelectorAll('path.org-connection');

            lines.forEach(line => {

	                const parentId =
	                    line.dataset.fromNodeId;

	                const childId =
	                    line.dataset.toNodeId;

                const parent =
                    document.querySelector(
                        '.org-node-html[data-id="' + parentId + '"]'
                    );

                const child =
                    document.querySelector(
                        '.org-node-html[data-id="' + childId + '"]'
                    );

                if (!parent || !child) {
                    return;
                }

	                /*
	                 * Die Verbindungen leben im Koordinatensystem des SVG.
	                 * getBoundingClientRect()/getScreenCTM() ist hier nicht
	                 * zuverlässig: Nach dem Rücksprung aus einem Formular
	                 * können Browser den Seiten- und Scrollversatz zu
	                 * unterschiedlichen Zeitpunkten melden. Dadurch wurden
	                 * Endpunkte um die Höhe der HumHub-Kopfzeile verschoben.
	                 *
	                 * data-x/data-y sind die eindeutigen Workspace-Mittelpunkte
	                 * und werden bereits während des Ziehens aktualisiert.
	                 * Auch Breite und Höhe kommen aus data-Werten: Im
	                 * Bearbeitungsmodus darf der zusätzliche Resize-Griff die
	                 * geometrische Kreisgrösse nicht beeinflussen.
	                 */
	                const parentWidth =
	                    parseFloat(parent.dataset.width) || parent.offsetWidth;
	                const parentHeight =
	                    parseFloat(parent.dataset.height) || parent.offsetHeight;
	                const childWidth =
	                    parseFloat(child.dataset.width) || child.offsetWidth;
	                const childHeight =
	                    parseFloat(child.dataset.height) || child.offsetHeight;
	                const parentX = parseFloat(parent.dataset.x);
	                const parentY = parseFloat(parent.dataset.y);
	                const childX = parseFloat(child.dataset.x);
	                const childY = parseFloat(child.dataset.y);

	                if (
	                    !Number.isFinite(parentX)
	                    || !Number.isFinite(parentY)
	                    || !Number.isFinite(childX)
	                    || !Number.isFinite(childY)
	                    || parentWidth <= 0
	                    || parentHeight <= 0
	                    || childWidth <= 0
	                    || childHeight <= 0
	                ) {
	                    return;
	                }

	                const dx = childX - parentX;
	                const dy = childY - parentY;
	                const distance = Math.sqrt(dx * dx + dy * dy);

	                if (distance === 0) {
	                    return;
	                }

	                const parentScale = 1 / Math.sqrt(
	                    (dx * dx) / Math.pow(parentWidth / 2, 2)
	                    + (dy * dy) / Math.pow(parentHeight / 2, 2)
	                );
	                const childScale = 1 / Math.sqrt(
	                    (dx * dx) / Math.pow(childWidth / 2, 2)
	                    + (dy * dy) / Math.pow(childHeight / 2, 2)
	                );

	                const x1 = parentX + dx * parentScale;
	                const y1 = parentY + dy * parentScale;
	                const x2 = childX - dx * childScale;
	                const y2 = childY - dy * childScale;
	                const curve = parseFloat(line.dataset.curve || '0');
	                const midX = (x1 + x2) / 2;
	                const midY = (y1 + y2) / 2;
	                const normalX = -dy / distance;
	                const normalY = dx / distance;
	                const controlX = midX + normalX * curve;
	                const controlY = midY + normalY * curve;

	                line.setAttribute(
	                    'd',
	                    `M ${x1} ${y1} Q ${controlX} ${controlY} ${x2} ${y2}`
	                );

	                const connectionId = line.dataset.connectionId;
	                const label = svg.querySelector(
	                    `text.org-connection-label[data-connection-id="${connectionId}"]`
	                );

	                if (label) {
	                    const offsetX = parseFloat(label.dataset.offsetX || '0');
	                    const offsetY = parseFloat(label.dataset.offsetY || '0');

	                    if (offsetX === 0 && offsetY === 0) {
	                        const labelX = 0.25 * x1 + 0.5 * controlX + 0.25 * x2;
	                        const labelY = 0.25 * y1 + 0.5 * controlY + 0.25 * y2;
	                        label.setAttribute('x', labelX);
	                        label.setAttribute('y', labelY);

	                        if (label.dataset.labelRotation === 'auto') {
	                            let angle = Math.atan2(dy, dx) * 180 / Math.PI;
	                            if (angle > 90 || angle < -90) {
	                                angle += 180;
	                            }
	                            label.setAttribute(
	                                'transform',
	                                `rotate(${angle}, ${labelX}, ${labelY})`
	                            );
	                        } else {
	                            label.removeAttribute('transform');
	                        }
	                    }
	                }
	            });
	        }

	/*
	--------------------------------------------------
	Linien nach jedem relevanten Layoutwechsel erneuern
	--------------------------------------------------

	Die vom Server ausgegebenen Pfade sind nur eine sichere
	Startdarstellung. Erst der Browser kennt die endgültigen
	Knotengrössen. Besonders nach Cache-Neuaufbau, PJAX oder
	dem Wechsel aus dem Bearbeitungsmodus muss deshalb noch
	einmal anhand des fertigen DOM gerechnet werden.
	*/
	function scheduleLineUpdate() {
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(updateLines);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', scheduleLineUpdate, {once: true});
	} else {
		scheduleLineUpdate();
	}

	window.addEventListener('load', scheduleLineUpdate);
	window.addEventListener('pageshow', scheduleLineUpdate);
	window.addEventListener('resize', scheduleLineUpdate);
	document.addEventListener('fullscreenchange', scheduleLineUpdate);

	if (window.ResizeObserver) {
		const lineLayoutObserver = new ResizeObserver(scheduleLineUpdate);
		const mapScroll = document.querySelector('.orgmap-scroll');
		const mapWrapper = document.querySelector('.orgmap-wrapper');
		if (mapScroll) {
			lineLayoutObserver.observe(mapScroll);
		}
		if (mapWrapper) {
			lineLayoutObserver.observe(mapWrapper);
		}
	}

	document.addEventListener('click', function (event) {
		if (event.target.closest('.orgmap-view-btn')) {
			scheduleLineUpdate();
		}
	});

	if (window.jQuery) {
		window.jQuery(document).on('pjax:success', scheduleLineUpdate);
	}
