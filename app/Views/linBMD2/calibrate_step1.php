	<?php 
		$session = session();
	?>
		
	<div>
		<!-- form to capture image parms -->
		<form action="<?php echo(base_url('/transcribe/calibrate_step2')) ?>" method="post">
			<?php
			switch ($session->calibrate) 
						{
							// rotation, zoom, posiion
							case 0: ?> 
								
								<div class="form-group row d-flex align-items-center">
									<!-- rotation -->
									<label for="rotation" class="col-2 pl-0">Rotation :</label>
									<input type="text" class="form-control col-1" id="rotation" name="rotation" aria-describedby="userHelp" value="<?php echo($session->rotation) ?>">
									<small id="userHelp" class="form-text text-muted col-3">Enter the rotation required in degrees; -ve for rotate left, +ve for rotate right. Can be decimals of a degree. The image will be re-positioned as you change the rotation.</small>
									
									<!-- zoom lock -->
									<label for="input-zoom-lock" class="col-1 pl-0">Zoom Lock :</label>
									<select name="zoom_lock" id="input-zoom-lock" class="box col-1">
										<?php foreach ($session->yesno as $key => $value): ?>
											 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->zoom_lock ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
										<?php endforeach; ?>
									</select>
									<small id="userHelp" class="form-text text-muted col-3">Whether to allow zoom on Transcribe and Verify screens.</small>	
								</div>
								
								<div class="form-group row d-flex align-items-center">
									<!-- zoom -->
									<label for="input-zoom" class="col-2 pl-0">Zoom :</label>
									<input type="number" step="0.1" min="1" max="10" class="form-control col-1" id="input-zoom" name="panzoom_z" aria-describedby="userHelp" value="<?php echo($session->panzoom_z); ?>">
									<small id="userHelp" class="form-text text-muted col-3">Use the mouse scroll wheel to zoom the image as required.</small>				
								</div>
								
								<div class="form-group row d-flex align-items-center">
									<!-- horizontal position -->
									<label for="input-x" class="col-2 pl-0">Horizontal Position :</label>
									<input type="number" class="form-control col-1" id="input-x" name="panzoom_x" readonly aria-describedby="userHelp" value="<?php echo($session->panzoom_x); ?>" tabindex="-1">
									<small id="userHelp" class="form-text text-muted col-3">Click and drag the image to the horizontal position you want.</small>
									
									<!-- vertical position -->
									<label for="input-y" class="col-1 pl-0">Vertical Position :</label>
									<input type="number" class="form-control col-1" id="input-y" name="panzoom_y" readonly aria-describedby="userHelp" value="<?php echo($session->panzoom_y); ?>" tabindex="-1">
									<small id="userHelp" class="form-text text-muted col-3">Click and drag the image so that the first data line of the image is at the top of the image window.</small>									
								</div>
								<?php
								break;
								
							// scroll step, image height	
							case 1: ?>
								<div class="form-group row d-flex align-items-center">	
									<!-- number of lines to be shown -->
									<label for="input_height_lines" class="col-2 pl-0">Number of scan lines to show in Transcribe and Verify :</label>
									<input type="number" class="form-control col-1" id="input_height_lines" name="height_l" value="<?php echo($session->height_l); ?>">
									<small id="userHelp" class="form-text text-muted col-5">Enter the number of scan lines that you wish to see in the transcribe and verify screens. The height of the image will be calculated automatically.</small>
									
									<!-- height of image -->
									<label for="input_height_image" class="col-1 pl-0">Image Height :</label>
									<input type="number" class="form-control col-1" id="input_height_image" name="image_y" readonly aria-describedby="userHelp" value="<?php echo($session->image_y); ?>" tabindex="-1">
								</div>
							
								<!-- scroll step -->
								<div class="form-group row d-flex align-items-center">
									<!-- number of lines -->
									<label for="input_scroll_lines" class="col-2 pl-0">Number of lines to use for scroll step calculation :</label>
									<input type="number" class="form-control col-1" autofocus id="input_scroll_lines" name="panzoom_l" value="<?php echo($session->panzoom_l); ?>">
									<small id="userHelp" class="form-text text-muted col-5">Enter the number of lines you intend to use for calibrating the Scroll Step. The more the better. </small>
									
									<!-- scroll step -->
									<label for="input_scroll_step" class="col-1 pl-0">Scroll Step :</label>
									<input type="number" step="0.1" min="1" max="100" class="form-control col-1" id="input_scroll_step" name="panzoom_s" aria-describedby="userHelp" value="<?php echo($session->panzoom_s); ?>">
									<small id="userHelp" class="form-text text-muted col-2">You can use the RED bar method or manually enter a value here by using the up/down arrows in the input field. </small>
								</div>
								
								<!-- instructions -->
								<div class="form-group row d-flex align-items-center">
									<p class="col-12 pl-0">Now click and drag the red ruler to cover the horizontal center of first line. Press CTRL + Arrow Down (<i>MAC = SHIFT+CONTROL+COMMAND+DownArrow</i>) until the red ruler has descended to cover centre of 10th line or number of lines available. Watch the height and scroll step being calculated.</p>
								</div>

								<?php
								break;	
							
							// data entry fields	
							case 2: ?>
							
								<!-- instructions -->
								<div class="form-group row d-flex align-items-center">
									<p class="col-12 pl-0">Select the field you want to work with from the drop-down list. Move the red box so that its left-hand side is on the start of the data for that field. Press CTRL+arrow-right (<i>MAC = CONTROL+COMMAND+RightArrow</i>) to extend the red box to the end of the field. The end of the field is not necessarily the end of the data; it could be the start of the next field. CTRL+arrow-left (<i>MAC = CONTROL+COMMAND+LeftArrow</i>) will reduce the length of the red box. You can drag the row of fields to the image which may help with alignment.</p>
								</div>
								
								<div class="row d-flex align-items-center">
									<label for="input_field" class="col-1 pl-0">Input Field</label>
										<select class="box" name="input_field" id="input_field">
											<?php foreach ($session->current_transcription_def_fields as $key => $td): ?>
													 <option value="<?= esc($td['html_id'])?>">
														<?= esc($td['column_name'])?>
													</option>
											<?php endforeach; ?>
										</select>
								</div>

								<br>
								
								<div class="form-group row d-flex align-items-center" draggable="false" id="dragme_no">
								<?php
									// loop through table element by element
									foreach ($session->current_transcription_def_fields as $td) 
										{ ?>
											<!-- output data -->
											<input
												class=	"form-control"
												style=	"	
															height: 		auto; 
															width: 			<?php if (esc($td['column_width']) > 0) {echo esc($td['column_width']);}?>px; 
															font-size: 		<?= esc($td['font_size']);?>vw; 
															font-weight: 	<?= esc($td['font_weight']);?>;
															text-align: 	<?php echo esc($td['field_align']);?>;
															padding-left: 	<?= esc($td['pad_left']).'px';?>;
															border: 		2px  solid rgba(0,0,0,0.5);
															border-radius: 	4px;	
														"
												type=	"number" 
												id=		"<?php echo esc($td['html_id']);?>" 
												name=	"<?php echo esc($td['html_name']);?>"
												value=	"<?php echo esc($td['column_width']); ?>"
												readonly
											>
										<?php
										} ?>
								</div>
								<?php
								break;
						} ?>			
	</div>
	
	<!-- show image -->
	<!-- Inject initial values for Panzoom here (x, y, zoom ...) -->
	<br>
	<div class="panzoom-wrapper">
		<div class="panzoom" id="panzoom_image">
			<?php
				echo 
						"<img 
							src=\"data:$session->mime_type;base64,$session->fileEncode\" 
							alt=\"$session->image\"   
							data-scroll=\"$session->panzoom_s\"
						>"; 
			?>
		</div>
	</div>

		<div class="alert row mt-2 d-flex justify-content-between">
			
				<?php
				// if calibrate was done from verify or transcribe
				if ( $session->verifytranscribe_calibrate == 'N' )
					{ ?>
						<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('transcribe/transcribe_step1/2') ); ?>">
						<?php echo $session->current_project[0]['back_button_text']?>
						</a>
					<?php
					}
				else
					{
						switch ($session->last_cycle_code) 
							{
								case 'VERIT':
									$session->BMD_cycle_code = $session->last_cycle_code; ?>
										<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('transcribe/verify_step1/'.$session->current_transcription[0]['BMD_header_index'])); ?>">
										<?php echo $session->current_project[0]['back_button_text']?>
										</a>
									<?php
									break;
								case 'INPRO':
									$session->BMD_cycle_code = $session->last_cycle_code;
									switch ($session->current_allocation[0]['BMD_type']) 
										{
											case 'B': // = Births in FreeBMD ?>
													<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('births/transcribe_births_step1/0/')); ?>">
													<?php echo $session->current_project[0]['back_button_text']?>
													</a> 
												<?php
												break;
											case 'M': // = Marriages in FreeBMD ?>
													<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('marriages/transcribe_marriages_step1/0/')); ?>">
													<?php echo $session->current_project[0]['back_button_text']?>
													</a> 
												<?php
												break;
											case 'D': // = Deaths in FreeBMD ?>
													<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('deaths/transcribe_deaths_step1/0/')); ?>">
													<?php echo $session->current_project[0]['back_button_text']?>
													</a> 
												<?php
												break;
												
												// cases for types in other projects, FreeREG, FreeCEN
											default:
												break;
										}
									break;									
							}
					} ?>
						
				
				<?php
				if ( $session->calibrate < 2 ) 
					{ ?>
						<button type="submit" class="btn btn-primary mr-0">
							<span>Continue calibration</span>	
						</button>
					<?php
					} ?>

				<?php
				if ( $session->calibrate == 2 ) 
					{ ?>
						<button type="submit" class="btn btn-primary mr-0">
							<span>Complete calibration.</span>	
						</button>
					<?php
					} ?>
			</form> 
		</div>

<script>
			
	<!-- get calibration stage -->
	var calibrateStage = <?php echo json_encode($session->calibrate); ?>;
	
	<!-- apply rotate if stage 0 -->
	if ( calibrateStage == '0' )
		{
			document.getElementById("rotation").addEventListener("input", (event) => 
				{
					const rotateImage = $("#rotation").val();
					var imgs = document.querySelectorAll(".panzoom > img");
					for( var i = 0; i < imgs.length; i++ ) 
						{
							imgs[i].style.transform = "rotate("+rotateImage+"deg)";
						}
				});
		}
		
</script>

