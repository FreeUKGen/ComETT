	<?php $session = session();
	use App\Models\Table_Details_Model;
	use App\Models\Header_Table_Details_Model;
	$table_details_model = new Table_Details_Model();
	$header_table_details_model = new Header_Table_Details_Model();
	?>
	
	<div class="alert alert-dark row" role="alert">
		<span class="col-12 text-center"><b>SET FONT PARAMETERS</b></span>
	</div>
	<div>
		<b>
			<div class="row">
				<p class="col-2 pl-0"></p>
				<p class="col-2 text-center">Data entry font</p>
				<p class="col-2 text-center">Data entry font size (VW)</p>
				<p class="col-2 text-center">Data entry font style</p>
			</div>
		</b>
	</div>
	
	<div>
			<div class="row">
				<p class="col-2 pl-0 font-weight-bold">Default</p>
				<p class="col-2 text-center">MODERN_TYPEWRITER</p>
				<p class="col-2 text-center">2</p>
				<p class="col-2 text-center">bold</p>
			</div>
	</div>
	
	<div>
			<div class="row">
				<p class="col-2 pl-0 font-weight-bold">Current</p>
				<p class="col-2 text-center"><?= $session->transcribe_header[0]['BMD_font_family'];?></p>
				<p class="col-2 text-center"><?= $session->transcribe_header[0]['BMD_font_size'];?></p>
				<p class="col-2 text-center"><?= $session->transcribe_header[0]['BMD_font_style'];?></p>
			</div>
	</div>

	<div>
		<form action="<?php echo(base_url('transcribe/enter_parameters_step2/'.$session->transcribe_header[0]['BMD_header_index'])); ?>" method="post">
			<div class="form-group row">
				<label class="col-2 pl-0 font-weight-bold">New</label>
				<select name="enter_font_family" id="enter_font_family" class="col-2">
					<?php foreach ($session->data_entry_fonts as $key => $value): ?>
						 <option value="<?= esc($value)?>"<?php if ( $value == $session->enter_font_family ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
				
				<input type="text" class="form-control col-2 text-center" id="enter_font_size" name="enter_font_size" autofocus value="<?= esc($session->enter_font_size);?>">
				
				<select name="enter_font_style" id="enter_font_style" class="col-2">
					<?php foreach ($session->data_entry_styles as $key => $value): ?>
						 <option value="<?= esc($value)?>"<?php if ( $value == $session->enter_font_style ) {echo esc(' selected');} ?>><?php echo esc($value)?></option>
					<?php endforeach; ?>
				</select>
			</div>
	</div>
	
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
		<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
			<span>Apply => Set Font Parameters</span>	
		</button>
	</div>
		
			
		</form>
		
	<br>
	
	<!-- Set data entry matrix -->
	<?php
		$session->set('table_details', $table_details_model
			->join('header_table_details', 'table_details.BMD_index = header_table_details.BMD_table_details_index')
			->where('header_table_details.BMD_header_index = '.$session->transcribe_header[0]['BMD_header_index'])
			->where('BMD_controller', $session->controller)
			->where('BMD_show', 'Y')
			->where('BMD_table_attr', 'body')
			->where('BMD_format', $session->format)
			->orderby('BMD_order','ASC')
			->find());
	?>

	<div class="alert alert-dark row" role="alert">
		<span class="col-12 text-center">
			<b>SET DATA ENTRY MATRIX PARAMETERS FOR => </b>
			<select name="select_field" id="select_field">
					<?php foreach ($session->table_details as $key => $td): ?>
						 <option value="<?= esc($key);?>"><?= esc($td['BMD_name']);?></option>
					<?php endforeach; $selected_field = 'firstname'; $key=0;?>
			</select>
		</span>
	</div>
	
	<div class="row table-responsive w-auto text-center" style="height:250px">
		<table class="table table-sm table-hover table-striped table-bordered" style="border-collapse: separate; border-spacing: 0;">
			<thead class="sticky-top bg-white">
				<tr>
					<th><?= $selected_field; ?></th>
					<th>Width</th>
					<th>Align</th>
					<th>Pad Left</th>
				</tr>
			</thead>
		
			<tbody id="content">						
				<tr>
					<td>Default</td>
					<td><?= $session->table_details[$key]['BMD_span']?></td>
					<td><?= $session->table_details[$key]['BMD_align']?></td>
					<td><?= $session->table_details[$key]['BMD_pad_left']?></td>
				</tr>	
				<tr>
					<td>Current</td>
					<td><?= $session->table_details[$key]['BMD_header_span']?></td>
					<td><?= $session->table_details[$key]['BMD_header_align']?></td>
					<td><?= $session->table_details[$key]['BMD_header_pad_left']?></td>
				</tr>
				<tr>
					<td>New</td>
					<td>
						<input type="text" class="form-control col-2 text-center" id="enter_field_span" name="enter_field_span" autofocus value="<?= esc($session->enter_field_span);?>">
						<?= $session->table_details[$key]['BMD_header_span']?></td>
					<td><?= $session->table_details[$key]['BMD_header_align']?></td>
					<td><?= $session->table_details[$key]['BMD_header_pad_left']?></td>
				</tr>
			
			</tbody>
		</table>
	</div>

		
	<div class="row mt-4 d-flex justify-content-between">	
		<a id="return" class="btn btn-primary mr-0 flex-column align-items-center" href="<?php echo(base_url('transcribe/transcribe_step1/0')); ?>">
			<span>Return</span>
		</a>
		<button type="submit" class="btn btn-primary mr-0 d-flex flex-column align-items-center">
			<span>Apply => Set Data Entry Matrix Parameters</span>	
		</button>
	</div>
		
			
		</form>
	</div>
	
