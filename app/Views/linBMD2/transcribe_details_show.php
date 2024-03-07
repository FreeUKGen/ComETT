	<?php
	// initialise 
	$session = session();
	$lines_span_C = 0;
	$lines_span_T = 0;
	$lines_span_N = 0;
	$lines_span_B = 0;
	use App\Models\Detail_Comments_Model;
	use App\Models\Transcription_Detail_Def_Model;
	$transcription_detail_def_model = new Transcription_Detail_Def_Model();
	?>
			
<div>
	<div class="row table-responsive w-auto text-center" style="height:150px">
		<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
			<thead>
				<tr>
					<th>LineNo</th>
					<?php
					if ( $session->BMD_cycle_code != 'VERIT' )
						{ ?>
							<th>Status</th>
							<th></th>
							<th></th>
						<?php
						} ?>
					<?php
						foreach ($session->current_transcription_def_fields as $table_header) 
							{ ?>		
								<th><?=$table_header['column_name'];?></th>
							<?php
							} ?>
					<th>Annotations</th>
					<th>Verified?</th>
				</tr>
			</thead>

			<tbody id="content">
				<?php if( $session->transcribe_detail_data ): ?>
					<?php 
						// read each line in turn
						foreach ( $session->transcribe_detail_data as $detail )
							{ ?>
								<tr>
									<!-- line no -->
									<td>
										<span><?= esc($detail['BMD_line_sequence']); ?></span>
									</td>
									
									<?php
									if ( $session->BMD_cycle_code != 'VERIT' )
										{ ?>
											<!-- status -->
											<td>
												<?php 
												if ( $detail['BMD_status'] == 0 )
													{ ?>
														<a id="toogle_line" href="<?=(base_url('transcribe/toogle_line_step1/'.esc($detail['BMD_index']))) ?>">
														<span><?= 'ACTIVE';?></span>
													<?php }
												else
													{ ?>
														<a id="toogle_line" href="<?=(base_url('transcribe/toogle_line_step1/'.esc($detail['BMD_index']))) ?>">
														<span><?= 'DE-ACTIVATED';?></span>
													<?php } ?>
											</td>
											<!-- insert -->
											<td>
												<a id="insert_line" href="<?=(base_url('transcribe/insert_line_step1/'.esc($detail['BMD_index']))) ?>">
												<span><?= 'Insert';?></span>
											</td>
											<!-- change -->
											<td>
												<a id="select_line" href="<?=(base_url($session->controller.'/select_line/'.esc($detail['BMD_index']))) ?>">
												<span><?= 'Modify';?></span>
											</td>
										<?php
										} ?>
									<!-- get all elements for this type and year from DB -->
									<?php
										// loop through element by element
										foreach ( $session->current_transcription_def_fields as $table_line ) 
											{		
												// highlight lines
												switch (TRUE)
													{
														case $session->insert_before_line_sequence == $detail['BMD_line_sequence']:
															?>
																<td id="insert_before_line" class="alert alert-info" style="font-family: sans-serif;">
															<?php
															break;
														case $session->insert_line_sequence == $detail['BMD_line_sequence']:
															?>
																<td id="inserted_line" class="alert alert-warning" style="font-family: sans-serif;">
															<?php
															break;
														case $session->modify_line_sequence == $detail['BMD_line_sequence']:
															?>
																<td id="modified_line" class="alert alert-primary" style="font-family: sans-serif;">
															<?php
															break;
														case $detail['BMD_status'] == 1:
															?>
																<td class="alert alert-danger" style="font-family: sans-serif;">
															<?php
															break;
														case $session->last_detail_index == $detail['BMD_index']:
															if ( $session->BMD_cycle_code == 'VERIT' )
																{ ?>
																	<td id="last_line" class="alert alert-warning" style="font-family: sans-serif;">
																<?php
																}
															else
																{ ?>
																	<td id="last_line" class="alert alert-success" style="font-family: sans-serif;">
																<?php
																} ?>
															<?php
															break;
														default:
															?> 
																<td style="font-family: sans-serif;"> 
															<?php
															break;
													} ?>
												
												<!-- output data -->
												<?php echo esc($detail[$table_line['table_fieldname']]);
											} 
											
											?>
											</td>
											
												<!-- Handle comments -->
												<td>
													<?php
														// get the comment lines and load fields
														$detail_comments_model = new Detail_Comments_Model();
														$session->set('detail_comments', $detail_comments_model	
															->where('BMD_line_index', $detail['BMD_index'])
															->where('BMD_identity_index', $session->BMD_identity_index)
															->where('BMD_header_index', $session->current_transcription[0]['BMD_header_index'])
															->find());
														$comment = '';
														// read the comments if there are any
														if ( $session->detail_comments )
															{
																foreach ($session->detail_comments as $dc)
																	{
																		if ( $dc['BMD_comment_type'] == 'C' )
																			{
																				$lines_span_C = $dc['BMD_comment_span'] - 1;
																			}
																		if ( $dc['BMD_comment_type'] == 'T' )
																			{
																				$lines_span_T = $dc['BMD_comment_span'] - 1;
																			}
																		if ( $dc['BMD_comment_type'] == 'N' )
																			{
																				$lines_span_N = $dc['BMD_comment_span'] - 1;
																			}
																		if ( $dc['BMD_comment_type'] == 'B' )
																			{
																				$lines_span_B = $dc['BMD_comment_span'] - 1;
																			}
																		if ( $dc['BMD_comment_type'] == 'P' )
																			{
																				$lines_span_B = $dc['BMD_comment_span'] - 1;
																			}
																		$lines_index = $detail['BMD_index'];
																		$comment = $comment.$dc['BMD_comment_type'].$dc['BMD_comment_span'].' ';
																	}
															}
															else
															{
																$found = 'N';
																if ( $lines_span_C > 0 )
																	{
																		$comment = $comment.'c ';
																		$lines_span_C = $lines_span_C - 1;
																		$found = 'Y';
																	}
																if ( $lines_span_T > 0 )
																	{
																		$comment = $comment.'t ';
																		$lines_span_T = $lines_span_T - 1;
																		$found = 'Y';
																	}
																if ( $lines_span_N > 0 )
																	{
																		$comment = $comment.'n ';
																		$lines_span_N = $lines_span_N - 1;
																		$found = 'Y';
																	}
																if ( $lines_span_B > 0 )
																	{
																		$comment = $comment.'b ';
																		$lines_span_B = $lines_span_B - 1;
																		$found = 'Y';
																	}
																if ( $found == 'N' )
																	{
																		$lines_index = $detail['BMD_index'];
																	}
															}
													?>
													<a id="select_line" href="<?=(base_url($session->controller.'/select_comment/'.esc($lines_index))) ?>"</a>
													<?php if ( empty($comment) ) 
														{ ?> 
															<span><?= '+';?></span>
														<?php }
														else 
															{?>
															<span><?= esc($comment);?></span>
														<?php }?>
												</td>
												
												<!-- Handle verify flag -->
												<td>
													<?php echo esc($detail['line_verified']);?>
												</td>
								</tr>
							<?php
							} 
							?>

				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>


