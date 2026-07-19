const svg =
	document.getElementById(
		'orgmap-svg'
	);

	/*
	==================================================
	SVG Linien
	==================================================
	*/
	
	function updateLines() {

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

	                const parentRadiusX = parent.offsetWidth / 2;
	                const parentRadiusY = parent.offsetHeight / 2;
	                const childRadiusX = child.offsetWidth / 2;
	                const childRadiusY = child.offsetHeight / 2;

                const parentX =
                    parseFloat(parent.dataset.x);

                const parentY =
                    parseFloat(parent.dataset.y);

                const childX =
                    parseFloat(child.dataset.x);

                const childY =
                    parseFloat(child.dataset.y);

                const dx =
                    childX - parentX;

                const dy =
                    childY - parentY;

                const distance =
                    Math.sqrt(dx * dx + dy * dy);

                if (distance === 0) {
                    return;
                }

	                const parentScale = 1 / Math.sqrt(
	                    (dx * dx) / (parentRadiusX * parentRadiusX)
	                    + (dy * dy) / (parentRadiusY * parentRadiusY)
	                );
	                const childScale = 1 / Math.sqrt(
	                    (dx * dx) / (childRadiusX * childRadiusX)
	                    + (dy * dy) / (childRadiusY * childRadiusY)
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
