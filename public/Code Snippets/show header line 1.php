<!-- show header line 1 -->
		<div class="row text-left">
		<?php
			// loop through table element by element
			foreach ($session->table_details_head_line_1 as $th) 
				{ ?>		
					<h6 class="<?php echo $th['BMD_span']; ?> pl-0 small text-muted"><?php echo $th['BMD_html']; ?> </h6>
			<?php	}  ?>	
		</div>


<!-- show body line 1 -->
		<div class="form-inline row">
		<?php			
			// loop through table element by element
			foreach ($session->table_details_body_line_1 as $td) 
				{ 
					$fn=$td['BMD_name'];
		
					// output data
					if ( $td['BMD_show'] == 'Y' )
						{
							?>
							<input
								class="form-control"
								style="height: auto; 
										width: <?php if (esc($td['BMD_header_span']) > 0) {echo esc($td['BMD_header_span']);}?>%;
										font-family: webbmd, sans-serif;
										font-size: <?= esc($session->current_transcription[0]['BMD_font_size']);?>vw; 
										font-weight: <?= esc($session->current_transcription[0]['BMD_font_style']);?>;
										text-align: <?= esc($td['BMD_header_align']);?>;
										padding-left: <?= esc($td['BMD_header_pad_left']);?>px;"
								type="text" 
								id="<?php echo esc($td['BMD_id']);?>" 
								name="<?php echo esc($td['BMD_name']);?>"
								value="<?php echo esc($session->$fn);?>"
								autocomplete="off"
								<?php if ($session->position_cursor == $td['BMD_name']) { ?> autofocus <?php } ?>
							>
				<?php
						}
				}?>
		</div>
		
		
		
		
case "F4": // F4 key pressed = show virtual keyboard
							// stop the browser getting the key
							dup.preventDefault();
							// do nothing if target id is blank = no field focussed
							if ( dup.target.id != '' )
								{
									// find current ID in def fields and test if virtual keyboard allowed for this ID
									// get def fields - held in current transcription def fields array
									defFields = <?php echo json_encode($session->current_transcription_def_fields); ?>;
									// loop through def fields
									for ( var fieldsIndex in defFields ) 
										{
											// have I found the iteration with the current ID?
											if ( defFields[fieldsIndex]["html_id"] == dup.target.id )
												{
													// is the virtual keyboard allowed for this ID
													if ( defFields[fieldsIndex]["virtual_keyboard"] == 'YES' )
														{
															$(function()
																{
																	// create the id
																	const inputField = document.querySelector('#' + dup.target.id);
																	// initialse the keyboard on this id
																	$(inputField).keyboard(
																	{
																		// options here
																		usePreview: false,
																		caretToEnd: true,
																		openOn: null,
																	})
																	// focus the id to show the keyboard
																	//inputField.focus();
																	//.addTyping(); 
console.log(inputField.name+'keyboardicon');
$('#'+inputField.name+'keyboardicon').click(function(){ 
	console.log('in function');
 $(inputField).getkeyboard().reveal(); 
});

																	// destroy the keybaord on cancel key
																	$(inputField).on('canceled', function(e, keyboard, el)
																		{
																			// destroy
																			console.log('cancel ' + dup.target.id);
																			
																			//$(inputField).getkeyboard().destroy(); 
																		});
																})
														}
												}
										}
								}
