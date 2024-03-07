	<?php $session = session();	?>
	

	
	<div class="row table-responsive w-auto text-center">
		<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th colspan="12" class="bg-success text-center">Current Value (Default Value)</th>
				</tr>
				
				<tr>					
					<th>Field</th>
					<th class="text-center">Font size </th>
					<th class="text-center">Font weight</th>
					<th class="text-center">Pad left</th>
					<th class="text-center">Field align</th>
					<th class="text-center">Capitalise</th>
					<th class="text-center">Roman Volume?</th>
					<th class="text-center">Auto Full-stop?</th>
					<th class="text-center">Auto Copy?</th>
					<th class="text-center">Auto Focus?</th>
					<th class="text-center">Colour</th>
					<th class="text-center">Format</th>
				</tr>
			</thead>
		
			<tbody id="content">						
				<?php
				// loop through element by element
				foreach ($session->standard_def as $key => $def) 
					{ 
						?>		
						<!-- output data -->
						<tr>
							<!-- change -->
							<td>
								<a id="select_line" href="<?=(base_url('transcribe/enter_parameters_step2/0/'.esc($key))) ?>">
								<span><?= esc($def['column_name']);?></span>
							</td>
							<td><?= esc($session->current_transcription_def_fields[$key]['font_size'].' ('.$def['font_size'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['font_weight'].' ('.$def['font_weight'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['pad_left'].' ('.$def['pad_left'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['field_align'].' ('.$def['field_align'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['capitalise'].' ('.$def['capitalise'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['volume_roman'].' ('.$def['volume_roman'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['auto_full_stop'].' ('.$def['auto_full_stop'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['auto_copy'].' ('.$def['auto_copy'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['auto_focus'].' ('.$def['auto_focus'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['colour'].' ('.$def['colour'].')');?></td>
							<td><?= esc($session->current_transcription_def_fields[$key]['field_format'].' ('.$def['field_format'].')');?></td>
						</tr>
					<?php
					} ?>
			</tbody>
		</table>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		
		<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url($session->controller.'/transcribe_'.$session->controller.'_step1/0')); ?>">
			<span><?php echo $session->current_project[0]['back_button_text']?></span>
		</a>
		
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('/transcribe/inherit_parameters')); ?>">
			<span>Inherit parameters from last Transcription in same Allocation</span>
		</a>
		
		<?php 
		if ( $session->current_identity[0]['role_index'] <= 2 )
			{
				?>
				<a id="update_def_fields" class="btn btn-primary mr-0" href="<?php echo(base_url('database/update_def_fields')); ?>">
					<span>Co-ordinator ONLY => Update Standard def field values</span>
				</a>
				<?php
			}
		?>
	</div>
		
			
		</form>
	</div>
	
