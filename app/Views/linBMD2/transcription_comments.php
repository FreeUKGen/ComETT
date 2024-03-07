	<?php $session = session(); ?>
	
	<div class="step1">
		<form action="<?php echo(base_url('transcription/comments_step2')) ?>" method="post">
			<!-- show transcription comment text  -->
		<div class="form-group row d-flex align-items-center">
			<label for="comment_text" class="col-2">Comment for this transcription =></label>
			<input type="text" class="form-control col-6" id="comment_text" name="comment_text" aria-describedby="userHelp" value="<?php echo esc($session->comment_text); ?>">
			<small id="userHelp" class="form-text text-muted col-4">You can enter / change a comment at any time for this transcription here if you want. If you want to remove it, just make it blank. The comment will be updated each time you enter a detail line.</small>
		</div>
		
			<div class="row mt-4 d-flex justify-content-between">	
				<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
					<?php echo $session->current_project[0]['back_button_text']?>
				</a>
				<button type="submit" class="create_message btn btn-primary mr-0 d-flex">
					<span>Add / Change / Remove Transcription Comments</span>	
				</button>
			</div>
	
	</form>
