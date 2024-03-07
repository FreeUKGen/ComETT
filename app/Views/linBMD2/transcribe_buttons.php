		<?php 
			$session = session();
		?>
		
		<div class="alert row mt-2 d-flex justify-content-between">
				
				<a id="return" class="btn btn-outline-primary mr-0" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
					<?php echo $session->current_project[0]['back_button_text']?>
				</a>
				
				<a id="image_parms" class="btn btn-outline-primary mr-0" href="<?php echo(base_url('transcribe/image_parameters_step1/0')); ?>">
					<span>Change this image parameters</span>
				</a>
				
				<a id="field_parms" class="btn btn-outline-primary mr-0" href="<?php echo(base_url('transcribe/enter_parameters_step1/0')); ?>">
					<span>Change this transcription parameters</span>
				</a>
				
				<!-- show sharpen slider -->
				<div class="">
					<input class="" type="range" id="sharpen-slider" min="1" max="5" step=".5" value="$session->sharpen" />
				</div>
				
				<!-- Sharpen filter for image using SVG -->
				<svg id="filters">
					<defs>
						<filter id="unsharpy" x="0" y="0" width="100%" height="100%">
							<feGaussianBlur result="blurOut" in="SourceGraphic" stdDeviation="1" />
							<feComposite operator="arithmetic" k1="0" k2="4" k3="-3" k4="0" in="SourceGraphic" in2="blurOut" />
						</filter>
					</defs>
				</svg>
				
				<a id="send_message_button" class="btn btn-outline-primary mr-0" href="<?php echo(base_url('transcribe/message_to_coord_step1/0')); ?>">
					<span>Send a message to your Co-ordinator</span>
				</a>

				<button type="submit" class="btn btn-outline-primary mr-0">
					<span>Submit</span>	
				</button>
		</div>
	</form>
	

