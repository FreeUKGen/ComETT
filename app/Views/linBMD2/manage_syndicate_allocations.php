	<?php $session = session(); ?>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-hover table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr class="text-primary">
					<th>Transcriber</th>
					<th>Allocation Name</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Last page uploaded</th>
					<th>Status</th>
					<th>Last change date/time</th>
					<th>Last Action Performed</th>
					<th>
						<input class="no-sort" id="search" type="text" placeholder="Search..." >		
					</th>
					<th class="no-sort"></th>
				</tr>
			</thead>

			<tbody id="user_table">
				<?php foreach ($session->all_records as $allocation): ?>
					<tr>	
						<td><?= esc($allocation['BMD_user'])?></td>
						<td><?= esc($allocation['BMD_allocation_name'])?></td>
						<td><?= esc($allocation['BMD_start_date'])?></td>
						<td><?= esc($allocation['BMD_end_date'])?></td>
						<td><?= esc($allocation['BMD_last_uploaded'])?></td>
						<td><?= esc($allocation['BMD_status'])?></td>
						<td><?= esc($allocation['Change_date'])?></td>
						<td><?= esc($allocation['BMD_last_action'])?></td>
						<td>
							<label for="next_action" class="sr-only">Next action</label>
								<select class="box" name="next_action" id="next_action">
									<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
										 <?php if ( $transcription_cycle['BMD_cycle_type'] == 'SYALL' ): ?>
											 <option value="<?= esc($transcription_cycle['BMD_cycle_code'])?>">
												<?= esc($transcription_cycle['BMD_cycle_name'])?>
											</option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
						</td>
						<td>
							<button  
								data-index="<?= esc($allocation['BMD_identity_index']); ?>" 
								class="go_button btn btn-success">Go
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?=(base_url('syndicate/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_identity_index" id="BMD_identity_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0" href="<?=(base_url('syndicate/manage_syndicates/0')); ?>">
			<?php echo $session->current_project[0]['back_button_text']?>
		</a>
	</div>

<script>
	
$(document).ready(function()
	{	
		$('.go_button').on("click", function()
			{
				// define the variables
				var id=$(this).data('index');
				var BMD_next_action=$(this).parents('tr').find('select[name="next_action"]').val();
				// load variables to form
				$('#BMD_identity_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>


