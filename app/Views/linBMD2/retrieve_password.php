	<?php $session = session(); ?>
	
	<form action="<?php echo(base_url('identity/retrieve_password_step2')) ?>" method="post">
	
		<div class="form-group row">
			<label for="identity">Identity</label>
			<input type="text" class="form-control" id="identity" name="identity" aria-describedby="userHelp" value="<?php echo($session->identity) ?>">
			<small id="userHelp" class="form-text text-muted">This must be your FreeBMD user name.</small>
		</div>
	  
		<div class="form-group row">
				<label for="email">Your email</label>
				<input type="email" class="form-control" id="email" name="email" value="<?php echo($session->email) ?>">
				<small id="userHelp" class="form-text text-muted">This must be the email address attached to your account.</small>
		</div>
	
		<div class="row d-flex justify-content-end mt-4">
			<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
				<span>Retrieve Password</span>	
			</button>
		</div>
		

	</form>


