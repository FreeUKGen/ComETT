	<?php $session = session(); ?>
	
	<form action="<?php echo(base_url('identity/admin_user_step2')) ?>" method="post">
		<div class="form-group row">
			<label for="identity" class="col-1 pl-0">Identity</label>
			<input type="text" class="form-control col-1" id="identity" name="identity" aria-describedby="userHelp" value="<?php echo($session->identity) ?>">
			<small id="userHelp" class="form-text text-muted col-2">This must be an existing webBMD user.</small>
		</div>
		<div class="form-check">
			<label class="form-check-label" for="flexRadioDefault1">
			<input class="form-check-input" type="radio" name="admin_action" value="give" id="flexRadioDefault1">
			Give admin rights
			</label>
			</div>
			<div class="form-check">
			<input class="form-check-input" type="radio" name="admin_action" value="remove" id="flexRadioDefault2">
			<label class="form-check-label" for="flexRadioDefault2">
			Remove admin rights
			</label>
		</div>
		
		<div class="alert row mt-2 d-flex justify-content-between">
			
				<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('housekeeping/index/0')); ?>">
					<span>Return</span>
				</a>

				<button type="submit" class="btn btn-primary mr-0 flex-column align-items-center">
					<span>Submit</span>	
				</button>
			
		</div>
	
	</form>


