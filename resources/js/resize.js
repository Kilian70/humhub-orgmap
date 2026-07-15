		console.log('RESIZE JS LOADED');
		
		/*
		--------------------------------------------------
		Resize 
		--------------------------------------------------
		*/

		function handleResize(size) {

			state.selectedNode.style.width =
				size + 'px';
		
			state.selectedNode.style.height =
				size + 'px';
		
			const handle =
				state.selectedNode.querySelector('.org-node-resize');
		
			if (handle) {
		
				const handleSize =
					Math.max(12, size * 0.08);
		
				handle.style.width =
					handleSize + 'px';
		
				handle.style.height =
					handleSize + 'px';
			}
		
			state.selectedNode.dataset.radius =
				size / 2;
			
			state.selectedNode.dataset.width =
				size;
			
			state.selectedNode.dataset.height =
				size;
			
			updateLines();
		}