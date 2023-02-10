	<?php $session = session(); ?>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-hover table-striped table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th>Syndicate Name</th>
					<th>Leader</th>
					<th>Email</th>
					<th>BMD Header Credit line</th>
					<th colspan="2">What do you want to do?</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($session->syndicates as $syndicate): ?>	
					<td><?= esc($syndicate['BMD_syndicate_name'])?></td>
					<td><?= esc($syndicate['BMD_syndicate_leader'])?></td>
					<td><?= esc($syndicate['BMD_syndicate_email'])?></td>
					<td><?= esc($syndicate['BMD_syndicate_credit'])?></td>
					<td>
						<label for="next_action" class="sr-only">Next action</label>
							<select name="next_action" id="next_action">
								<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
									<?php if ( $transcription_cycle['BMD_cycle_type'] == 'SYNDC' ): ?>
										 <option value="<?= esc($transcription_cycle['BMD_cycle_code'])?>">
											<?= esc($transcription_cycle['BMD_cycle_name'])?>
										</option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
					</td>
					<td>
						<button  
							data-id="<?= esc($syndicate['BMD_syndicate_index']); ?>" 
							class="go_button btn btn-success btn-sm">Go
						</button>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?=(base_url('syndicate/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_syndicate_index" id="BMD_syndicate_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div>	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?=(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
	</div>

<script>
	
$(document).ready(function()
	{	
		$('.go_button').on("click", function()
			{
				// define the variables
				var id=$(this).data('id');
				var BMD_next_action=$(this).parents('tr').find('select[name="next_action"]').val();
				// load variables to form
				$('#BMD_syndicate_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>


