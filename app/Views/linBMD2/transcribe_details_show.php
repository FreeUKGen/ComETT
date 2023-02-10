	<?php
	// initialise 
	$session = session();
	$lines_span_C = 0;
	$lines_span_T = 0;
	$lines_span_N = 0;
	$lines_span_B = 0;
	use App\Models\Detail_Comments_Model;
	use App\Models\Table_Details_Model;
	$table_details_model = new Table_Details_Model();
	?>
			
<div>
	<div class="alert alert-dark row" role="alert">
		<span class="col-12 text-center"><b><?= esc($session->table_title) ?></b></span>
	</div>
	<div class="row table-responsive w-auto text-center" style="height:250px">
		<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th>Del</th>
					<th>Modify</th>
					<?php 
						$session->set('table_details', $table_details_model	
							->where('BMD_controller', $session->controller)
							->where('BMD_table_attr', 'head')
							->where('BMD_format', $session->format)
							->where('BMD_show', 'Y')
							->orderby('BMD_order','ASC')
							->find());
						foreach ($session->table_details as $th) 
							{ ?>		
								<th><?=$th['BMD_html'];?></th>
							<?php }
					?>
					<th>Annotations</th>
				</tr>
			</thead>

			<tbody id="content">
				<?php if( $session->transcribe_detail_data ): ?>
					<?php 
						// read each line in turn
						foreach ($session->transcribe_detail_data as $detail): ?>
						
						<tr>
						
							<!-- delete -->
							<td>
								<a id="delete_line" href="<?=(base_url('transcribe/delete_line_step1/'.esc($detail['BMD_index']))) ?>">
								<span><?= '-';?></span>
							</td>
							<!-- change -->
							<td>
								<a id="select_line" href="<?=(base_url($session->controller.'/select_line/'.esc($detail['BMD_index']))) ?>">
								<span><?= '#';?></span>
							</td>
							<!-- get all elements for this type and year from DB -->
							<?php
								$session->set('table_details', $table_details_model	
									->where('BMD_controller', $session->controller)
									->where('BMD_table_attr', 'body')
									->where('BMD_format', $session->format)
									->where('BMD_show', 'Y')
									->orderby('BMD_order','ASC')
									->find());
								// loop through element by element
								foreach ($session->table_details as $td) {
									// highlight last line
									if ( $session->last_detail_index == $detail['BMD_index'] ) { ?>
									<td class="alert alert-success" style="font-family: sans-serif;">
									<?php } else { ?> 
									<td style="font-family: sans-serif;"> <?php } ?>
										<!-- output data -->
										<?= esc($detail[$td['BMD_html']]);}?>
									</td>
							
									<!-- Handle comments -->
									<td>
										<?php
											// get the comment lines and load fields
											$detail_comments_model = new Detail_Comments_Model();
											$session->set('detail_comments', $detail_comments_model	
												->where('BMD_line_index', $detail['BMD_index'])
												->where('BMD_identity_index', $session->BMD_identity_index)
												->where('BMD_header_index', $session->transcribe_header[0]['BMD_header_index'])
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
												<span><?= '+';?></span></td>
											<?php }
											else 
												{?>
												<span><?= esc($comment);?></span></td>
											<?php }?>
						</tr>
						<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>
