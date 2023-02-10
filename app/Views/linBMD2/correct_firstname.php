	<?php $session = session(); ?>
	
	<form action="<?php echo(base_url('firstname/correct_firstname_step2')) ?>" method="post">
	
		<div class="form-group row">
			<label for="identity">Current Firstname</label>
			<input type="text" class="form-control" id="current_firstname" name="current_firstname" aria-describedby="userHelp" value="<?php echo($session->firstname_to_corrected['Firstname']) ?>">
		</div>
	  
		<div class="form-group row">
				<label for="corrected_firstname">Corrected Firstname</label>
				<input type="text" class="form-control" id="corrected_firstname" name="corrected_firstname" value="<?php echo($session->corrected_firstname) ?>">
		</div>		
		
		<div class="row mt-2 d-flex justify-content-between">
			
				<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('firstname/manage_firstnames/0')); ?>">
					<span>Return</span>
				</a>

				<button type="submit" class="btn btn-primary mr-0 flex-column align-items-center">
					<span>Submit</span>	
				</button>
			
		</div>
		
	</form>


