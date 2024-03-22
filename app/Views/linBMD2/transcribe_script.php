<?php $session = session(); ?>	

<script>
	
	// check session exists
	$(document).ready(function()
		{
			fetch("<?php echo(base_url('home/session_exists')) ?>", 
				{
					method: "POST",
					headers: 
						{
							"Content-Type": "application/json; charset=UTF-8",
						},
					body: JSON.stringify(),
				})
			.then(response => response.text()) // get the response as text because JSON doesn't work
			.then(data => 	{
								const myData = data.split('<');
								var sessionStatus = myData[0];
								sessionStatus = sessionStatus.replace(/\"/g, '');
								if ( sessionStatus.trim() === 'session_expired' )
									{
										alert("Your session has EXPIRED. Press Submit button or ENTER to signin again and continue transcribing.");
									}
							})
			.catch((error) => 	{
								})
		});
		
		// debug with console.log('message');
	
	$(document).ready(function()
		{
			// Sharpen filter system
			const filterDefElement = document.querySelector("#unsharpy > feComposite");
			const filterSlider = document.querySelector("#sharpen-slider");
			const formInputSharpen = document.querySelector("#input-sharpen");
			var sharpen = <?php echo json_encode($session->sharpen); ?>;

			filterSlider.value = formInputSharpen.value = parseFloat(sharpen);
			filterSlider.addEventListener("change", (event) => 
				{
					const factor = parseFloat(event.currentTarget.value);
					formInputSharpen.value = factor;
					filterDefElement.setAttribute("k2", factor);
					filterDefElement.setAttribute("k3", 1 - factor);
				});
		});
		
	// position show details transcribed to last line of table.
	$(document).ready(function()
		{							
			if ( document.getElementById("insert_before_line") )
				{
					document.getElementById("insert_before_line").scrollIntoView({ behavior: "instant", block: "end", inline: "nearest" });
				}
			else if ( document.getElementById("inserted_line") )
				{
					document.getElementById("inserted_line").scrollIntoView({ behavior: "instant", block: "end", inline: "nearest" });
				}
			else if ( document.getElementById("modified_line") )
				{
					document.getElementById("modified_line").scrollIntoView({ behavior: "instant", block: "end", inline: "nearest" });
				}
			else if ( document.getElementById("last_line") )
				{
					document.getElementById("last_line").scrollIntoView({ behavior: "instant", block: "end", inline: "nearest" });
				}
			else
				{
					document.getElementById("last_line").scrollIntoView({ behavior: "instant", block: "end", inline: "nearest" });
				}
		});

	// handle keypress requests
	$(document).keypress(function(e)
		{
			// manage autocomplete
			
			$( "#forenames" ).autocomplete(
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
				
			$( "#surname" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
				})
				
			$( "#mother" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
				})
				
			$( "#district" ).autocomplete(
				{
					minLength: 2,
					delay: 500,
					source: "<?php echo(base_url('transcribe/search_districts')) ?>",
					
				})
				
			$( "#volume" ).autocomplete(
				{
					minLength: 1,
					source: "<?php echo(base_url('transcribe/search_volumes')) ?>",
				})
				
			$( "#synonym" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_districts')) ?>",
				})
			
			$( "#spouse" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_surnames')) ?>",
				})
				
			// end manage autocomplete
			
			
		});
		
	// handle tab press in district = get volume
	$("input[name=district]").keydown(function(e)
		{
			// get the code of the key that was pressed 
			var code = e.keycode || e.which;
			
			// only get volume if TAB was pressed in district field
			if ( code === 9 )
				{
					// get def fields - held in current transcription def fields array
					defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
					
					// loop through def fields
					for (var fieldsIndex in defFields) 
						{
							// have I found the iteration with html_id of volume?
							if ( defFields[fieldsIndex]["html_id"] == 'volume' )
								{	
									// get the value of district
									const district = $('#district').val();
									
									// call the php method to get the volume this district, year and quarter
									fetch("<?php echo(base_url('transcribe/get_volume')) ?>", 
										{
											method: "POST",
											headers: 
												{
													"Content-Type": "application/json; charset=UTF-8",
												},
											body: JSON.stringify([district, defFields[fieldsIndex]["volume_roman"]]),
										})
									.then(response => response.text()) // x=${x}&y=${y} get the response as text because JSON doesn't work
									.then(data => 	{
														const myData = data.split('<'); 		// isolate what I want because CI adds a lot of stuff
														var volume = myData[0];					// now get first element of array as volume
														volume = volume.replace(/\"/g, '');		// take out all "
														if ( volume.trim().length !== 0 )
															{
																$('#volume').val(volume);							// set volume
																$('#' + getnextID("volumeFill")).focus();			// and focus next desired input field
															}
														else
															{
																$('#volume').val('');
																$('#volume').focus();
															}
													})
									.catch((error) => 	{
															$('#volume').val('');				// if error, set volume as blank
														})
									break;
								}
						}
				}
		});
	
	// handle click in verify on the fly
	$('#verifyonthefly').on("click touchend", function(e) 
		{
			// if more than one click is detected stop
			if( e.detail > 1 )
				{ 
					e.preventDefault(); 
				}	
		});
	
	window.onkeydown = function(dup)
			{ 
				// key codes are here -> https://www.oreilly.com/library/view/javascript-dhtml/9780596514082/apb.html
				
				// get which key was pressed
				var keyPressed = dup.key;
				var forenamesALL = '';
				
				// test if special key where pressed
				if ( dup.ctrlKey && dup.key === 'r' || dup.ctrlKey && dup.key === 'R') { keyPressed = 'Insert' }
				if ( dup.ctrlKey && dup.key === 'b' || dup.ctrlKey && dup.key === 'B') { keyPressed = 'Back' }
				if ( dup.ctrlKey && dup.key === 'a' || dup.ctrlKey && dup.key === 'A') { keyPressed = 'Insert'; forenamesALL = 'ALL'; }
				
				// if verify on the fly get keys
				verifyOnthefly = <?php echo json_encode($session->verify_onthefly); ?>;
				if ( verifyOnthefly == 1 )
					{
						if ( dup.altKey && dup.key === 'v' || dup.altKey && dup.key === 'V') { keyPressed = 'VerifyConfirm' }
					}
				
				// do actions depending on key press				
				switch (keyPressed)
					{
						case "Insert": // Insert key pressed = duplicate
							// stop the browser getting the key
							dup.preventDefault();
							
							// get the last data element array
							lastEl = <?php echo json_encode($session->lastEl); ?>;

							// get def fields - held in current transcription def fields array
							defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
							
							// loop through def fields
							for (var fieldsIndex in defFields) 
								{
									// have I found the iteration with the current ID?
									if ( defFields[fieldsIndex]["html_id"] == dup.target.id )
										{
											// am I processing a forename? If so repeat first forename only except if all fornames requested
											if ( dup.target.id == 'forenames' && forenamesALL == '' )
												{
													// explode on space
													const forenames = lastEl[defFields[fieldsIndex]["table_fieldname"]].split(" ");
													// remove full stop at end if there is one
													var lastChar = forenames[0].charAt(forenames[0].length - 1);
													// set value
													if ( lastChar == '.' )
														{
															var cleanForename = forenames[0].substr(0, forenames[0].length - 1);
															$('#' + dup.target.id).val(cleanForename + ' ');
														}
													else
														{
															$('#' + dup.target.id).val(forenames[0] + ' ');
														}
												}
											else
												{
													// set value
													$('#' + dup.target.id).val(lastEl[defFields[fieldsIndex]["table_fieldname"]]);
												}
											
											// break loop
											break;
										}
								}	
							break;
							
						case "Home": // Home key pressed = duplicate all
							// stop the browser getting the key
							dup.preventDefault();
							
							// get the last data element array
							var lastEl = <?php echo json_encode($session->lastEl); ?>;
							
							// get def fields - held in current transcription def fields array
							var defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
							
							// loop through def fields
							for (var fieldsIndex in defFields) 
								{
									// set value
									$('#' + defFields[fieldsIndex]["html_id"]).val(lastEl[defFields[fieldsIndex]["table_fieldname"]]);
								}
							
							// set last field in data entry and focus
							$('#' + defFields[defFields.length - 1]["html_id"]).val(' ');
							$('#' + defFields[defFields.length - 1]["html_id"]).focus();
							break;
							
						case "End": // end pressed in mother field = duplicate surname to mother name
							// stop the browser getting the key
							dup.preventDefault();
							
							// get def fields - held in current transcription def fields array
							defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
							
							// loop through def fields
							for (var fieldsIndex in defFields) 
								{
									// have I found the iteration with the current ID?
									if ( defFields[fieldsIndex]["html_id"] == dup.target.id )
										{
											// only do this if dup_fromfieldname is not blank
											if ( defFields[fieldsIndex]["dup_fromfieldname"] != null )
												{													
													// set value
													$('#' + dup.target.id).val($('#' + defFields[fieldsIndex]["dup_fromfieldname"]).val());
													// get next ID for focus
													// increment index
													fieldsIndex++;
													// check past end of array
													if ( fieldsIndex > defFields.length - 1 )
													{
														fieldsIndex = defFields.length - 1;
													}		
													
													// focus
													$('#' + defFields[fieldsIndex]["html_id"]).focus();
												}
											
											// break loop
											break;
										}
								}	
							break;
							
						case "PageDown": // Page down pressed = advance image by one line
							// stop the browser getting the key
							dup.preventDefault();
							var scrollStep = <?php echo json_encode($session->scroll_step); ?>;
							panzoom.pan(0, -scrollStep, { relative: true } );
							break;
							
						case "PageUp": // Page Up pressed = reverse image by one line
							// stop the browser getting the key
							dup.preventDefault();
							var scrollStep = <?php echo json_encode($session->scroll_step); ?>;
							panzoom.pan(0, scrollStep, { relative: true });
							break;
							
						case "Back": // Control pressed - position cursor at end of previous field
							// stop the browser getting the key
							dup.preventDefault();
							// get the previous ID
							getpreviousID(dup.target.id);
							const inputField = document.querySelector('#' + getpreviousID(dup.target.id));
							const cursorPos = inputField.value.length;
							inputField.focus();
							inputField.setSelectionRange(cursorPos,cursorPos);		 
							break;
							
						case "Tab": // Tab pressed - if in last data entry field stop from tabbing.
							// get def fields - held in current transcription def fields array
							defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
							// get cycle code
							var cycleCode = <?php echo json_encode($session->BMD_cycle_code); ?>;
							// was tab pressed in last data entry field?
							// test for last field is different in VERIT because dup.target.id is a constructed id and not just the html_id
							if ( cycleCode == "VERIT" )
								{
									// is the html_id on the last field a substring of the composed dup.target.id 
									// if so test returns somthing other than -1
									if ( dup.target.id.indexOf(defFields[defFields.length - 1]['html_id']) !== -1)
										{
											// if so stop tab
											dup.preventDefault();
										}
								}
							else
								{
									// is last htmlid equal to dup.target.id
									if ( defFields[defFields.length - 1]['html_id'] == dup.target.id )
										{
											// if so stop tab
											dup.preventDefault();
										}
								}
							break;
							
						case "VerifyConfirm": // alt v pressed - do the verify confirm
							// stop the browser getting the key
							dup.preventDefault();
							// get the in verify flag
							verifyOnthefly = <?php echo json_encode($session->verify_onthefly); ?>;
							// test if in verify
							if ( verifyOnthefly == 1 )
								{
									// create the click event
									var clickEvent = new MouseEvent("click", 
										{
											"view": window,
											"bubbles": true,
											"cancelable": false
										});

									// create the element
									var element = document.getElementById("verifyonthefly");
									// and click it
									element.dispatchEvent(clickEvent);
								}								
							break;
							
						default:
							break;
					}
			};
						
	// function to get the next input field ID
	// current input id is passed as parameter
	// current input fields are held in the PHP array, so parse it to a JS array
	function getnextID(element) 
		{
			// initialise
			var nextID = null;
			
			// if called from volume fill after tab out of district, element == volume
			// next field is defined in the def range array.
			if ( element == 'volumeFill' )
				{
					var defProfile = <?php echo json_encode($session->def_range[0]); ?>;
			
					if ( defProfile['volume_follows_district'] == 'Y' )
						{
							nextID = defProfile['field_after_volume'];
						}
					else
						{
							nextID = defProfile['field_after_district'];
						}
				}
			else
				{
					// initialise
					var nextID = null;
					var nextIndex = 0;
					// if called from insert then def fields are held in current transcription def fields array
					var defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
					// get last key
					lastKey = defFields.length - 1;
					for (var fieldsIndex in defFields) 
						{
							// have I found the iteration with the current ID? If so, pass next ID
							if ( defFields[fieldsIndex]["html_id"] == element )
								{
									// increment index
									fieldsIndex++;
									// check past end of array
									if ( fieldsIndex > lastKey )
										{
											fieldsIndex = lastKey;
										}		
									nextID = defFields[fieldsIndex]["html_id"];
									break;
								}
						}
				}
			
			return nextID;
		}
		
	// function to get the previous input field ID
	// current input id is passed as parameter
	// current input fields are held in the PHP array, so parse it to a JS array
	function getpreviousID(element) 
		{
			// initialise
			var previousID = null;
			
			// if called from ArrowLeft then def fields are held in current transcription def fields array
			var defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
			for (var fieldsIndex in defFields) 
				{
					// have I found the iteration with the current ID? If so, pass previous ID
					if ( defFields[fieldsIndex]["html_id"] == element )
						{
							previousIndex = fieldsIndex - 1;
							if ( previousIndex < 0 )
								{
									previousIndex = 0;
								}		
							previousID = defFields[previousIndex]["html_id"];
							// console.log(previousID);
							break;
						}
				}
			
			return previousID;
		}

</script>

<script>
	// thanks to - Rob Garrison - https://github.com/Mottie/Keyboard
	// has a virtual keyboard icon been clicked?
	// the keyboard icon has an id constructed from the id of the mother+'_keyboardicon
	$("[id$='_keyboardicon']").click(function(e) 
		{
			// stop the browser getting the key
			e.preventDefault();
			// get the mother id using split on _
			const motherArray = e.target.id.split("_");
			const inputField = document.querySelector('#' + motherArray[0]);
			if ( motherArray[0] != '' )
				{
					// find current input field ID in def fields and test if virtual keyboard allowed for this ID
					// get def fields - held in current transcription def fields array
					defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
					// loop through def fields
					for ( var fieldsIndex in defFields ) 
						{
							// have I found the iteration with the current ID?
							if ( defFields[fieldsIndex]["html_id"] == motherArray[0] )
								{
									// is the virtual keyboard allowed for this ID?
									if ( defFields[fieldsIndex]["virtual_keyboard"] == 'YES' )
										{
											// then initialise the keyboard on this field
											$(function()
												{
													// initialse the keyboard on this id
													$(inputField).keyboard(
														{
															// options here
															// don't absorb the input field into the keyboard
															usePreview: false,
															// cursor to end of any input already in input field		
															caretToEnd: true,
															// don't auto show the keyboard. This will prevent the keyboard from showing if the input field is subsequently focussed.		
															openOn: null,
															// change key names
															display: 
																{   
																   's'		: 'Capitals',
																   'c'		: 'Cancel',
																   'a'		: 'Accept',
																}, 
															// use a custom layout
															layout: 'custom',
															// define the custom layout - https://www.lexilogos.com/keyboard/latin_alphabet.htm
															customLayout: 	
																{
																	'normal' : ['á à â ā ä ã å æ', 'é è ê ē ë', 'í ì î ī ï', 'ó ò ô ō ö õ ø œ', 'ú ù û ū ü', 'ŵ', 'ý ŷ ȳ ÿ', 'þ ç ð ñ ß', '{c} {s} {a}'],
																	'shift'  : ['Á À Â Ā Ä Ã Å Æ', 'É È Ê Ē Ë', 'Í Ì Î Ī Ï', 'Ó Ò Ô Ō Ö Õ Ø Œ', 'Ú Ù Û Ū Ü', 'Ŵ', 'Ý Ŷ Ȳ Ÿ', 'Þ Ç Ð Ñ ẞ', '{c} {s} {a}']
																}			
														});
													// now reveal the keyboard
													$(inputField).getkeyboard().reveal();
												});						
										}
								}
						}
				}
		});
</script>

<script>
var cycleCode = <?php echo json_encode($session->BMD_cycle_code); ?>;
if ( cycleCode == 'INPRO' )
	{
		document.getElementById("surname").onmouseenter = function() 
			{
				document.getElementById("surname").title = document.getElementById("surname").value;
			}
	};
if ( cycleCode == 'VERIT' )
	{
		var index = <?php echo json_encode($session->detail_line['BMD_index']); ?>;
		document.getElementById("surname"+index).onmouseenter = function() 
			{
				document.getElementById("surname"+index).title = document.getElementById("surname"+index).value;
			}
	};
</script>


