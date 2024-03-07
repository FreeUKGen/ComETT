	<?php
	// initialise 
	$session = session();
	?>
			
<div>
	<div class="row">
		<p class="bg-danger col-12 pl-0 text-center font-weight-bold" style="font-size:2vw;"><?php echo 'With power comes great responsibility!'?></p>
	</div>
	<div class="row">
		<p class="bg-info col-12 pl-0 text-center" style="font-size:2vw;"><?php echo 'Please confirm delete all FreeComETT transcriber data for, '.$session->identity_userid.', by entering your password.'?></p>
		<?php if ( $session->role_index <= 2 )
			{
				?>
				<p class="bg-warning col-12 pl-0 text-center" style="font-size:2vw;"><?php echo 'ATTENTION! You are deleting data for a FreeComETT administrator or coordinator!'?></p>
				<?php
			}
		?>
	</div>
</div>

<br><br>

<form action="<?php echo(base_url('database/delete_user_data_step3')); ?>" method="post">
			<div class="form-group row">
				<label for="password" class="col-4 pl-0 font-weight-bold">Enter your password to confirm delete data for transcriber</label>
				<input type="password" class="form-control col-2 text-left" id="password" name="password" autofocus value="<?php echo esc($session->password);?>">
			</div>


<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('syndicate/manage_users_step1/'.$session->saved_syndicate_index)); ?>">
			<?php echo $session->current_project[0]['back_button_text']?>
		</a>
		
		<button type="submit" class="btn btn-primary mr-0 d-flex">
			<span>Apply</span>	
		</button>
</div>
