	<?php $session = session(); ?>
	
	<div>
		<div class="alert alert-dark row" role="alert">
			<span class="col-12 text-center"><b><?= esc($session->table_title) ?></b></span>
		</div>
		<div class="row table-responsive w-auto text-center" style="height:250px">
			<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
				<thead class="sticky-top bg-white">
					<tr>
						<?php
							foreach ($session->current_transcription_def_fields as $table_header) 
								{ ?>		
									<th><?=$table_header['column_name'];?></th>
								<?php }
						?>
					</tr>
				</thead>

				<tbody id="content">
					<?php if( $session->current_line ): ?>
						<?php 
							// read each line in turn
							foreach ($session->current_line as $detail): ?>
							
							<tr>
								<!-- get all elements for this type and year from DB -->
								<?php
									// loop through element by element
									foreach ($session->current_transcription_def_fields as $table_line) 
										{		
											// highlight last line
											if ( $session->last_detail_index == $detail['BMD_index'] ) { ?>
											<td class="alert alert-success" style="font-family: sans-serif;">
											<?php } else { ?> 
											<td style="font-family: sans-serif;"> <?php } ?>
												<!-- output data -->
												<?= esc($detail[$table_line['table_fieldname']]);}?>
											</td>									
							<?php endforeach; ?>
							</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>

	
	<div class="step1">
		<form action="<?php echo(base_url('transcribe/toogle_line_step2')) ?>" method="post">
			
			<div class="form-group row">
				
				<label for="confirm" class="col-2 pl-0"><?= 'Confirm '.$session->action ?></label>
				<select name="confirm" id="confirm" class="box col-2">
					<?php foreach ($session->yesno as $key => $value): ?>
						 <option value="<?php echo esc($key)?>"<?php if ( $key == $session->confirm ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
			</div>
		
		<div class="row d-flex justify-content-end mt-4">
				<button type="submit" class="btn btn-primary mr-0 d-flex">
					<span>Continue</span>	
				</button>
			</div>
			
		</form>
	</div>
	
