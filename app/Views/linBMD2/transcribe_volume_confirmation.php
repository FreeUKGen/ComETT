	<?php $session = session(); ?>
	
	<br><br><br><br><br><br>
	<div class="step1">
		<form action="<?php echo(base_url($session->return_route)) ?>" method="post">
			
			<div class="form-group row">
				<label for="volume" class="col-2 pl-0">Enter volume from scan</label>
				<input type="text" class="form-control col-2 pl-0" id="volume" name="volume" autofocus value="<?php echo esc($session->volume);?>">
				<label for="confirm" class="col-2 pl-0">Confirm volume?</label>
				<select name="confirm" id="confirm" class="col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->confirm ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
			</div>
		
		<div class="row d-flex justify-content-end mt-4">
				<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
					<span>Continue</span>	
				</button>
			</div>
			
		</form>
	</div>
	