<?php $session = session(); ?>	

<script>
	// Thanks to https://github.com/timmywil/panzoom/
	
	// debug with console.log('message'); or window.alert('message');

	// HTML elements to hold Panzoom

	// panzoom must be in global scope
	
	// get vars to control panzoom and zoomLock
	var zoomLock = <?php echo json_encode($session->zoom_lock); ?>;
	var cycleCode = <?php echo json_encode($session->BMD_cycle_code); ?>;
	var calibStage = <?php echo json_encode($session->calibrate); ?>;
	var verifytranscribeCalibrate = <?php echo json_encode($session->verifytranscribe_calibrate); ?>;
	var scrollStep = <?php echo json_encode($session->current_transcription[0]['BMD_image_scroll_step']); ?>;
	var lastEl = <?php echo json_encode($session->lastEl); ?>;
	var panzoomID = document.getElementById("panzoom");
	var client_x = panzoomID.clientWidth;
	var client_y = panzoomID.clientHeight;
	var calib_x = <?php echo json_encode($session->current_transcription[0]['header_x']); ?>;
	var calib_y = <?php echo json_encode($session->current_transcription[0]['header_y']); ?>;
	var defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
	var defUpdateflag = <?php echo json_encode($session->def_update_flag); ?>;
	const position = { x: 0, y: 0 };
	
	// set resize edges
	var edgeL = true;
	var edgeR = true;
	var edgeT = false;
	var edgeB = false;

	// reset resize edges if in calibrate stage 1
	if ( calibStage == 1 )
		{
			var edgeL = false;
			var edgeR = false;
			var edgeT = false;
			var edgeB = true;
		}
		
	// get panzoom x,y,z
	var panzoom_x = <?php echo json_encode($session->panzoom_x); ?>;
	var panzoom_y = <?php echo json_encode($session->panzoom_y); ?>;
	var panzoom_z = <?php echo json_encode($session->panzoom_z); ?>;
	
	// calculate x and y if in INPRO and new transcription
	if ( cycleCode == 'INPRO' && lastEl.length === 0 && defUpdateflag == 0)
		{
			// calculate panzoom x
			var header_panzoom_x = <?php echo json_encode($session->current_transcription[0]['BMD_panzoom_x']); ?>;
			var panzoom_x = header_panzoom_x * client_x / calib_x;

			// calculate panzoom y
			var header_panzoom_y = <?php echo json_encode($session->current_transcription[0]['BMD_panzoom_y']); ?>;
			var panzoom_y = (header_panzoom_y * client_y / calib_y) + (scrollStep * panzoom_z);
			
			// calculate and apply field width
			for (let i = 0; i < defFields.length; i++) 
				{
					var element = document.getElementById(defFields[i].html_id);
					var newWidth = defFields[i].column_width * client_x / calib_x;
					element.style.width = newWidth+'px';
					
					updateDeffields(defFields, element.id, newWidth);
				}
		}

	// has user applied zoom
	if ( document.getElementById("input-zoom") )
		{
			document.getElementById("input-zoom").oninput = function() 
				{
					panzoom_z = $("#input-zoom").val();
					panzoom.zoom(parseFloat(panzoom_z));
					panzoom.pan(parseFloat(panzoom_x), parseFloat(panzoom_y));
				}
		}
	
	// get html 
	const panzoomElementWrapper = document.querySelector(".panzoom-wrapper");
	const panzoomElement = panzoomElementWrapper.querySelector(".panzoom");

	// Instantiate Panzoom
	const panzoom = Panzoom(panzoomElement, {minScale: 1, maxScale: 10});
			
	// Setup default view using image element data attributes
	setTimeout(pan);
		
	// Update image position and zoom values in input on Panzoom change in INPRO
	switch (cycleCode) 
		{
			case 'INPRO':
				panzoomElement.addEventListener("panzoomchange", (event) => 
					{
						const formInputX = document.querySelector("#input-x");
						formInputX.value = event.detail.x;
						const formInputY = document.querySelector("#input-y");
						formInputY.value = event.detail.y;
						const formInputZoom = document.querySelector("#input-zoom");
						formInputZoom.value = event.detail.scale;
					});
				break;
			case 'CALIB':
				if ( calibStage == 0 );
					{	
						panzoomElement.addEventListener("panzoomchange", (event) => 
							{
								const formInputX = document.querySelector("#input-x");
								formInputX.value = event.detail.x;
								const formInputY = document.querySelector("#input-y");
								formInputY.value = event.detail.y;
								const formInputZoom = document.querySelector("#input-zoom");
								formInputZoom.value = event.detail.scale;
							});
					}
				break;
		}
				
	// if in calibrate or calib called from verit or inpro
	if ( cycleCode == 'CALIB' || verifytranscribeCalibrate == 'Y' )
		{
			if ( calibStage == '0' )
				{
					// and in stage 0, allow zoom
					zoomLock = 'N';
				}
			else
				{
					// otherwise lock zoom
					zoomLock = 'Y';
				}
		}
				
	// set zoomLock
	if ( zoomLock == 'N' )
		{
			panzoomElement.addEventListener("wheel", panzoom.zoomWithWheel);
		}
		
	function pan() 
		{
			// sometimes x and y can be 0, which causes a problem in image view.
			// protect by checking for x and y zero and putting in reasonable start values.
			if ( panzoom_x == 0 )
				{
					panzoom_x = 1;
				}
			if ( panzoom_y == 0 )
				{
					panzoom_y = 1;
				}
			if ( panzoom_z == 0 )
				{
					panzoom_z = 1;
				}
			// then pan
			panzoom.zoom(parseFloat(panzoom_z));
			panzoom.pan(parseFloat(panzoom_x), parseFloat(panzoom_y));
		}
		
	interact('.resizable')
		.resizable(
			{
				edges: 	{ 
							left: edgeL, 
							right: edgeR,
							top: edgeT,
							bottom: edgeB,
						}
			})
		.on('resizemove', function (event) 
			{
				var target = event.target;

				// update the element's style
				target.style.width  = event.rect.width + 'px';
				target.style.height = event.rect.height + 'px';
				
				updateDeffields(defFields, target.id, event.rect.width);	
			});
			
	interact('.draggable').draggable(
		{
			listeners: 
				{
					move (event) 
						{
							position.x += event.dx;
							position.y += event.dy;

							event.target.style.transform = `translate(${position.x}px, ${position.y}px)`;
						},
				}
		})
			
	function updateDeffields(defFields, elementId, width)
		{
			// find the id in the defFields array of arrays
			for (var fieldsIndex in defFields) 
				{
					// have I found the iteration with the current ID?
					if ( defFields[fieldsIndex]["html_id"] == elementId )
						{
							defFields[fieldsIndex]["column_width"] = width;
						}
				}
			// update form field
			$('#input-defFields').val(JSON.stringify(defFields));
		}
</script>


