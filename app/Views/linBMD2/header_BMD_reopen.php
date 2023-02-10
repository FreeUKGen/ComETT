	<?php $session = session(); ?>
			
	<br><br><br><br><br><br>
	<form action="<?php echo(base_url('header/reopen_BMD_step2')) ?>" method="post">
		<div class="form-group row">
			<label for="BMD_file" class="col-3 pl-0">Transcription to reopen</label>
			<input type="text" class="form-control col-2" id="BMD_file" name="BMD_file" aria-describedby="userHelp" value="<?php echo($session->BMD_file) ?>">
			<small id="userHelp" class="form-text text-muted col-6">eg, 1988BL0319</small>
		</div>		
		<div class="form-group row">
			<label for="BMD_reopen_confirm" class="col-3 pl-0">Reopen this transcription?</label>
			<select name="BMD_reopen_confirm" id="BMD_reopen_confirm" class="col-1">
				<?php foreach ($session->yesno as $key => $value): ?>
					 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->BMD_reopen_confirm ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
				<?php endforeach; ?>
			</select>
			<small id="userHelp" class="form-text text-muted col-6">Yes, will reopen the transcription so that you can make changes.</small>
		</div>
			
		<div class="row d-flex justify-content-end mt-4">
			<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
				<span>Reopen scan</span>
			</button>
		</div>

	</form>

