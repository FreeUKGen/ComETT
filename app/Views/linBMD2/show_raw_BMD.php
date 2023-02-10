	<?php $session = session(); ?>
	
	<?php
	// initialise
	$path = getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files';
	$file = $session->transcribe_header[0]['BMD_file_name'].'.BMD';
	// read content
	$content = "
		<code>
			<pre>".htmlspecialchars(file_get_contents("$path/$file"))."</pre>
		</code>";
	// display
	?>
	<div class="row table-responsive w-auto" style="height:500px">
		<?php
			echo $content;
		?>
	</div>
		
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
	</div


		
	



