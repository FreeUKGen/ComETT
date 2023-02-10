	<?php $session = session(); ?>
	
	<br><br><br><br><br><br>
	<div class="step1">
		<form action="<?php echo(base_url($session->return_route)) ?>" method="post">
			
			<div class="form-group row">
				<label class="col-3 pl-0" for="synonym">Is this district a synonym for =>?</label>
				<input type="text" class="form-control col-2 pl-0" id="synonym" name="synonym" value="<?php echo esc($session->synonym);?>">
				<label for="confirm_synonym" class="col-2 pl-0">Confirm synonym?</label>
				<select name="confirm_synonym" id="confirm_synonym" class="col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->confirm ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
			</div>
			
			<div class="row mt-4">
				<h4><b>OR</b></h4>
			</div>
			
			<div class="form-group row mt-4">
				<label for="confirm" class="col-3 pl-0">Confirm district?</label>
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


<script>
$(document).ready(function()
	{	
		$( "#synonym" ).autocomplete(
				{
					minLength: 2,
					source: "<?php echo(base_url('transcribe/search_synonyms')) ?>",
					focus: function( event, ui ) 
						{
							$( "#synonym" ).val( ui.item.label );
							return false;
						},
					select: function( event, ui ) 
						{
							$( "#synonym" ).val( ui.item.label );   
							return false;
						}
				})	
	});
  </script>
	
