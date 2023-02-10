	<?php $session = session(); ?>
	
	<div class="step1">
		<form action="<?php echo(base_url('allocation/create_allocation_step2')) ?>" method="post">
			<div class="form-group row">
				<label for="syndicate" class="col-2 pl-0">Choose a Syndicate</label>
				<select name="syndicate" id="syndicate" class="col-8">
					<option value="NONE">Please select a syndicate...or refresh syndicates =></option>
					<?php foreach ($session->syndicates as $syndicate): ?>
						 <option value="<?php echo esc($syndicate['BMD_syndicate_index'])?>"><?php echo esc($syndicate['BMD_syndicate_name'])?></option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<div class="form-group row">
				<label for="year" class="col-2 pl-0">Allocation year</label>
				<input type="text" class="form-control col-1" id="year" name="year" aria-describedby="userHelp" value="<?php echo esc($session->year); ?>">
				<small id="userHelp" class="form-text text-muted col-1">eg. 1988</small>
				<label for="quarter" class="col-2">Allocation quarter</label>
				<select name="quarter" id="quarter" class="col-2">
					<?php foreach ($session->quarters_short_long as $key => $quarter): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->quarter ) {echo esc(' selected');} ?>><?php echo esc($quarter)?></option>
					<?php endforeach; ?>
				</select>
				<label for="type" class="col-2 pl-0">Allocation type</label>
				<select name="type" id="type" class="col-2">
					<?php foreach ($session->types_lower as $key => $type): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->type ) {echo esc(' selected');} ?>><?php echo esc($type)?></option>
					<?php endforeach; ?>
				</select>
				<small id="userHelp" class="form-text text-muted col-2">Select from list</small>
			</div>
			
			<div class="form-group row">
				<label for="letter" class="col-2 pl-0">Allocation letter</label>
				<select name="letter" id="letter" class="col-2">
					<?php foreach ($session->alphabet as $key => $letter): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->letter ) {echo esc(' selected');} ?>><?php echo esc($letter)?></option>
					<?php endforeach; ?>
				</select>
				<small id="userHelp" class="form-text text-muted col-2">Select from list</small>
			</div>
			
			<div class="form-group row">
				<label for="start_page" class="col-2 pl-0">Page range</label>
				<input type="text" class="form-control col-2" id="start_page" name="start_page" aria-describedby="userHelp" value="<?php echo esc($session->start_page); ?>">
				<small id="userHelp" class="form-text text-muted col-2"></small>
				<input type="text" class="form-control col-2" id="end_page" name="end_page" aria-describedby="userHelp" value="<?php echo esc($session->end_page); ?>">
				<small id="userHelp" class="form-text text-muted col-2">Enter Page range from - to</small>
			</div>
			
			<div class="form-group row">
				<label for="autocreate" class="col-2 pl-0">Auto create name?</label>
				<select name="autocreate" id="autocreate" class="col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->autocreate ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
				<label for="name" class="col-2">Allocation Name</label>
				<input type="text" class="form-control col-6" id="name" name="name" aria-describedby="userHelp" value="<?php echo esc($session->name); ?>">
			</div>
		
			<div class="row mt-4 d-flex justify-content-between">	
				<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
					<span>Return</span>
				</a>
				<button type="submit" class="create_allocation btn btn-primary mr-0 d-flex flex-column align-items-center">
					<span>Create Allocation</span>
					<span class="spinner-border"  role="status">
						<span class="sr-only">Loading...</span>
					</span>		
				</button>
			</div>
	
	</form>

	<script type="text/javascript">
		$( document ).ready(function() 
		{	
			let $create_allocation = $('.create_allocation');
			$create_allocation.on("click",function()
				{
					let $spinner = $('.spinner-border');
					$spinner.addClass("active");
				});
		});
	</script>
