	<?php $session = session(); ?>
	
	<form action="<?php echo(base_url('header/create_bmd_step2')) ?>" method="post">
		
		<div class="text-center d-flex">
			<hr color="black" class="flex-grow-1" />
				<span class="px-5 font-weight-bold large align-self-center">
					Create a webBMD header 
				</span>
			<hr color="black" class="flex-grow-1" />
		</div>
		
		<br>
		
		<div class="form-group row">
			<label for="syndicate" class="col-2 pl-0">Choose a Syndicate</label>
			<select name="syndicate" id="syndicate" class="col-8">
				<option value="NONE">Please select a syndicate...or refresh syndicates =></option>
				<?php foreach ($session->syndicates as $syndicate): ?>
					 <option value="<?php echo esc($syndicate['BMD_syndicate_index'])?>"><?php echo esc($syndicate['BMD_syndicate_name'])?></option>
				<?php endforeach; ?>
			</select>
					<a id="refresh_syndicates" class="btn btn-outline-primary btn-sm col-2 d-flex flex-column align-items-center" href="<?php echo base_url('syndicate/refresh_syndicates');?>">
						<span>Refresh Syndicates</span>
						<span class="spinner-border"  role="status">
							<span class="sr-only">Loading...</span>
						</span>
					</a>
		</div>
		
		<div class="form-group row">
			<label for="allocation" class="col-2 pl-0">Choose an Allocation</label>
			<select name="allocation" id="allocation" class="col-8">
				<option value="NONE">Please select an allocation...or create a new one =></option>
				<?php foreach ($session->allocations as $allocation): ?>
					<option value="<?php echo esc($allocation['BMD_allocation_index'])?>"><?php echo esc($allocation['BMD_allocation_name'].' => last uploaded = '.$allocation['BMD_last_uploaded'])?></option>
				<?php endforeach; ?>
			</select>
			<a class="btn btn-outline-primary btn-sm col-2" href="<?php echo base_url('allocation/create_allocation_step1/0');?>">Create new Allocation</a>
		</div>
	
		<div class="form-group row">
			<label for="scan_page" class="col-2 pl-0">Scan page number</label>
			<input type="text" class="form-control col-2" id="scan_page" name="scan_page" aria-describedby="userHelp" value="<?php echo esc($session->scan_page); ?>">
			<small id="userHelp" class="form-text text-muted col-2">Must be in your current allocation page range.</small>
			<label for="scan_page_suffix" class="col-2 ">Scan page suffix</label>
			<input type="text" class="form-control col-1" id="scan_page_suffix" name="scan_page_suffix" aria-describedby="userHelp" value="<?php echo esc($session->scan_page_suffix); ?>">
			<small id="userHelp" class="form-text text-muted col-3">Occasionally scan page numbers have a suffix, eg a, b.</small>
		</div>
		
		<div class="form-group row">
				<label for="autocreate" class="col-2 pl-0">Auto create scan name?</label>
				<select name="autocreate" id="autocreate" class="col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->autocreate ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
				<small id="userHelp" class="form-text text-muted col-2">If YES, the scan name will be created from the current allocation parameters.</small>
				<label for="scan_name" class="col-2">Scan Name</label>
				<input type="text" class="form-control col-4" id="scan_name" name="scan_name" aria-describedby="userHelp" value="<?php echo esc($session->scan_name); ?>">
		</div>
		
		<div class="form-group row">
				<label for="fetch_scan" class="col-2 pl-0">Automatically download the scan from FreeBMD?</label>
				<select name="fetch_scan" id="fetch_scan" class="col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->fetch_scan ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
				<small id="userHelp" class="form-text text-muted col-8">If YES, the scan will be automatically downloaded from FreeBMD. If NO, you will have to download the scan yourself.</small>
		</div>
		
		<br>
		
		<div class="text-center d-flex">
			<hr color="black" class="flex-grow-1" />
				<span class="px-5 font-weight-bold large align-self-center">
					Download any .BMD file from FreeBMD
				</span>
			<hr color="black" class="flex-grow-1" />
		</div>

		<br>
		
		<div class="form-group row">
			<label for="fetch_bmd" class="col-2 pl-0">Name of BMD file</label>
			<input type="text" class="form-control col-2 pl-0" id="fetch_bmd" name="fetch_bmd" value="<?php echo esc($session->fetch_bmd); ?>">
			<label for="fetch_bmd_dl" class="col-2">Confirm download</label>
			<input type="radio" class="form-control col-1" name="fetch_bmd_dl" value="OK" id="fetch_bmd_dl">
		</div>
	
		<div class="row mt-4 d-flex justify-content-between">	
			<a class="btn btn-primary btn-sm" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">Return</a>
			<button class="btn btn-primary btn-sm">Submit</button>
		</div>
		
		
	
	</form>

	
	
	<script type="text/javascript">
		$( document ).ready(function() 
		{
			let $button_refresh_syndicates = $('#refresh_syndicates');
			$button_refresh_syndicates.on("click",function()
				{
					let $spinner = $('.spinner-border');
					$spinner.addClass("active");
				});
		});
	</script>
