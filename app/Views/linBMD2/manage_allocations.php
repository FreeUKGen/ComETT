	<?php $session = session(); ?>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-hover table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th>Allocation Name</th>
					<th>Start Date</th>
					<th>End Date</th>
					<th>Last page uploaded</th>
					<th>Status</th>
					<th>Last Action Performed</th>
					<th colspan="2">What do you want to do?</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($session->allocations as $allocation): ?>
					<?php 	if ( $allocation['BMD_status'] == 'Open' )
									{ ?>
										<tr class="alert alert-success">
						<?php }
								else
									{ ?>
										<tr class="alert alert-light">
						<?php } ?>	
											<td><?= esc($allocation['BMD_allocation_name'])?></td>
											<td><?= esc($allocation['BMD_start_date'])?></td>
											<td><?= esc($allocation['BMD_end_date'])?></td>
											<td><?= esc($allocation['BMD_last_uploaded'])?></td>
											<td><?= esc($allocation['BMD_status'])?></td>
											<td><?= esc($allocation['BMD_last_action'])?></td>
											<td>
												<label for="next_action" class="sr-only">Next action</label>
													<select name="next_action" id="next_action">
														<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
															<?php if ( $transcription_cycle['BMD_cycle_type'] == 'ALLOC' ): ?>
																 <option value="<?= esc($transcription_cycle['BMD_cycle_code'])?>">
																	<?= esc($transcription_cycle['BMD_cycle_name'])?>
																</option>
															<?php endif; ?>
														<?php endforeach; ?>
													</select>
											</td>
											<td>
												<button  
													data-id="<?= esc($allocation['BMD_allocation_index']); ?>" 
													class="go_button btn btn-success btn-sm">Go
												</button>
											</td>
										</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?=(base_url('allocation/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_allocation_index" id="BMD_allocation_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?=(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
		<a class="btn btn-primary btn-sm" href="<?=(base_url('allocation/create_allocation_step1/0')) ?>">Create a new allocation</a>
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
				$('#BMD_allocation_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>

