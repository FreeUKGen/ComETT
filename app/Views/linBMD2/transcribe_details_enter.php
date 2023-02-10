	<?php 
		$session = session();
	?>
	
	<!-- this for the font family (https://dl.dafont.com/dl/?f=modern_typewriter Free for personal and non-commercial use -->
	<style>
		@font-face 
			{
				font-family: 'webbmd';
				src: url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.eot');
				src: url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.eot?#iefix') format('embedded-opentype'),
					 url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.woff2') format('woff2'),
					 url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.woff') format('woff'),
					 url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.ttf') format('truetype'),
					 url('<?= '/Fonts/'.$session->transcribe_header[0]['BMD_font_family']; ?>.svg') format('svg');
				font-weight: normal;
				font-style: normal;
			}
	</style>

	<form action="<?php echo(base_url($session->controller.'/transcribe_'.$session->controller.'_step2')) ?>" method="post">
		
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
										font-size: <?= esc($session->transcribe_header[0]['BMD_font_size']);?>vw; 
										font-weight: <?= esc($session->transcribe_header[0]['BMD_font_style']);?>;
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
		
		<!-- show image -->
		<?php
		if ( ! isset($session->feh_show) )
			{
				$session->set('feh_show', 1);
				$file = getcwd().'/Users/'.$session->user[0]['BMD_user'].'/Scans/'.$session->transcribe_header[0]['BMD_scan_name'];	
				$session->set('image', $session->transcribe_header[0]['BMD_scan_name']);
				$session->set('fileData', exif_read_data($file));
				$session->set('mime_type', $session->fileData['MimeType']);
				$session->set('fileEncode', base64_encode(file_get_contents($file)));
			}
		?>
		
		<!-- show sharpen slider -->
		<div class="row d-flex justify-content-center">
			<h6 class="col-1 small text-muted">Sharpen</h6>
			<input class="col-2" type="range" id="sharpen-slider" min="1" max="5" step=".5" value="$session->sharpen" />
		</div>

		<!-- Inject initial values for Panzoom here (x, y, zoom) -->
		<div class="panzoom-wrapper row">
			<div class="panzoom">
				<?php echo "<img src=\"data:$session->mime_type;base64,$session->fileEncode\" alt=\"$session->image\" data-x=\"$session->panzoom_x\" data-y=\"$session->panzoom_y\" data-zoom=\"$session->panzoom_z\" data-s=\"$session->sharpen\"  data-scroll=\"$session->scroll_step\"/>"; ?>
			</div>
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
		
		<!-- show body line 2 -->
		<div class="form-inline row">
		<?php
			// loop through table element by element
			foreach ($session->table_details_body_line_2 as $td) 
				{ 
					if ( $td['BMD_show'] == 'Y' )
						{
							$fn=$td['BMD_name'];
							?>
							<!-- output data -->
							<input
								class="form-control"
								style="height: auto; 
										width: <?php if (esc($td['BMD_header_span']) > 0) {echo esc($td['BMD_header_span']);}?>%; 
										font-family: webbmd, sans-serif;
										font-size: <?= esc($session->transcribe_header[0]['BMD_font_size']);?>vw; 
										font-weight: <?= esc($session->transcribe_header[0]['BMD_font_style']);?>;
										text-align: <?php echo esc($td['BMD_header_align']);?>;
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
				
			<!-- Form part to save Panzoom and Sharpen state -->
			<input type="hidden" name="panzoom_x" id="input-x">
			<input type="hidden" name="panzoom_y" id="input-y">
			<input type="hidden" name="panzoom_z" id="input-zoom">
			<input type="hidden" name="sharpen" id="input-sharpen">
		</div>	
		
		<br><br>

	<!-- ATTENTION - the form is closed in the transcribe_buttons view -->
		
	



