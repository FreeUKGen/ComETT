
	<?php $session = session();
	use App\Models\Transcription_Comments_Model; ?>
	
	<div class="row">
		<p 
			class="bg-warning col-12 pl-0 text-center font-weight-bold" 
			style="font-size:1.5vw;">
			
			<?php
				echo 'Transcription Home Page - '; 
				if ( $session->status == '0' ) 
					{ 
						?>
						<a href="<?=(base_url('transcribe/toogle_transcriptions'))?>"><?php echo 'ACTIVE transcriptions for => '.$session->identity_userid.' <= transcribing for '.$session->syndicate_name; ?></a>
					<?php
					}
				else
					{
						?>
						<a href="<?=(base_url('transcribe/toogle_transcriptions'))?>"><?php echo 'CLOSED transcriptions for => '.$session->identity_userid.' <= transcribing for '.$session->syndicate_name; ?></a>
					<?php
					}
					?>
		</p>
	</div>
	
	<div class="row text-center table-responsive w-auto" style="max-height: 450px;">
		<table class="table table-borderless" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr class="text-primary">
					<th>Allocation</th>
					<th>Transcription</th>
					<th>Scan</th>
					<th>N° lines trans</th>
					<th>Start Date</th>
					<th>Last change date/time</th>
					<th>Verified</th>
					<th>Upload Date</th>
					<th>Upload Status</th>
					<th>Transcription Comments</th>
					<th>Last Action Performed</th>
					<th>
						<input class="box no-sort" id="search" type="text" placeholder="Search..." >		
					</th>
					<th class="no-sort"></th>
					
					<?php
						if ( $session->status == '0' )
							{
							?>
								<th class="no-sort"></th>
							<?php
							}
					?>
				</tr>		
			</thead>

			<tbody id="user_table">
				<?php foreach ($session->transcriptions as $transcription) 
					{
						if ( $transcription['BMD_header_index'] == $session->current_header_index )
							{ ?>
								<tr class="alert alert-success">
							<?php 
							}
						else
							{ ?>
								<tr class="alert alert-light">
							<?php 
							} ?>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_allocation_name'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_file_name'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_scan_name'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_records'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_start_date'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['Change_date'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['verified'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_submit_date'])?></td>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_submit_status'])?></td>
									<?php	if ( is_null($transcription['comment_text']) )
												{ ?>
													<td style="border-bottom: 2pt solid green;"></td>
												<?php
												}
											else
												{ ?>
													<td  style="border-bottom: 2pt solid green;" title="<?=esc($transcription['comment_text'])?>"><?= esc(ellipsize($transcription['comment_text'], 30, .5, '...'))?></td>
												<?php
												} ?>
									<td style="border-bottom: 2pt solid green;"><?= esc($transcription['BMD_last_action'])?></td>
									
								
									<?php
										if ( $session->status == '0' )
											{
											?>
												<td style="border-bottom: 2pt solid green;">
														<select class="box" name="next_action" id="next_action">
															<?php foreach ($session->transcription_cycles as $key => $transcription_cycle): ?>
																 <?php if ( $transcription_cycle['BMD_cycle_type'] == 'TRANS' ): ?>
																	<option value="<?= esc($transcription_cycle['BMD_cycle_code'])?>">
																		<?= esc($transcription_cycle['BMD_cycle_name'])?>
																	</option>
																<?php endif; ?>
															<?php endforeach; ?>
														</select>
												</td>
											<?php
											}
									?>
									<td style="border-bottom: 2pt solid green;">
										<button  
											data-id="<?= esc($transcription['BMD_header_index']); ?>" 
											class="go_button">Go
										</button>
									</td>						
							</tr>
				
					<?php 
					} ?>
			</tbody>
		</table>
	</div>
	
	<div>
		<form action="<?=(base_url('transcribe/next_action')); ?>" method="POST" name="form_next_action" >
			<input name="BMD_header_index" id="BMD_header_index" type="hidden" />
			<input name="BMD_next_action" id="BMD_next_action" type="hidden" />
		</form>
	</div>
	
	<?php
	if ( $session->masquerade == 0 )
		{ ?>	
			<div class="row mt-4 d-flex justify-content-between">
				<a class="btn btn-primary mr-0 d-flex" href="<?=(base_url('allocation/manage_allocations/0')) ?>">Manage your <?php echo $session->current_project[0]['project_name'] ?> Allocations</a>
				<a class="btn btn-primary mr-0 d-flex" href="<?=(base_url('transcription/reopen_BMD_step1/0')) ?>">Reopen <?php echo $session->current_project[0]['project_name'] ?> Transcription</a>
				<a class="btn btn-primary mr-0 d-flex" href="<?=(base_url('transcription/create_BMD_step1/0')) ?>">Create a new <?php echo $session->current_project[0]['project_name'] ?> Transcription</a>
			</div>
		<?php
		}
	else
		{ ?>
			<div class="row mt-4 d-flex justify-content-between">
				<a class="btn btn-primary mr-0 d-flex" href="<?=(base_url('syndicate/manage_users_step1/'.$session->saved_syndicate_index)) ?>">Go back to Manage Users in your syndicate</a>
				<a class="btn btn-primary mr-0 d-flex" href="<?=(base_url('syndicate/stop_masquerading/')) ?>"><?php echo 'Stop masquerading as Transcriber => '.$session->identity_userid; ?> </a>
			</div>
		<?php
		} ?>

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


