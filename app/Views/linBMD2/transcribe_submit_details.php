	<?php $session = session(); ?>
	
	<br><br><br><br><br><br>

			
	<div class="row">
		<label for="file_name" class="col-2 pl-0">BMD file name =></label>
		<span class="col-2 pl-0" id="file_name" name="file_name"><?php echo esc($session->transcribe_header[0]['BMD_file_name']); ?></span>
		<label for="scan_name" class="col-2 pl-0">BMD scan name =></label>
		<span class="col-2 pl-0" id="scan_name" name="scan_name"><?php echo esc($session->transcribe_header[0]['BMD_scan_name']); ?></span>
	</div>
	
	<div class="row">
		<label for="upload_date" class="col-2 pl-0">Upload date =></label>
		<span class="col-2 pl-0" id="upload_date" name="upload_date"><?php echo esc($session->transcribe_header[0]['BMD_submit_date']); ?></span>
		<label for="upload_status" class="col-2 pl-0">Upload status =></label>
		<span class="col-2 pl-0" id="upload_status" name="upload_status"><?php echo esc($session->transcribe_header[0]['BMD_submit_status']); ?></span>
	</div>
	
	<div class="row">
		<label for="upload_message" class="col-2 pl-0">Upload messages =></label>
		<span class="col-10 pl-0" id="upload_message" name="upload_message"><?php echo esc($session->transcribe_header[0]['BMD_submit_message']); ?></span>
	</div>
		
		
	<div class="row mt-4 d-flex justify-content-between">
			
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
				
	</div>
	
