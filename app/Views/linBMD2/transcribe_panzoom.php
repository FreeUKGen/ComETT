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
	var panzoom_x = <?php echo json_encode($session->panzoom_x); ?>;
	var panzoom_y = <?php echo json_encode($session->panzoom_y); ?>;
	var panzoom_z = <?php echo json_encode($session->panzoom_z); ?>;
alert(clientWidth+' * '+clientHeight);
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
		
</script>


