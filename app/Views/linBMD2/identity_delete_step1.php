	<?php
	// initialise 
	$session = session();
	?>
			
<div>
	<div class="row">
		<p class="bg-info col-12 pl-0 text-center" style="font-size:2vw;"><?php echo 'Delete FreeComETT transcriber data for a transcriber in project => '.$session->current_project[0]['project_name'].'.'?></p>
	</div>

	<div class="row table-responsive w-auto text-center" style="height:450px">
		<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th>Name (click to delete data)</th>
					<th>Identity</th>
					<th>Email</th>
					<th>Administrator Role?</th>
				</tr>
			</thead>

			<tbody id="content">
				<?php 
				if ( $session->delete_ids ) 
					{		 
						// read each line in turn
						foreach ( $session->delete_ids as $key => $identity ) 
							{
								?>
									<tr>
											<div class="row">
												<td class="text-center">
													<a id="select_line" href="<?php echo(base_url('database/delete_user_data_step2/'.$key))?>">
													<span><?= $identity['realname'];?></span>
												</td>
												<td class="text-center"><?php echo($identity['BMD_user']);?></td>
												<td class="text-center"><?php echo($identity['emailid']);?></td>
												<td class="text-center"><?php echo($identity['role_name']);?></td>
											</div>
									</tr>
							<?php
							} ?>
					<?php
					} ?>
			</tbody>
		</table>
	</div>
</div>

<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0" href="<?php echo(base_url('database/coord_step1/0')); ?>">
			<?php echo $session->current_project[0]['back_button_text']?>
		</a>
	</div>
