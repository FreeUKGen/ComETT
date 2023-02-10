<?php $session = session(); ?>	

<script>
document.addEventListener("DOMContentLoaded", () => 
{	
	// position show details transcribed to last line of table.
	$(document).ready(function()
		{			
			const element = document.getElementById("content");
			element.scrollIntoView(false);
		});
	
	// handle keypress requests
	$(document).keypress(function()
		{
			$( "#firstname" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
				})

			$( "#secondname" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
				})
				
			$( "#thirdname" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_firstnames')) ?>",
				})
				
			$( "#partnername" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
				})
				
			$( "#district" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_districts')) ?>",
				})
				
			$( "#reverselookup" ).autocomplete(
				{
					minLength: 1,
					source: "<?php echo(base_url('transcribe/search_volumes')) ?>",
				})	
		});
		

		
	window.onkeydown= function(dup)
			{ 
				// test which key was pressed
				// key codes are here -> https://www.oreilly.com/library/view/javascript-dhtml/9780596514082/apb.html
				switch (dup.key)
					{
						case "Insert": // Insert key pressed = duplicate
							// stop the browser getting the key
							dup.preventDefault();
							// test which field to duplicate but only if records exist
							switch (dup.target.id)
								{
									case 'firstname':
										$('#firstname').val("<?php echo $session->dup_firstname; ?>");
										$('#secondname').focus();
										break;
									case 'secondname':
										$('#secondname').val("<?php echo $session->dup_secondname; ?>");
										$('#thirdname').focus();
										break;
									default:
										break;
								}
							break;
						case "Home": // Home key pressed = duplicate all
							// stop the browser getting the key
							dup.preventDefault();
							// duplicate all fields
							$('#firstname').val("<?php echo $session->dup_firstname; ?>");
							$('#secondname').val("<?php echo $session->dup_secondname; ?>");
							$('#thirdname').val("<?php echo $session->dup_thirdname; ?>");
							$('#partnername').val("<?php echo $session->dup_partnername; ?>");
							$('#age').val("<?php echo $session->dup_age; ?>");
							$('#district').val("<?php echo $session->dup_district; ?>");
							$('#registration').val("<?php echo $session->dup_registration; ?>");
							$('#dis_number').val("<?php echo $session->dup_dis_number; ?>");
							$('#reg_number').val("<?php echo $session->dup_reg_number; ?>");
							$('#ent_number').val("<?php echo $session->dup_ent_number; ?>");
							$('#source_code').val("<?php echo $session->dup_source_code; ?>");
							$('#page').val('');
							$('#page').focus();
							break;
						case "End": // end pressed = duplicate family name to partner name
							// stop the browser getting the key
							dup.preventDefault();
							$('#partnername').val($('#familyname').val());
							$('#district').focus();
							break;
						case "PageDown": // Page down pressed = advance image by one line
							// stop the browser getting the key
							dup.preventDefault();
							panzoom.pan(0, -imageElement.dataset.scroll, { relative: true } );
							switch (dup.target.id)
								{
									case 'familyname':
										$('#firstname').focus();
										break;
									case 'partnername':
										const partnernameInput = document.querySelector('#partnername');
										const partnernameLength = partnernameInput.value.length;
										if ( partnernameLength === 0 )
											{
												$('#partnername').focus();
											}
										else
											{
												$('#district').focus();
											}
										break;
									default:
										const firstnameInput = document.querySelector('#firstname');
										const firstnameLength = firstnameInput.value.length;
										if ( firstnameLength === 0 )
											{
												$('#firstname').focus();
											}
										else
											{
												$('#partnername').focus();
											}
										break;
								}
							break;
						case "PageUp": // Page Up pressed = reverse image by one line
							// stop the browser getting the key
							dup.preventDefault();
							panzoom.pan(0, imageElement.dataset.scroll, { relative: true });
							break;
						case "Pause": // Pause pressed - position cursor at end of familyname
							// stop the browser getting the key
							dup.preventDefault();
							const familynameInput = document.querySelector('#familyname');
							const cursorPos = familynameInput.value.length;
							familynameInput.focus();
							familynameInput.setSelectionRange(cursorPos,cursorPos);		 
							break;
						default:
							break;
					}
			};

	// handle firstname actions
	$('.go_firstname_button').on("click", function()
			{
				// define the variables
				var id=$(this).data('id');
				var BMD_next_action=$(this).parents('tr').find('select[name="next_action"]').val();
				// load variables to form
				$('#Firstname').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
			
	// handle surname actions
	$('.go_surname_button').on("click", function()
			{
				// define the variables
				var id=$(this).data('id');
				var BMD_next_action=$(this).parents('tr').find('select[name="next_action"]').val();
				// load variables to form
				$('#Surname').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
			
		// HTML elements to hold Panzoom
		const panzoomElementWrapper = document.querySelector(".panzoom-wrapper");
		const panzoomElement = panzoomElementWrapper.querySelector(".panzoom");
		const imageElement = panzoomElement.querySelector("img");

		// Instantiate Panzoom
		const panzoom = Panzoom(panzoomElement);

		// Setup default view using image element data attributes
		setTimeout(() => {
			panzoom.zoom(parseFloat(imageElement.dataset.zoom));
			panzoom.pan(
				parseFloat(imageElement.dataset.x),
				parseFloat(imageElement.dataset.y)
			);
		});

		// Enable zoom on mouse wheel
		panzoomElement.addEventListener("wheel", panzoom.zoomWithWheel);
		
		// Update image position and zoom values in input on Panzoom change
		panzoomElement.addEventListener("panzoomchange", (event) => {
			const formInputX = document.querySelector("#input-x");
			const formInputY = document.querySelector("#input-y");
			const formInputZoom = document.querySelector("#input-zoom");
			formInputX.value = event.detail.x;
			formInputY.value = event.detail.y;
			formInputZoom.value = event.detail.scale;
		});

		// Sharpen filter system
		const filterDefElement = document.querySelector("#unsharpy > feComposite");
		const filterSlider = document.querySelector("#sharpen-slider");
		const formInputSharpen = document.querySelector("#input-sharpen");

		filterSlider.value = formInputSharpen.value = parseFloat(imageElement.dataset.s);
		filterSlider.addEventListener("change", (event) => {
			const factor = parseFloat(event.currentTarget.value);
			formInputSharpen.value = factor;
			filterDefElement.setAttribute("k2", factor);
			filterDefElement.setAttribute("k3", 1 - factor);
		});
		
		
	
});
  </script>
