	<?php $session = session(); ?>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-hover table-striped table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr class="font-italic text-info">
					<th colspan="5">Allocation</th>
					<th colspan="4">Syndicate</th>
				</tr>
				<tr>
					<th>BMD File</th>
					<th>BMD Scan Name</th>
					<th>N° lines trans</th>
					<th>Start date</th>
					<th>Last change date</th>
					<th>Upload date</th>
					<th>Upload status</th>
					<th>Last Action Performed</th>
					<th colspan="2">What do you want to do?</th>
				</tr>
			</thead>

			<tbody>
				<?php foreach ($session->headers as $header): ?>
					
					<tr class="font-italic text-info">
						<td colspan="5"><?= esc($header['BMD_allocation_name'])?></td>
						<td colspan="4"><?= esc($header['BMD_syndicate_name'])?></td>
					</tr>
					<?php 	if ( $header['BMD_header_index'] == $session->current_header_index )
							{ ?>
								<tr class="alert alert-success">
						<?php }
						else
							{ ?>
								<tr class="alert alert-light">
						<?php } ?>
						<td><?= esc($header['BMD_file_name'])?></td>
						<td><?= esc($header['BMD_scan_name'])?></td>
						<td><?= esc($header['BMD_records'])?></td>
						<td><?= esc($header['BMD_start_date'])?></td>
						<td><?= esc($header['Change_date'])?></td>
						<td><?= esc($header['BMD_submit_date'])?></td>
						<td><?= esc($header['BMD_submit_status'])?></td>
						<td><?= esc($header['BMD_last_action'])?></td>
						<td>
							
							<label for="next_action" class="sr-only">Next action</label>
								<select name="next_action" id="next_action">
									<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
										 <?php if ( $transcription_cycle['BMD_cycle_type'] == 'TRANS' ): ?>
											 <option value="<?= esc($transcription_cycle['BMD_cycle_code'])?>">
												<?= esc($transcription_cycle['BMD_cycle_name'])?>
											</option>
										<?php endif; ?>
									<?php endforeach; ?>
								</select>
						</td>
						<td>
							<button  
								data-id="<?= esc($header['BMD_header_index']); ?>" 
								class="go_button btn btn-success btn-sm">Go
							</button>
						</td>					
					</tr>
			
				 <?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?=(base_url('transcribe/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_header_index" id="BMD_header_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">
		<a class="btn btn-primary btn-sm" href="<?=(base_url('syndicate/manage_syndicates/0')) ?>">Manage Syndicates</a>
		<a class="btn btn-primary btn-sm" href="<?=(base_url('allocation/manage_allocations/0')) ?>">Manage Allocations</a>
		<a class="btn btn-primary btn-sm" href="<?=(base_url('header/reopen_BMD_step1/0')) ?>">Reopen Transcription</a>
		<a class="btn btn-primary btn-sm" href="<?=(base_url('header/create_BMD_step1/0')) ?>">Start a new BMD scan transcription</a>
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
				$('#BMD_header_index').val(id);
				$('#BMD_next_action').val(BMD_next_action);
				// and submit the form
				$('form[name="form_next_action"]').submit();
			});
	});

</script>


