	<?php $session = session(); ?>
	
	<div class="row">
		<label for="manage_allocations" class="col-8 pl-0">Manage Allocations?</label>
		<a id="manage_allocations" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('allocation/manage_allocations/0')) ?>">
			<span>Manage Allocations</span>
		</a>
	</div>
	
	<div class="row">
		<label for="manage_syndicates" class="col-8 pl-0">Manage Syndicates?</label>
		<a id="manage_syndicates" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('syndicate/manage_syndicates/0')) ?>">
			<span>Manage Syndicates</span>
		</a>
	</div>
	
	<div class="row">
		<label for="manage_surnames" class="col-8 pl-0">Manage Surnames?</label>
		<a id="manage_surnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('surname/manage_surnames/0')) ?>">
			<span>Manage Surnames</span>
		</a>
	</div>
	
	<div class="row">
		<label for="manage_firstnames" class="col-8 pl-0">Manage Firstnames?</label>
		<a id="manage_firstnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('firstname/manage_firstnames/0')) ?>">
			<span>Manage Firstnames</span>
		</a>
	</div>
	
	<br>
	
	<div class="row">
		<label for="firstnames" class="col-8 pl-0">Show given names</label>
		<a id="firstnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/firstnames')) ?>">
			<span>Show given names</span>
		</a>
	</div>
	
	<div class="row">
		<label for="surnames" class="col-8 pl-0">Show family names</label>
		<a id="surnames" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/surnames')) ?>">
			<span>Show family names</span>
		</a>
	</div>
	
	<div class="row">
		<label for="issues" class="col-8 pl-0">Issue Tracker</label>
		<a id="issues" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" target="_blank" href="https://docs.google.com/spreadsheets/d/1quaP9rhInmqlLeRzSZGDxi-Xlbto_U80ucV1f7J0Nek/edit?usp=sharing">
			<span>Issue Tracker</span>
		</a>
	</div>
	
	<br>
	
	<?php if ( $session->user[0]['BMD_admin'] == 'Y' )
		{
			?>
				<div class="row">
					<label for="districts_staleness" class="col-8 pl-0">Test to see if your local Districts database is stale before refreshing.</label>
					<a id="districts_staleness" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/districts_staleness')) ?>">
						<span>Districts stale?</span>
						<span id="districts_staleness_spinner" class="spinner-border"  role="status">
							<span class="sr-only">Loading...</span>
						</span>
					</a>
				</div>
				
				<div class="row">
					<label for="districts_refresh" class="col-8 pl-0">Refresh districts and volumes database?</label>
					<a id="districts_refresh" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/districts_refresh')) ?>">
						<span>Refresh Districts</span>
						<span id="districts_refresh_spinner" class="spinner-border"  role="status">
							<span class="sr-only">Loading...</span>
						</span>
					</a>
				</div>
				
				<div class="row">
					<label for="admin-user" class="col-8 pl-0">Give or remove webBMD admin rights to an existing webBMD user.</label>
					<a id="admin-user" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('identity/admin_user_step1/0')) ?>">
						<span>Admin user</span>
					</a>
				</div>
				
				<div class="row">
					<label for="merge_first_names" class="col-8 pl-0">Merge second and third names to first name</label>
					<a id="merge_first_names" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/merge_names')) ?>">
						<span>Merge forenames</span>
					</a>
				</div>
				
				<div class="row">
					<label for="create_dimensions" class="col-8 pl-0">Create data entry table dimensions for existing headers</label>
					<a id="create_dimensions" class="btn btn-outline-primary btn-sm col-4 d-flex flex-column align-items-center" href="<?php echo(base_url('housekeeping/create_header_data_entry_dimensions')) ?>">
						<span>Create data entry table dimensions</span>
					</a>
				</div>
		<?php
		}
		?>
		
	<br>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
	</div>
	
	<script type="text/javascript">
		$( document ).ready(function() 
		{
			let $button_districts_staleness = $('#districts_staleness');
			$button_districts_staleness.on("click",function()
				{
					let $districts_staleness_spinner = $('#districts_staleness_spinner');
					$districts_staleness_spinner.addClass("active");
				});
				
			let $button_districts_refresh = $('#districts_refresh');
			$button_districts_refresh.on("click",function()
				{
					let $districts_refresh_spinner = $('#districts_refresh_spinner');
					$districts_refresh_spinner.addClass("active");
				});
			
			let $button_export_names = $('#export_names');
			$button_export_names.on("click",function()
				{
					let $export_names_spinner = $('#export_names_spinner');
					$export_names_spinner.addClass("active");
				});
			
			let $button_import_names = $('#import_names');
			$button_import_names.on("click",function()
				{
					let $import_names_spinner = $('#import_names_spinner');
					$import_names_spinner.addClass("active");
				});
		});
	</script>


