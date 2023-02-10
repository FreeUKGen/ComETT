<?php namespace App\Controllers;
use App\Models\Header_Model;
use App\Models\Detail_Data_Model;
use App\Models\Detail_Comments_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;
use App\Models\Table_Details_Model;
use App\Models\Header_Table_Details_Model;
	
	function comment_update()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$detail_comments_model = new Detail_Comments_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		// get inputs
		$session->set('comment_type', $_POST['comment_type']);
		$session->set('comment_span', $_POST['comment_span']);
		$session->set('comment_text', $_POST['comment_text']);
		// do tests
		switch ($session->comment_type) 
			{
				case "B":
					// comment span
					if ( $session->comment_span != '' )
						{
							$session->set('comment_span', '');
							$session->set('message_2', 'You cannot enter span for a +BREAK line');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					// comment must be blank
					if ( $session->comment_text != '' )
						{
							$session->set('comment_text', '');
							$session->set('message_2', 'You cannot enter text for a +BREAK line.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					break;
				default:
					// comment span
					if ( ! is_numeric($session->comment_span) )
						{
							$session->set('message_2', 'Span must be a number.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					if ( $session->comment_span <= 0 )
						{
							$session->set('message_2', 'Span must be greater than 0');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					// comment text
					if ( $session->comment_text == '' )
						{
							$session->set('message_2', 'Please enter some text in order to create the annotation.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return;
						}
					break;	
			}
		
		// update record
		$data =	[
					'BMD_identity_index' => $session->BMD_identity_index,
					'BMD_header_index' => $session->transcribe_detail[0]['BMD_header_index'],
					'BMD_line_index' => $session->transcribe_detail[0]['BMD_index'],
					'BMD_line_sequence' => $session->transcribe_detail[0]['BMD_line_sequence'],
					'BMD_comment_type' => $session->comment_type,
					'BMD_comment_span' => $session->comment_span,
					'BMD_comment_text' => $session->comment_text,
				];
				
		// add if line edit = 0 / update if line edit = 1
		if ( $session->line_edit_flag == 0 )
			{
				// insert record
				$detail_comments_model->insert($data);
				$session->set('message_2', 'Annotation line added.');
				$session->set('message_class_2', 'alert alert-success');
				$session->set('message_error', 'success');				
			}
		else
			{
				$detail_comments_model->update($session->line_edit_data[0]['BMD_index'], $data);
				$session->set('message_2', 'Annotation line updated.');
				$session->set('message_class_2', 'alert alert-success');
				$session->set('message_error', 'success');				
			}
			
		// reload data
		$session->set('transcribe_detail_comments', $detail_comments_model	
			->where('BMD_line_index', $session->transcribe_detail[0]['BMD_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_header_index', $session->transcribe_detail[0]['BMD_header_index'])
			->find());
	}
	
	function comment_remove($comment_line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$detail_comments_model = new Detail_Comments_Model();
		// remove record
		$detail_comments_model->delete($comment_line_index);
		$session->set('message_2', 'Annotation line removed.');
		$session->set('message_class_2', 'alert alert-success');
		$session->set('message_error', 'success');
		// reload data
		$session->set('transcribe_detail_comments', $detail_comments_model	
			->where('BMD_line_index', $session->transcribe_detail[0]['BMD_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_header_index', $session->transcribe_detail[0]['BMD_header_index'])
			->find());
		// return
		return;				
	}
	
	function comment_select($detail_line_index)
	{
		// initialse
		$session = session();
		$detail_comments_model = new Detail_Comments_Model();
		$detail_data_model = new Detail_Data_Model();
		// if no error get the data, otherwise just show error
		if ( $session->message_error != 'error' )
			{
				// get the line detail
				$session->set('transcribe_detail', $detail_data_model	
					->where('BMD_index', $detail_line_index)
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('BMD_header_index', $session->transcribe_header[0]['BMD_header_index'])
					->find());
					
				// get the comment lines and load fields
				$session->set('transcribe_detail_comments', $detail_comments_model	
					->where('BMD_line_index', $detail_line_index)
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('BMD_header_index', $session->transcribe_header[0]['BMD_header_index'])
					->find());
				
				// load session fields
				$session->set('line_index', $detail_line_index);
				$session->set('line_sequence', '');
				$session->set('comment_type', '');
				$session->set('comment_span', '');
				$session->set('comment_text', '');
			}
		return;
	}
	
	function delete_line_confirm($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// get the line and load fields
		$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find());
		// set message
		$session->set('message_2', 'You requested to delete line number => '.$session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('message_class_2', 'alert alert-danger');
		// show view
		echo view('templates/header');
		echo view('linBMD2/delete_line_confirmation');
		echo view('templates/footer');
	}
	
	function delete_line_delete()
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$header_model = new Header_Model();
		// get input
		$session->set('delete_ok', $_POST['confirm']);
		// if confirmed delete the line
		if ( $session->delete_ok == 'Y' )
			{
				// delete detail line
				$detail_data_model->delete($session->line_edit_data[0]['BMD_index']);
				// reduce header count
				$data =	[
									'BMD_records' => $session->transcribe_header[0]['BMD_records'] - 1,
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
				// load the header again
				$session->transcribe_header = $header_model
					->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
					->find();
			}
	}
	
	function select_trans_line($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// get the line and load fields
		$session->set('line_edit_data', $detail_data_model->where('BMD_index', $line_index)
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find());
		// save current image parameters
		$session->set('save_panzoom_x', $session->panzoom_x);
		$session->set('save_panzoom_y', $session->panzoom_y);
		$session->set('save_panzoom_z', $session->panzoom_z);
		$session->set('save_sharpen', $session->sharpen);
		$session->set('save_image_r', $session->image_r);
		// load session fields
		$session->set('familyname', $session->line_edit_data[0]['BMD_surname']);
		$session->set('line', $session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('firstname', $session->line_edit_data[0]['BMD_firstname']);
		$session->set('secondname', $session->line_edit_data[0]['BMD_secondname']);
		$session->set('thirdname', $session->line_edit_data[0]['BMD_thirdname']);
		$session->set('partnername', $session->line_edit_data[0]['BMD_partnername']);
		$session->set('district', $session->line_edit_data[0]['BMD_district']);
		$session->set('registration', $session->line_edit_data[0]['BMD_registration']);
		$session->set('page', $session->line_edit_data[0]['BMD_page']);
		$session->set('age', $session->line_edit_data[0]['BMD_age']);
		$session->set('dis_number', $session->line_edit_data[0]['BMD_volume']);
		$session->set('reg_number', $session->line_edit_data[0]['BMD_reg']);
		$session->set('ent_number', $session->line_edit_data[0]['BMD_entry']);
		$session->set('source_code', $session->line_edit_data[0]['BMD_source_code']);
		$session->set('panzoom_x', $session->line_edit_data[0]['BMD_line_panzoom_x']);
		$session->set('panzoom_y', $session->line_edit_data[0]['BMD_line_panzoom_y']);
		$session->set('panzoom_z', $session->line_edit_data[0]['BMD_line_panzoom_z']);
		$session->set('sharpen', $session->line_edit_data[0]['BMD_line_sharpen']);
		$session->set('image_r', $session->line_edit_data[0]['BMD_line_image_rotate']);
		// set line_edit flag
		$session->set('line_edit_flag', 1);
		$session->set('show_view_type', 'transcribe');
		// set message
		$session->set('message_2', 'You requested to edit line number => '.$session->line_edit_data[0]['BMD_line_sequence']);
		$session->set('message_class_2', 'alert alert-warning');
	}
	
	function transcribe_initialise_step1($start_message, $controller, $controller_title)
	{
		$session = session();
		$detail_data_model = new Detail_Data_Model();

		// get all existing details for this header
		$session->transcribe_detail_data = $detail_data_model	
			->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->findAll();
																										
		// set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', $controller_title.' => '.$session->transcribe_header[0]['BMD_file_name'].' => '.$session->transcribe_header[0]['BMD_scan_name'].' => '.$session->transcribe_header[0]['BMD_records'].' records transcribed from this scan. Enter your transcription data from scan image. *=required field **=contextual help available');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					$session->set('element', $session->transcribe_header[0]['BMD_scan_name']);
					// flow control
					$session->set('show_view_type', 'transcribe');
					$session->set('confirm', 'N');
					$session->set('district_ok', 'N');
					$session->set('page_ok', 'N');
					$session->set('volume_ok', 'N');
					$session->set('registration_ok', 'N');
					$session->set('firstname_ok', 'N');
					$session->set('line_edit_flag', 0);
					$session->set('last_detail_index', 0);
					// return routes depend on calling controller
					$session->set('return_route', $controller.'/transcribe_'.$controller.'_step2');
					$session->set('return_route_step1', $controller.'/transcribe_'.$controller.'_step1/0');
					// table title
					$session->set('table_title', $controller_title);
					// controller
					$session->set('controller', $controller);
					// set format 
					// format change year depends on type = controller
					$session->set('format', 'post');
					switch ($session->controller)
						{
							case 'births':
								if ( $session->transcribe_allocation[0]['BMD_year'] < 1993 )
									{
										$session->set('format', 'prior');
									}
								break;
							case 'deaths':
								if ( $session->transcribe_allocation[0]['BMD_year'] < 1993 )
									{
										$session->set('format', 'prior');
									}
								break;
							case 'marriages':
								if ( $session->transcribe_allocation[0]['BMD_year'] < 1994 )
									{
										$session->set('format', 'prior');
									}
								break;
							default:
								break;
						}
					// backup performed
					if ( $session->database_backup_performed == 1 )
						{
							$session->set('table_title', $controller_title.' - database backup performed');
						}
					// set dup fields
					$session->set('dup_firstname', '');
					$session->set('dup_secondname', '');
					$session->set('dup_thirdname', '');
					$session->set('dup_partnername', '');
					$session->set('dup_age', '');
					$session->set('dup_district', '');
					$session->set('dup_registration', '');
					$session->set('dup_page', '');
					$session->set('dup_dis_number', '');
					$session->set('dup_reg_number', '');
					$session->set('dup_ent_number', '');
					$session->set('dup_source_code', '');
					// if detail data
					if ( $session->transcribe_detail_data )
						{
							// get last record in array
							$lastEl = array_values(array_slice($session->transcribe_detail_data, -1))[0];
							// set defaults
							$session->set('line', $lastEl['BMD_line_sequence'] + 10);
							$session->set('familyname', $lastEl['BMD_surname']);
							$session->set('dup_familyname', $lastEl['BMD_surname']);
							$session->set('dup_firstname', $lastEl['BMD_firstname']);
							$session->set('dup_secondname', $lastEl['BMD_secondname']);
							$session->set('dup_thirdname', $lastEl['BMD_thirdname']);
							$session->set('dup_partnername', $lastEl['BMD_partnername']);
							$session->set('dup_age', $lastEl['BMD_age']);
							$session->set('dup_district', $lastEl['BMD_district']);
							$session->set('dup_registration', $lastEl['BMD_registration']);
							$session->set('dup_page', $lastEl['BMD_page']);
							$session->set('dup_dis_number', $lastEl['BMD_volume']);
							$session->set('dup_reg_number', $lastEl['BMD_reg']);
							$session->set('dup_ent_number', $lastEl['BMD_entry']);
							$session->set('dup_source_code', $lastEl['BMD_source_code']);
							$session->set('last_detail_index', $lastEl['BMD_index']);
						}
					else
						{
							$session->set('line', 10);
							$session->set('familyname', '');
						}
					// blank input fields
					$session->set('firstname', '');
					$session->set('secondname', '');
					$session->set('thirdname', '');
					$session->set('partnername', '');
					$session->set('age', '');
					$session->set('district', '');
					$session->set('reverselookup', '');
					$session->set('registration', '');
					$session->set('page', '');
					$session->set('synonym', '');
					$session->set('dis_number', '');
					$session->set('reg_number', '');
					$session->set('ent_number', '');
					$session->set('source_code', '');
					// get enter table definitions for this header
					$table_details_model = new Table_Details_Model();
					// table details head line 1
					$session->set('table_details_head_line_1', $table_details_model	
						->where('BMD_controller', $session->controller)
						->where('BMD_table_attr', 'head')
						->where('BMD_format', $session->format)
						->where('BMD_table_line', 1)
						->orderby('BMD_order','ASC')
						->find());
					// table details body line 1
					$session->set('table_details_body_line_1', $table_details_model
						->join('header_table_details', 'table_details.BMD_index = header_table_details.BMD_table_details_index')
						->where('header_table_details.BMD_header_index = '.$session->transcribe_header[0]['BMD_header_index'])
						->where('BMD_controller', $session->controller)
						->where('BMD_table_attr', 'body')
						->where('BMD_format', $session->format)
						->where('BMD_table_line', 1)
						->orderby('BMD_order','ASC')
						->find());
					// table details head line 2
					$session->set('table_details_head_line_2', $table_details_model
						->join('header_table_details', 'table_details.BMD_index = header_table_details.BMD_table_details_index')
						->where('header_table_details.BMD_header_index = '.$session->transcribe_header[0]['BMD_header_index'])
						->where('BMD_controller', $session->controller)
						->where('BMD_table_attr', 'head')
						->where('BMD_format', $session->format)
						->where('BMD_table_line', 2)
						->orderby('BMD_order','ASC')
						->find());
					// table details body line 2
					$session->set('table_details_body_line_2', $table_details_model
						->join('header_table_details', 'table_details.BMD_index = header_table_details.BMD_table_details_index')
						->where('header_table_details.BMD_header_index = '.$session->transcribe_header[0]['BMD_header_index'])
						->where('BMD_controller', $session->controller)
						->where('BMD_table_attr', 'body')
						->where('BMD_format', $session->format)
						->where('BMD_table_line', 2)
						->orderby('BMD_order','ASC')
						->find());
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', $controller_title.' => '.$session->transcribe_header[0]['BMD_file_name'].' => '.$session->transcribe_header[0]['BMD_scan_name'].' => Approximately '.$session->transcribe_header[0]['BMD_records'].' records transcribed from this scan. Enter your transcription data from scan image. *=required field **=contextual help available');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
					break;
			}
	}
	
	function transcribe_show_step1($controller)
	{	
		// initialise
		$session = session();
		
		// show header																
		echo view('templates/header');
		// show views depending on view type
		switch ($session->show_view_type) 
			{
				// normal transcription
				case 'transcribe':
					
					echo view('linBMD2/transcribe_details_enter');
					// echo view('linBMD2/transcribe_image');
					echo view('linBMD2/transcribe_buttons');
					echo view('linBMD2/transcribe_script');
					break;
				// confirm page if not standard
				case 'confirm_page':
					echo view('linBMD2/transcribe_page_confirmation');
					break;
				// confirm district if not standard
				case 'confirm_district':
					echo view('linBMD2/transcribe_district_confirmation');
					break;
				// confirm volume if not standard
				case 'confirm_volume':
					echo view('linBMD2/transcribe_volume_confirmation');
				// confirm registraion if year not standard
				case 'confirm_registration':
					echo view('linBMD2/transcribe_registration_confirmation');
					break;
				// confirm forenames if blank
				case 'confirm_firstname':
					echo view('linBMD2/transcribe_firstname_confirmation');
					break;
			}
		
		// show details	
		echo view('linBMD2/transcribe_details_show');
		// show footer
		echo view('templates/footer');
	}
	
	function transcribe_get_transcribe_inputs($controller)
	{
		// initialise method
		$session = session();
		
		// unset all input fields
		$input_fields = [ "familyname", "firstname", "secondname", "thirdname", "partnername", "district", "dis_number",
							"reg_number", "ent_number", "page", "partnername", "age", "line", "reverselookup", "registration", "source_code" ];
		foreach ( $input_fields as $input_field )
			{
				unset($session->input_field);
			}
		
		// get common entries for all types
		$session->set('familyname', $_POST['familyname']);
		// $session->set('line', $_POST['line']);
		$session->set('firstname', $_POST['firstname']);
		$session->set('district', $_POST['district']);
		// $session->set('reverselookup', $_POST['reverselookup']);
		$session->set('registration', $_POST['registration']);
		
		// get per type entries
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':
					$session->set('partnername', $_POST['partnername']);
					if ( $session->transcribe_allocation[0]['BMD_year'] > 1992 )
						{
							$session->set('reg_number', $_POST['reg_number']);
							$session->set('ent_number', $_POST['ent_number']);
							$session->set('dis_number', $_POST['dis_number']);
						}
					else
						{
							$session->set('page', $_POST['page']);
							$session->set('dis_number', $_POST['dis_number']);
						}
					break;
				case 'M':
					$session->set('partnername', $_POST['partnername']);
					if ( $session->transcribe_allocation[0]['BMD_year'] > 1993 )
						{
							$session->set('dis_number', $_POST['dis_number']);
							$session->set('page', $_POST['page']);
							$session->set('ent_number', $_POST['ent_number']);
							$session->set('source_code', $_POST['source_code']);
						}
					else
						{
							$session->set('page', $_POST['page']);
							$session->set('dis_number', $_POST['dis_number']);
						}
					break;
				case 'D':
					$session->set('age', $_POST['age']);
					if ( $session->transcribe_allocation[0]['BMD_year'] > 1992 )
						{
							$session->set('reg_number', $_POST['reg_number']);
							$session->set('ent_number', $_POST['ent_number']);
							$session->set('dis_number', $_POST['dis_number']);
						}
					else
						{
							$session->set('page', $_POST['page']);
							$session->set('dis_number', $_POST['dis_number']);
						}
					break;
			}
		// get panzoom data elements
		$session->set('panzoom_x', $_POST['panzoom_x']);
		$session->set('panzoom_y', $_POST['panzoom_y']);
		$session->set('panzoom_z', $_POST['panzoom_z']);
		$session->set('sharpen', $_POST['sharpen']);
	}
				
	function transcribe_get_confirm_district_inputs($controller)
	{
		// initialise method
		$session = session();	
		// get inputs
		$session->set('synonym_ok', $_POST['confirm_synonym']);
		$session->set('synonym', $_POST['synonym']);
		$session->set('district_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_page_inputs($controller)
	{			
		// initialise method
		$session = session();	
		// get inputs
		$session->set('page_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_volume_inputs($controller)
	{
		// initialise method
		$session = session();	
		// get inputs
		$session->set('volume', $_POST['volume']);
		$session->set('volume_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_registration_inputs($controller)
	{			
		// initialise method
		$session = session();	
		// get inputs
		$session->set('registration_ok', $_POST['confirm']);
	}
	
	function transcribe_get_confirm_firstname_inputs($controller)
	{			
		// initialise method
		$session = session();	
		// get inputs
		$session->set('firstname_ok', $_POST['confirm']);
	}
		
	
	function transcribe_validate_transcribe_inputs($controller)
	{
		// initialise method
		$session = session();	
		$detail_data_model = new Detail_Data_Model();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		$session->set('show_view_type', 'transcribe');
		unset($session->dis_volume);
		
		// do tests
		
		// standard tests for all types and years
		transcribe_validate_standard_tests();
		if ( $session->message_error != '' ) { return; }
		
		// tests by type and year
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':	// births
					test_births_standard();
					if ( $session->message_error == 'error' ) { return; }
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1992:
								test_births_prior();
								if ( $session->message_error != '' ) { return; }
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								test_births_after();
								if ( $session->message_error != '' ) { return; }
								break;
							default:
								break;
						}
					break;
				case 'M':	// Mariages
					test_marriages_standard();
					if ( $session->message_error == 'error' ) { return; }
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1993:
								test_marriages_prior();
								if ( $session->message_error != '' ) { return; }
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1993:
								test_marriages_after();
								if ( $session->message_error != '' ) { return; }
								break;
							default:
								break;
						}
					break;
				case 'D':	// Deaths
					test_deaths_standard();
					if ( $session->message_error == 'error' ) { return; }
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1992:
								test_deaths_prior();
								if ( $session->message_error == 'error' ) { return; }
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								test_deaths_after();
								if ( $session->message_error == 'error' ) { return; }
								break;
							default:
								break;
						}
					break;
				default:
					break;
			}

		// do registration tests
		transcribe_validate_registration_select_tests();
		if ( $session->message_error == 'error' ) { return; }	
		
		// do volume tests
		transcribe_validate_volume_tests();
		if ( $session->message_error == 'error' ) { return; }
	}
	
	function transcribe_validate_confirm_district_inputs($controller)
	{
		// initialise method
		$session = session();	
		$session->set('message_error', '');
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		// has user confirmed both synonym and district?
		if ( $session->synonym_ok == 'Y' AND $session->district_ok == 'Y' )
		{
			$session->set('show_view_type', 'confirm_district');
			$session->set('message_2', 'You cannot confirm both synonym and district.');
			$session->set('message_class_2', 'alert alert-danger');
			$session->set('message_error', 'error');
			return;
		}
		// did user confirm synonym
		if ( $session->synonym_ok == 'Y' )
			{
				// is synonym a valid district?
				$session->set('transcribe_synonym', $districts_model->where('District_name', $session->synonym)->findAll());
				$synonym_volumes = $volumes_model
									->where('district_index', $session->transcribe_synonym[0]['district_index'])
									->where('BMD_type', $session->transcribe_allocation[0]['BMD_type'])
									->findAll();
				if ( ! $session->transcribe_synonym OR ! $synonym_volumes )
					{
						$session->set('show_view_type', 'confirm_district');
						$session->set('message_2', 'You must enter a valid district for the synonym OR no volume data was found for the synonym.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
				// a valid synonym was confirmed by user
				// add district to table
				$data =	[
									'District_name' => strtoupper($session->district),
									'Added_by_user' => $session->user[0]['BMD_user'],
								];
				$id = $districts_model->insert($data);
				// now get all volume records regardless of BMD_type
				$synonym_volumes = $volumes_model
									->where('district_index', $session->transcribe_synonym[0]['district_index'])
									->findAll(); 
				// read all volume info for synonym and create volume records for the new district
				foreach ( $synonym_volumes as $synonym )
					{
						$data =	[
											'district_index' => $id,
											'volume_from' => $synonym['volume_from'],
											'volume_to' => $synonym['volume_to'],
											'volume' => $synonym['volume'],
											'BMD_type' => $synonym['BMD_type'],
										];
						$volumes_model->insert($data);
					}
			}
		else
			{
				// if synonym not confirmed, did user confirm district?							
				if ( $session->district_ok == 'N' )
					{
						$session->set('show_view_type', 'transcribe');
						$session->set('message_2', 'You did not confirm this district => '.$session->district.'. Please correct it.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
				else
					{
						// user confirmed district so add it to districts file
						$data =	[
											'District_name' => strtoupper($session->district),
											'Added_by_user' => $session->user[0]['BMD_user'],
										];
						$districts_model->insert($data);
					}
			}
	}
				
	function transcribe_validate_confirm_page_inputs($controller)
	{			
		// initialise method
		$session = session();
		$session->set('message_error', '');	
		// test confirm
		if ( $session->page_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm this page number => '.$session->page.'. Please correct it.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_validate_confirm_volume_inputs($controller)
	{
		// initialise method
		$session = session();
		$session->set('message_error', '');
		$volumes_model = new Volumes_Model();
		// did user confirm?
		if ( $session->volume_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm this the volume => '.$session->volume.'. Please correct it or confirm the district.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}	
	}
	
	function transcribe_validate_confirm_registration_inputs($controller)
	{			
		// initialise method
		$session = session();
		$session->set('message_error', '');	
		// test confirm
		if ( $session->registration_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm this registration number => '.$session->registration.'. Please correct it.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_validate_confirm_firstname_inputs($controller)
	{			
		// initialise method
		$session = session();
		$session->set('message_error', '');	
		// test confirm
		if ( $session->firstname_ok == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm blank forenames. Please correct them.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_update($controller)
	{
		// initialise method
		$session = session();	
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		
		// convert to capitals
		$capital_fields = [ "familyname", "firstname", "secondname", "thirdname", "partnername", "district", "dis_number",
							"reg_number", "ent_number", "page", "partnername", "age", "registration", "source_code" ];
		foreach ( $capital_fields as $capital_field )
			{
				if ( isset($session->$capital_field) )
					{
						$session->set($capital_field, strtoupper($session->$capital_field));
					}
				else
					{
						$session->set($capital_field, ' ');
					}
			}
			
		// set update fields by type and year
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':	// births
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1992:
								$session->set('volume_update', $session->volume);
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								$session->set('volume_update', $session->dis_number);
								break;
							default:
								break;
						}
				case 'M':	// Mariages
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1993:
								$session->set('volume_update', $session->volume);
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1993:
								$session->set('volume_update', $session->dis_number);
								break;
							default:
								break;
						}
				case 'D':	// Deaths
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1992:
								$session->set('volume_update', $session->volume);
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								$session->set('volume_update', $session->dis_number);
								break;
							default:
								break;
						}
				default:
					break;
			}

		// set fields for update
		$data =	[
					'BMD_identity_index' => $session->BMD_identity_index,
					'BMD_header_index' => $session->transcribe_header[0]['BMD_header_index'],
					'BMD_line_sequence' => $session->line,
					'BMD_surname' => $session->familyname,
					'BMD_firstname' => $session->firstname,
					'BMD_secondname' => $session->secondname,
					'BMD_thirdname' => $session->thirdname,
					'BMD_district' => $session->district,
					'BMD_volume' => $session->volume_update,
					'BMD_page' => $session->page,
					'BMD_status' => '0',
					'BMD_line_panzoom_x' => $session->panzoom_x,
					'BMD_line_panzoom_y' => $session->panzoom_y,
					'BMD_line_panzoom_z' => $session->panzoom_z,
					'BMD_line_sharpen' => $session->sharpen,
					'BMD_line_image_rotate' => $session->image_r,
					'BMD_partnername' => $session->partnername,
					'BMD_registration' => $session->registration,
					'BMD_reg' => $session->reg_number,
					'BMD_entry' => $session->ent_number,
					'BMD_age' => $session->age,
					'BMD_source_code' => $session->source_code,
				];
				
		// add if line edit = 0 / update if line edit = 1
		if ( $session->line_edit_flag == 0 )
			{
				// insert record
				$session->set('last_detail_index', $detail_data_model->insert($data));
				// update record count on header and image parameters
				$data =	[
									'BMD_records' => $session->transcribe_header[0]['BMD_records'] + 1,
									'BMD_panzoom_x' => $session->panzoom_x,
									'BMD_panzoom_y' => $session->panzoom_y,
									'BMD_panzoom_z' => $session->panzoom_z,
									'BMD_sharpen' => $session->sharpen,
									'BMD_image_scroll_step' => $session->scroll_step,
									'BMD_image_rotate' => $session->image_r,
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
				// scroll image
				scroll_step();
			}
		else
			{
				$detail_data_model->update($session->line_edit_data[0]['BMD_index'], $data);
				// restore image parameters
				$session->set('panzoom_x', $session->save_panzoom_x);
				$session->set('panzoom_y', $session->save_panzoom_y);
				$session->set('panzoom_z', $session->save_panzoom_z);
				$session->set('sharpen', $session->save_sharpen);
				$session->set('image_r', $session->save_image_r);
			}
			
		// add names to tables; update_surnames and update_firstnames are functions in the update_names_helper
		// familyname / partnername
		update_surnames($session->familyname);
		update_surnames($session->partnername);
		// first, second, third names
		// explode firstname on blank as all names are now stored in firstname.
		$forenames = explode(' ', $session->firstname);
		foreach ($forenames as $forename)
			{
				update_firstnames($forename);
			}
		
		// load the header again
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])->find();
		
		// do backup on a regular basis if no remainder when number of records divided by 5  is 0, ie backup on every fifth record
		$session->set('database_backup_performed', 0);
		if ( $session->transcribe_header[0]['BMD_records'] % 5 == 0 )
			{
				database_backup();
			}
			
		// reset position cursor
		$session->set('position_cursor', 'firstname');
	}
	
	function scroll_step()
	{
		$session = session();
		$session->set('panzoom_y', $session->panzoom_y - $session->scroll_step);
	}
	
	function transcribe_validate_standard_tests()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$districts_model = new Districts_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');	
		
		// familyname blank?
		if ( $session->familyname == '' )
			{
				$session->set('position_cursor', 'familyname');
				$session->set('message_2', 'Family name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// line number blank?
		if ( $session->line == '' )
			{
				$session->set('message_2', 'Line number cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// line number not numeric
		if ( ! is_numeric($session->line) )
			{
				$session->set('message_2', 'Line number must be numeric.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// exists?
		$line_detail = $detail_data_model	->where('BMD_line_sequence',  $session->line)
											->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
											->where('BMD_identity_index', $session->BMD_identity_index)
											->findAll();
		if ( $line_detail AND $session->line_edit_flag == 0 )
			{
				$session->set('message_2', 'Line number '.$session->line.' is already transcribed for this scan. If you want to change this line, select it in the table below. If you are adding a line, enter a line number that does not already exist.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// firstname blank?
		if ( $session->firstname_ok == 'N' )
			{
				if ( $session->firstname == '' )
					{
						$session->set('position_cursor', 'firstname');
						$session->set('show_view_type', 'confirm_firstname');
						$session->set('message_2', 'Forenames are not normally blank. Please confirm.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
			}
			
		// district blank and valid?
		if ( $session->district != '' AND $session->reverselookup != '' )
			{
				$session->set('message_2', 'You cannot enter both District name and District lookup. ');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// if reverse lookup set district
		if ( $session->district == '' AND $session->reverselookup != '' )
			{
				$session->set('district', $session->reverselookup);
			}
		// district blank
		if ( $session->district == '' )
			{
				$session->set('position_cursor', 'district');
				$session->set('message_2', 'District cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// district exists
		$session->set('transcribe_district', $districts_model->where('District_name', $session->district)->findAll());
		if ( ! $session->transcribe_district )
			{
				$session->synonym = '';
				$session->set('show_view_type', 'confirm_district');
				$session->set('message_2', 'This district is unknown => '.$session->district.'. Please confirm your entry or correct it by selecting No.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_validate_registration_select_tests()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// registration test for D are performed only if registration entered. is this true?
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':	
					// registration test for B
					transcribe_validate_registration_tests();
					if ( $session->message_error == 'error' ) { return; }
					// format the registration field
					if ( $session->transcribe_allocation[0]['BMD_year'] >= 1993 )
						{
							$session->set('registration', $session->reg_month.$session->reg_year);
						}
					else
						{
							$session->set('registration', $session->reg_month.'.'.$session->reg_year);
						}
					break;
				case 'M':
					// registration test for M
					if ( $session->transcribe_allocation[0]['BMD_year'] <= 1993 )
						{
							transcribe_validate_registration_tests();
							if ( $session->message_error == 'error' ) { return; }
							// format the registration field but only for 1993 and prior
							if ( $session->transcribe_allocation[0]['BMD_year'] < 1993 )
								{
									$session->set('registration', $session->reg_month.'.'.$session->reg_year);
								}
							else
								{
									$session->set('registration', $session->reg_month.$session->reg_year);
								}
						}
					break;
				case 'D':	
					// registration test for D
					transcribe_validate_registration_tests();
					if ( $session->message_error == 'error' ) { return; }
					// format the registration field
					if ( $session->transcribe_allocation[0]['BMD_year'] >= 1993 )
						{
							$session->set('registration', $session->reg_month.$session->reg_year);
						}
					else
						{
							$session->set('registration', $session->reg_month.'.'.$session->reg_year);
						}
					break;
				default:
					break;
			}
	}
	
	function transcribe_validate_registration_tests()
	{
		// initialise
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// what format has been entered? length can be 2, 4 or 5; 2=month only; 4=mmyy, 5=mm.yy
		switch (strlen($session->registration))
			{
				case 2: // just the month has been entered
					// check the month
					$session->set('reg_month', $session->registration);
					transcribe_validate_reg_month();
					if ( $session->message_error != '' ) { return; }
					// check the year
					$session->set('reg_year', substr($session->transcribe_allocation[0]['BMD_year'], 2, 2)); 
					transcribe_validate_reg_year();
					if ( $session->message_error != '' ) { return; }
					break;
				case 4: // month and year has been entered
					// check the month
					$session->set('reg_month', substr($session->registration, 0, 2));
					transcribe_validate_reg_month();
					if ( $session->message_error != '' ) { return; }
					// check the year
					$session->set('reg_year', substr($session->registration, 2, 2) ); 
					transcribe_validate_reg_year();
					if ( $session->message_error != '' ) { return; }
					break;
				case 5: // month and year has been entered with .
					// check the month
					$session->set('reg_month', substr($session->registration, 0, 2));
					transcribe_validate_reg_month();
					if ( $session->message_error != '' ) { return; }
					// check the year
					$session->set('reg_year', substr($session->registration, 3, 2) ); 
					transcribe_validate_reg_year();
					if ( $session->message_error != '' ) { return; }
					// check the separator
					$session->set('reg_separator', substr($session->registration, 2, 1) ); 
					transcribe_validate_reg_separator();
					if ( $session->message_error != '' ) { return; }
					break;
				default: // anything else is an error
					$session->set('message_2', 'Registration format not valid. Registration can be mm, or mmyy or mm.yy. If mm, webBMD will add the year for you.');
					$session->set('message_class_2', 'alert alert-danger');
					$session->set('message_error', 'error');
					return;
					break;
			}
			
	}
	
	function transcribe_validate_reg_month()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// numeric
		if ( ! is_numeric($session->reg_month) )
			{
				$session->set('message_2', 'Registration month number must be numeric.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// in range
		if ( $session->reg_month < '01' OR $session->reg_month > '12' )
			{
				$session->set('message_2', 'Registration month number must be in range 01:12.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function transcribe_validate_reg_year()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// year valid?
		if ( $session->registration_ok == 'N' )
			{
				if ($session->reg_year != substr($session->transcribe_allocation[0]['BMD_year'], 2, 2) )
					{
						$session->set('show_view_type', 'confirm_registration');
						$session->set('message_2', 'Registration year, '.$session->registration.', is normally equal to scan year (allocation year) = '.substr($session->transcribe_allocation[0]['BMD_year'], 2, 2));
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
			}
	}
	
	function transcribe_validate_reg_separator()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// separator valid?
		if ($session->reg_separator != '.' )
			{
				$session->set('message_2', 'Registration separator must be . = full-stop.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}

	function transcribe_validate_volume_tests()
	{
		// initialise
		$session = session();
		$volumes_model = new Volumes_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// if volume was confirmed don't test it
		if ( $session->volume_ok == 'Y' )
			{
				return;
			}

		// get volume info
		$session->set('transcribe_volumes', $volumes_model
			->where('district_index', $session->transcribe_district[0]['district_index'])
			->where('BMD_type', $session->transcribe_allocation[0]['BMD_type'])
			->findAll());
		if ( ! $session->transcribe_volumes )
			{
				$session->set('show_view_type', 'confirm_volume');
				$session->set('volume', '');
				$session->set('message_2', 'No volume data found for this district => '.$session->district.' Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// set volume found flag 
		$volume_found = 0;
		// set values in order to find this registration in range
		$year = $session->transcribe_allocation[0]['BMD_year'];
		// per type quarter
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':
					$quarter = $session->month_to_quarter[$session->reg_month];
					break;
				case 'M':
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] <= 1993:
								$quarter = $session->month_to_quarter[$session->reg_month];
								break;
							case $session->transcribe_allocation[0]['BMD_year'] > 1993:
								$marr_month = $session->marriage_months[$session->registration];
								$quarter = $session->month_to_quarter[$marr_month];
								break;
							default:
								break;
						}
					break;
				case 'D':
					if ( empty ( $session->registration ) )
						{	
							$session->set('registration_was_blank', '1');
							$session->set('registration', '01.'.$session->transcribe_allocation[0]['BMD_year']);
							$quarter = '01';
						}
					else
						{
							$quarter = $session->month_to_quarter[$session->reg_month];
							$session->set('registration_was_blank', '0');
						}
					//$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;	
				default:
					break;
			}
		// find range
		foreach ( $session->transcribe_volumes as $volume_range )
			{
				if ( $year.$quarter >= $volume_range['volume_from'] AND $year.$quarter <= $volume_range['volume_to'])
					{
						$session->set('volume', $volume_range['volume']);
						$volume_found = 1;
						break;
					}	
			}
		// was a volume found?
		if ( $volume_found == 0 OR $session->volume == '' )
			{
				$session->set('show_view_type', 'confirm_volume');
				$session->set('volume', '');
				$session->set('message_2', 'No volume data found for this district => '.$session->district.', '.$session->transcribe_allocation[0]['BMD_year'].', '.$quarter.'. Please enter volume from scan and confirm.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// does it match entered volume?
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':	// births
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
							default:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
						}
					break;
				case 'M':	// Marriages
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] > 1993:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
							default:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
						}
					break;
				case 'D':	// Deaths
					switch (true)
						{
							case $session->transcribe_allocation[0]['BMD_year'] > 1992:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
							default:
								if ( $session->volume != $session->dis_volume )
									{
										$session->set('show_view_type', 'confirm_volume');
										$session->set('message_2', 'Scan volume/district number you entered is not equal to official volume list for this district => '.$session->district.'. You entered => '.$session->dis_volume.'. Official volume => '.$session->volume.'. Please confirm.');
										$session->set('message_class_2', 'alert alert-danger');
										$session->set('message_error', 'error');
										$session->set('volume', '');
										return;
									}
								break;
						}
					break;
				default:
					break;
			}
	}
	
	function test_deaths_standard()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// age can be the age or the DOB
		// test age/DOB exists.
		if ( trim($session->age) == '' )
			{
				$session->set('message_2', 'Age/DOB cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		
	}
	
	function test_deaths_prior()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// page blank?
		if ( $session->page == '' )
			{
				$session->set('message_2', 'Page cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
			
		
		// DOB valid?
		// split DOB into d:m:y
		$DOB_array = explode(' ', $session->age);
		// if arrray count is 3, I have a DOB (probably), so validate
		if ( count($DOB_array) == 3 )
			{
				// length OK?
				if ( strlen($session->age) != 10 )
					{
						$session->set('message_2', 'Age/DOB format incorrect. Too many characters.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('message_error', 'error');
						return;
					}
				
				// split DOB into components
				$session->set('DOB_day', strtoupper($DOB_array[0]));
				$session->set('DOB_month', strtoupper($DOB_array[1]));
				$session->set('DOB_year', strtoupper($DOB_array[2]));
				
				// test DOB
				test_DOB();
				if ( $session->message_error == 'error' ) { return; }			
			}
		// set dis_volume
		$session->set('dis_volume', $session->dis_number);
	}
	
	function test_deaths_after()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// DOB valid?
		// length OK?
		if ( strlen($session->age) != 8 )
			{
				$session->set('message_2', 'Age/DOB format incorrect.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		
		// split DOB into components
		$session->set('DOB_day', strtoupper(substr($session->age, 0, 2)));
		$session->set('DOB_month', strtoupper(substr($session->age, 2, 2)));
		$session->set('DOB_year', strtoupper(substr($session->age, 4, 4))); 
		
		// test DOB
		test_DOB();
		if ( $session->message_error == 'error' ) { return; }
		
		// test dis number exists.
		if ( $session->dis_number == '' )
			{
				$session->set('message_2', 'District No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test reg number exists.
		if ( $session->reg_number == '' )
			{
				$session->set('message_2', 'Reg No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test entry number exists.
		if ( $session->ent_number == '' )
			{
				$session->set('message_2', 'Entry No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test registration number exists.
		if ( $session->registration == '' )
			{
				$session->set('message_2', 'DOR cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		
		// create district volume field
		$session->set('dis_volume', substr($session->dis_number, 0, 3));
	}
	
	function test_marriages_standard()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// test partnername
		if ( $session->partnername == '' )
			{
				$session->set('position_cursor', 'partnername');
				$session->set('message_2', 'Partner name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function test_marriages_prior()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// not a lot to do here
		// set dis_volume
		$session->set('dis_volume', $session->dis_number);
	}
	
	function test_marriages_after()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
	
		// test dis number exists.
		if ( $session->dis_number == '' )
			{
				$session->set('position_cursor', 'dis_number');
				$session->set('message_2', 'District No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
			
		// registration blank?
		if ( $session->registration == '' )
			{
				$session->set('position_cursor', 'registration');
				$session->set('message_2', 'Month cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
			
		// test registration is in marriage months array
		$session->set('registration', strtoupper($session->registration));
		if ( ! array_key_exists($session->registration, $session->marriage_months) )
			{
				$session->set('position_cursor', 'registration');
				$session->set('message_2', 'Month is invalid');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
				
		if ( $session->page_ok == 'N' )
			{
				switch ($session->transcribe_allocation[0]['BMD_type'])
					{
						case 'D':
							break;
						default:
							if ( strlen($session->page) < 3 OR strlen($session->page) > 4 )
								{
									$session->set('show_view_type', 'confirm_page');
									$session->set('message_2', 'Page number is usually 3 or 4 digits long. You entered => '.$session->page.'. Please confirm your entry or correct it by selecting No.');
									$session->set('message_class_2', 'alert alert-danger');
									$session->set('message_error', 'error');
									return;
								}
							break;
					}
			}

		// test entry number
		if ( $session->ent_number == '' )
			{
				$session->set('position_cursor', 'ent_number');
				$session->set('message_2', 'Entry no cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
			
		// test source code
		if ( $session->source_code == '' )
			{
				$session->set('position_cursor', 'source_code');
				$session->set('message_2', 'Source code cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
			
		// create district volume field
		$session->set('dis_volume', $session->dis_number);		
	}
	
	function test_births_standard()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// test partnername
		if ( $session->partnername == '' )
			{
				$session->set('message_2', 'Partner name cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
	
	function test_births_prior()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// volume blank?
		if ( $session->dis_number == '' )
			{
				$session->set('message_2', 'Volume must be entered');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// page blank?
		if ( $session->page == '' )
			{
				$session->set('message_2', 'Page cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// set dis_volume
		$session->set('dis_volume', $session->dis_number);
	}
	
	function test_births_after()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// test dis number exists.
		if ( $session->dis_number == '' )
			{
				$session->set('message_2', 'District No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test reg number exists.
		if ( $session->reg_number == '' )
			{
				$session->set('message_2', 'Reg No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test entry number exists.
		if ( $session->ent_number == '' )
			{
				$session->set('message_2', 'Entry No cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// test registration number exists.
		if ( $session->registration == '' )
			{
				$session->set('message_2', 'DOR cannot be blank.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		
		// create district volume field
		$session->set('dis_volume', substr($session->dis_number, 0, 3));
	}
	
	function test_DOB()
	{
		// initialise
		$session = session();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// test DOB_day is in DOB days array
		if ( ! in_array($session->DOB_day, $session->death_days) )
			{
				$session->set('message_2', 'DOB day is invalid');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// DOB month in DOB months array?
		if ( ! in_array($session->DOB_month, $session->death_months) )
			{
				$session->set('message_2', 'DOB month is invalid');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// DOB year valid?
		if ( ! is_numeric($session->DOB_year) )
			{
				$session->set('message_2', 'DOB year must be numeric');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// DOB year valid length?
		if ( strlen($session->DOB_year) != 4 )
			{
				$session->set('message_2', 'DOB year must be 4 digits long');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
		// DOB year > scan year?
		if ( $session->DOB_year > $session->transcribe_allocation[0]['BMD_year'] )
			{
				$session->set('message_2', 'DOB year cannot be greater than scan year.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('message_error', 'error');
				return;
			}
	}
