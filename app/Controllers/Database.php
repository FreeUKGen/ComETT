<?php namespace App\Controllers;

use App\Models\Districts_Model;
use App\Models\Parameter_Model;
use App\Models\Volumes_Model;
use App\Models\Volume_Ranges_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;
use App\Models\Detail_Data_Model;
use App\Models\Header_Model;
use App\Models\Header_Table_Details_Model;
use App\Models\Allocation_Model;
use App\Models\Table_Details_Model;
use App\Models\Def_Fields_Model;
use App\Models\Def_Image_Model;
use App\Models\Def_Ranges_Model;
use App\Models\Identity_Model;
use App\Models\Submitters_Model;
use App\Models\Detail_Comments_Model;
use App\Models\Transcription_Model;
use App\Models\Transcription_Detail_Def_Model;
use App\Models\Identity_Last_Indexes_Model;
use App\Models\Roles_Model;
use App\Models\Syndicate_Model;

class Database extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('backup');
        helper('remote');
    }
	
	public function database_step1($start_message)
	{
		// initialise
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the Database action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose the Database action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/database_menu');
		echo view('templates/footer');
	}
	
	public function coord_step1($start_message)
	{
		// initialise
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the COORDINATOR action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose the COORDINATOR action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/coord_menu');
		echo view('templates/footer');
	}
			
	public function tester_step1($start_message)
	{
		// initialise
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the TESTER action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose the TESTER action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/tester_menu');
		echo view('templates/footer');
	}
	
	public function manage_districts_step1($start_message)
	{
		// initialise
		$session = session();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the TESTER action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose the TESTER action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/manage_districts');
		echo view('linBMD2/sortTableNew');
		echo view('templates/footer');
	}
	
	public function update_def_fields()
	{
		// initialise
		$session = session();
		$def_fields_model = new Def_Fields_Model();
		
		// read through standard defs
		foreach ( $session->current_transcription_def_fields as $key => $fields )
			{			
				// update standard defs table
				$def_fields_model	->where('project_index', $session->current_project[0]['project_index'])
									->where('data_entry_format', $session->current_transcription_def_fields[$key]['data_entry_format'])
									->where('scan_format', $session->current_transcription_def_fields[$key]['scan_format'])
									->where('field_name', $session->current_transcription_def_fields[$key]['field_name'])
									->set(['column_width' => $fields['column_width']])
									->set(['font_size' => $fields['font_size']])
									->set(['font_weight' => $fields['font_weight']])
									->set(['field_align' => $fields['field_align']])
									->set(['pad_left' => $fields['pad_left']])
									->set(['capitalise' => $fields['capitalise']])
									->set(['volume_roman' => $fields['volume_roman']])
									->update();
			}
		
		// reload standard defs
		$session->standard_def =	$def_fields_model	
									->where('project_index', $session->current_project[0]['project_index'])
									->where('syndicate_index', $session->current_transcription[0]['BMD_syndicate_index'])
									->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
									->where('scan_format', $session->current_allocation[0]['scan_format'])
									->orderby('field_order','ASC')
									->find();
									
		// go to data entry
		return redirect()->to( base_url(base_url($session->controller.'/transcribe_'.$session->controller.'_step1/0')) );	
	}
	
	public function update_image_values()
	{
		// initialise
		$session = session();
		$def_image_model = new Def_Image_Model();

		// does this image set exist
		$image =	$def_image_model
					->where('project_index', $session->current_project[0]['project_index'])
					->where('syndicate_index', $session->current_transcription[0]['BMD_syndicate_index'])
					->where('data_entry_format', $session->current_transcription_def_fields[0]['data_entry_format'])
					->where('scan_format', $session->current_transcription_def_fields[0]['scan_format'])
					->find();
	
		// was a record found?
		if ( $image )
			{
				// found so update
				$def_image_model	->where('project_index', $session->current_project[0]['project_index'])
									->where('data_entry_format', $session->current_transcription_def_fields[0]['data_entry_format'])
									->where('scan_format', $session->current_transcription_def_fields[0]['scan_format'])
									->set(['image_x' => $session->current_transcription[0]['BMD_image_x']])
									->set(['image_y' => $session->current_transcription[0]['BMD_image_y']])
									->set(['image_rotate' => 0])
									->set(['image_scroll_step' => $session->current_transcription[0]['BMD_image_scroll_step']])
									->set(['panzoom_x' => $session->current_transcription[0]['BMD_panzoom_x']])
									->set(['panzoom_y' => $session->current_transcription[0]['BMD_panzoom_y']])
									->set(['panzoom_z' => $session->current_transcription[0]['BMD_panzoom_z']])
									->set(['sharpen' => $session->current_transcription[0]['BMD_sharpen']])
									->update();
			}
		else
			{
				// not found so insert
				$def_image_model	->set(['project_index' => $session->current_project[0]['project_index']])
									->set(['data_entry_format' => $session->current_transcription_def_fields[0]['data_entry_format']])
									->set(['scan_format' => $session->current_transcription_def_fields[0]['scan_format']])
									->set(['image_x' => $session->current_transcription[0]['BMD_image_x']])
									->set(['image_y' => $session->current_transcription[0]['BMD_image_y']])
									->set(['image_rotate' => 0])
									->set(['image_scroll_step' => $session->current_transcription[0]['BMD_image_scroll_step']])
									->set(['panzoom_x' => $session->current_transcription[0]['BMD_panzoom_x']])
									->set(['panzoom_y' => $session->current_transcription[0]['BMD_panzoom_y']])
									->set(['panzoom_z' => $session->current_transcription[0]['BMD_panzoom_z']])
									->set(['sharpen' => $session->current_transcription[0]['BMD_sharpen']])
									->insert();
			}
		
		// go round again
		return redirect()->to( base_url($session->controller.'/transcribe_'.$session->controller.'_step1/0') );
	}
	
	public function firstnames()
	{
		// initialise
		$session = session();
		$firstname_model = new Firstname_Model();
		// get firstnames
		$session->set('names', $firstname_model->select('Firstname AS name')
																			->select('Firstname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'First names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('linBMD2/transcribe_script');
		echo view('templates/footer');
	}
	
	public function surnames()
	{
		// initialise
		$session = session();
		$surname_model = new Surname_Model();
		// get surnames
		$session->set('names', $surname_model->select('Surname AS name')
																			->select('Surname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'Family names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('linBMD2/transcribe_script');
		echo view('templates/footer');
	}
	
	public function database_backup()
	{
		// initialise
		$session = session();
		// do the backup
		database_backup();
		
		$session->set('message_2', 'The FreeComETT database has been backed up to your web user folder.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );
	}
	
	public function merge_names()
	{		
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		
		// get all details
		$detail_data = $detail_data_model	
			->findAll();
			
		// read data
		foreach ($detail_data as $detail_line) 
			{
				// merge second and third name to first name
				$detail_line['BMD_firstname'] = $detail_line['BMD_firstname'].' '.$detail_line['BMD_secondname'].' '.$detail_line['BMD_thirdname'];
				
				// update record
				$data =	[
							'BMD_firstname' => $detail_line['BMD_firstname'],
							'BMD_secondname' => '',
							'BMD_thirdname' => '',
						];
				$detail_data_model->update($detail_line['BMD_index'], $data);
			}
			
		// all done
		$session->set('message_2', 'Second and third names have been merged to first name.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );	
	}
	
	public function create_header_data_entry_dimensions()
	{		
		// initialise method
		$session = session();
		$table_details_model = new Table_Details_Model();
		$header_table_details_model = new Header_Table_Details_Model();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		
		// read headers
		$headers = $header_model ->findall();
		
		foreach ($headers as $header)
			{
				// do data entry table dimensions axist already for this header
				$dimensions = $header_table_details_model
					->where('BMD_header_index', $header['BMD_header_index'])
					->find();
				
				// found?
				if ( ! $dimensions )
					{
						// get allocation
						$allocation = $allocation_model
							->where('BMD_allocation_index', $header['BMD_allocation_index'])
							->find();
						
						// allocation found?
						if ( $allocation )
							{
								// create the data entry table details
								// set format 
								// format change year depends on scan type = controller
								// default format = post
								$format = 'post';
								switch ($allocation[0]['BMD_type'])
									{
										case 'B':
											$controller = 'births';
											if ( $allocation[0]['BMD_year'] < 1993 )
												{
													$format = 'prior';
												}
											break;
										case 'D':
										$controller = 'deaths';
											if ( $allocation[0]['BMD_year'] < 1993 )
												{
													$format = 'prior';
												}
											break;
										case 'M':
										$controller = 'marriages';
											if ( $allocation[0]['BMD_year'] < 1994 )
												{
													$format = 'prior';
												}
											break;
										default:
											break;
									}
					
									// get the records
									$table_details = $table_details_model	
											->where('BMD_controller', $controller)
											->where('BMD_table_attr', 'body')
											->where('BMD_format', $format)
											->orderby('BMD_order','ASC')
											->find();
									// loop through table element by element and write the header specific table details
									foreach ($table_details as $td) 
										{ 
											// write to header table details
											$data =	[
													'BMD_header_index' => $header['BMD_header_index'],
													'BMD_table_details_index' => $td['BMD_index'],
													'BMD_header_span' => $td['BMD_span'],
													'BMD_header_align' => $td['BMD_align'],
													'BMD_header_pad_left' => $td['BMD_pad_left'],
													];
											$header_table_details_model->insert($data);
										}
							}
					}
			}
			
		// all done
		$session->set('message_2', 'Header data entry table dimensions have been created for all existing headers.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );	
	}
	
	public function delete_user_data_step1($start_message)
	{		
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		$roles_model = new Roles_Model();
		$db = \Config\Database::connect($session->project_DB);
		
		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Delete ALL FreeComETT data for a transcriber (Allocations, Transcriptions, Scans, Uploaded CSVs etc). This does NOT delete the transcriber identity in FreeComETT and it does NOT delete the transcriber from the project in FreeGenealogy.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
			}
			
		// get all identities this project
		$delete_ids =	$identity_model
						->where('project_index', $session->current_project[0]['project_index'])
						->findAll();

		// any found?
		if ( ! $delete_ids )
			{
				$session->set('message_2', 'No Identities found in this project => '.$session->current_project[0]['project_name']);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('database/delete_user_data_step1/1') );
			}
			
		// get submitters data for all identities
		$c = count($delete_ids);
		for( $i = 0; $i < $c; $i++ ) 
			{
				// get submitters record to construct real name
				$sql = "SELECT * FROM Submitters WHERE UserID = '".$delete_ids[$i]['BMD_user']."'";
				$query = $db->query($sql);
				$delete_sub = $query->getResultArray();
							
				// found ?
				if ( ! $delete_sub )
					{
						// is not found
						$delete_ids[$i]['realname'] = 'Not Found';
						$delete_ids[$i]['emailid'] = 'None';
					}
				else
					{
						// is found
						$delete_ids[$i]['realname'] = $delete_sub[0]['GivenName'].' '.$delete_sub[0]['Surname'];
						$delete_ids[$i]['emailid'] = $delete_sub[0]['EmailID'];
					}
				
				// get role name
				$role	=	$roles_model
							->where('role_index', $delete_ids[$i]['role_index'])
							->find();
				if ( ! $role )
					{
						$delete_ids[$i]['role_name'] = 'TRANSCRIBER';
					}
				else
					{
						$delete_ids[$i]['role_name'] = $role[0]['role_name'];
					}			
			}
			
		// set session array
		$session->delete_ids = $delete_ids;
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/identity_delete_step1');
		echo view('templates/footer');
	}
	
	public function delete_user_data_step2($delete_ids_key)
	{		
		// initialise method
		$session = session();
		$session->delete_ids_key = $delete_ids_key;
							
		// show view
		echo view('templates/header');
		echo view('linBMD2/identity_delete_step2');
		echo view('templates/footer');
	}
	
	public function delete_user_data_step3()
	{		
		// initialise method
		$session = session();
		$model = new Identity_Model();

		// is the password valid for current user
		if ( $session->identity_password != $_POST['password'] )
			{
				$session->set('message_2', 'You requested to delete data for, '.$session->delete_ids[$session->delete_ids_key]['BMD_user'].', '.$session->delete_ids[$session->delete_ids_key]['realname'].' in project, '.$session->current_project[0]['project_name'].', but the confirmation password you entered is not correct for your administrator identity.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('database/delete_user_data_step1/1') );
			}
			
		// ok, delete confirmed	
		// delete data in directory tree
		$folders = array('Scans', 'CSV_Files', 'Backups');
		foreach ( $folders as $folder )
			{
				$path = getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->delete_ids[$session->delete_ids_key]['BMD_user'].'/'.$folder.'/';
				$files = glob($path.'*');
				foreach( $files as $file ) 
					{
						if( is_file($file) )
							{
								unlink($file);
							}
					}
			}
			
		// delete table entries for this project/user in all tables
		$models = array('Transcription_Detail_Def_Model', 'Transcription_Model', 'Detail_Data_Model', 'Detail_Comments_Model', 'Allocation_Model');
		foreach ( $models as $model_name )
			{
				switch ($model_name) 
					{
						case 'Transcription_Detail_Def_Model':
							$model = new Transcription_Detail_Def_Model();
							$identity_field = 'identity_index';
							break;
						case 'Transcription_Model':
							$model = new Transcription_Model();
							$identity_field = 'BMD_identity_index';
							break;
						case 'Detail_Data_Model':
							$model = new Detail_Data_Model();
							$identity_field = 'BMD_identity_index';
							break;
						case 'Detail_Comments_Model':
							$model = new Detail_Comments_Model();
							$identity_field = 'BMD_identity_index';
							break;
						case 'Allocation_Model':
							$model = new Allocation_Model();
							$identity_field = 'BMD_identity_index';
							break;
					}

				$project_field = 'project_index';

				$table = 	$model
							->where($project_field, $session->current_project[0]['project_index'])
							->where($identity_field, $session->delete_ids[$session->delete_ids_key]['BMD_identity_index'])
							->delete();
			}

		$session->set('message_2', 'All FreeComETT transcription data for, '.$session->delete_ids[$session->delete_ids_key]['BMD_user'].', '.$session->delete_ids[$session->delete_ids_key]['realname'].', in project, '.$session->current_project[0]['project_name'].', has been deleted.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('syndicate/manage_users_step1/'.$session->saved_syndicate_index));
	}
	
	public function add_syndicate_to_def_image_table()
	{
		// Def_images has been changed to include the syndicate. Need to create records for each syndicate using the NULL syndicate entries as base
		// initialise
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$def_image_model = new Def_Image_Model();
		
		// get syndicates
		$syndicates = $syndicate_model
			->where('project_index', $session->current_project[0]['project_index'])
			->find();
		
		// read the syndicates one by one
		foreach ( $syndicates as $syndicate )
			{
				// read all def_image records with syndicate NULL
				$def_images = $def_image_model
					->where('project_index', $session->current_project[0]['project_index'])
					->where('syndicate_index', NULL )
					->find();
					
				// read def_images
				foreach ( $def_images as $def_image )
					{
						// does a record exist with this syndicate, data entry format and scan format
						$def_image_exists = $def_image_model
							->where('project_index', $session->current_project[0]['project_index'])
							->where('syndicate_index', $syndicate['BMD_syndicate_index'])
							->where('data_entry_format', $def_image['data_entry_format'])
							->where('scan_format', $def_image['scan_format'])
							->find();
							
						// found?
						if ( ! $def_image_exists )
							{
								// if not add it with the current syndicate
								$def_image_model
									->set(['project_index' => $session->current_project[0]['project_index']])
									->set(['syndicate_index' => $syndicate['BMD_syndicate_index']])
									->set(['data_entry_format' => $def_image['data_entry_format']])
									->set(['scan_format' => $def_image['scan_format']])
									->set(['image_x' => $def_image['image_x']])
									->set(['image_y' => $def_image['image_y']])
									->set(['image_rotate' => $def_image['image_rotate']])
									->set(['image_scroll_step' => $def_image['image_scroll_step']])
									->set(['panzoom_x' => $def_image['panzoom_x']])
									->set(['panzoom_y' => $def_image['panzoom_y']])
									->set(['panzoom_z' => $def_image['panzoom_z']])
									->set(['sharpen' => $def_image['sharpen']])
									->insert();
							}
					}
			}
			
		// send complete message and redirect
		$session->set('message_2', 'Def Image records have been added to Def Images Table for all syndicates in your project '.$session->current_project[0]['project_name'].'.' );
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('database/database_step1/1') );
	}
	
	public function add_syndicate_to_def_fields_table()
	{
		// Def_fields has been changed to include the syndicate. Need to create records for each syndicate using the NULL syndicate entries as base
		// initialise
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$def_fields_model = new Def_Fields_Model();
		
		// get syndicates from project server
		switch ( $session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':
					// get syndicates from server
					$db = \Config\Database::connect($session->syndicate_DB);
					$sql =	"
								SELECT * 
								FROM SyndicateTable 
								WHERE SyndicateTable.SyndicateShortDesc NOT LIKE 'This syndicate is no longer active having completed its agreed allocations.'
								ORDER BY SyndicateTable.SyndicateID
							";
					$query = $db->query($sql);
					$project_syndicates = $query->getResultArray();
					break;
				case 'FreeREG':
					break;
				case 'FreeCEN':
					break;
			}
		
		// read the syndicates one by one
		foreach ( $project_syndicates as $syndicate )
			{
				// read all def_image records with syndicate NULL
				$def_fields = $def_fields_model
					->where('project_index', $session->current_project[0]['project_index'])
					->where('syndicate_index', NULL )
					->find();
					
				// read def_images
				foreach ( $def_fields as $def_field )
					{
						// does a record exist with this syndicate, data entry format and scan format
						$def_field_exists = $def_fields_model
							->where('project_index', $session->current_project[0]['project_index'])
							->where('syndicate_index', $syndicate['SyndicateID'])
							->where('data_entry_format', $def_field['data_entry_format'])
							->where('scan_format', $def_field['scan_format'])
							->where('field_order', $def_field['field_order'])
							->where('field_name', $def_field['field_name'])
							->find();
							
						// found?
						if ( ! $def_field_exists )
							{
								// if not add it with the current syndicate
								$def_fields_model
									->set(['project_index' => $session->current_project[0]['project_index']])
									->set(['syndicate_index' => $syndicate['SyndicateID']])
									->set(['data_entry_format' => $def_field['data_entry_format']])
									->set(['scan_format' => $def_field['scan_format']])
									->set(['field_order' => $def_field['field_order']])
									->set(['field_name' => $def_field['field_name']])
									->set(['column_name' => $def_field['column_name']])
									->set(['column_width' => $def_field['column_width']])
									->set(['font_size' => $def_field['font_size']])
									->set(['font_weight' => $def_field['font_weight']])
									->set(['field_align' => $def_field['field_align']])
									->set(['pad_left' => $def_field['pad_left']])
									->set(['html_name' => $def_field['html_name']])
									->set(['html_id' => $def_field['html_id']])
									->set(['field_type' => $def_field['field_type']])
									->set(['blank_OK' => $def_field['blank_OK']])
									->set(['date_format' => $def_field['date_format']])
									->set(['volume_quarterformat' => $def_field['volume_quarterformat']])
									->set(['volume_roman' => $def_field['volume_roman']])
									->set(['table_fieldname' => $def_field['table_fieldname']])
									->set(['capitalise' => $def_field['capitalise']])
									->set(['dup_fieldname' => $def_field['dup_fieldname']])
									->set(['dup_fromfieldname' => $def_field['dup_fromfieldname']])
									->set(['special_test' => $def_field['special_test']])
									->set(['virtual_keyboard' => $def_field['virtual_keyboard']])
									->set(['input_first_line' => $def_field['input_first_line']])
									->set(['js_event' => $def_field['js_event']])
									->set(['js_function' => $def_field['js_function']])
									->set(['auto_full_stop' => $def_field['auto_full_stop']])
									->set(['auto_copy' => $def_field['auto_copy']])
									->set(['auto_focus' => $def_field['auto_focus']])
									->set(['colour' => $def_field['colour']])
									->set(['field_format' => $def_field['field_format']])
									->insert();
							}
					}
			}
			
		// send complete message and redirect
		$session->set('message_2', 'Def Field records have been added to Def Fields Table for all syndicates in your project '.$session->current_project[0]['project_name'].'.' );
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('database/database_step1/1') );
	}
	
	public function set_coord_role()
	{
		// initialise
		$session = session();
		$identity_model = new Identity_Model();
		
		// get FreeComETT identities
		$identities = $identity_model
			->where('project_index', $session->current_project[0]['project_index'])
			->find();
			
		// read identities
		foreach ( $identities as $identity )
			{
				// get syndicate member table entry from project
				switch ( $session->current_project[0]['project_name']) 
					{
						case 'FreeBMD':
							// get syndicate member from server
							$db = \Config\Database::connect($session->syndicate_DB);
							$sql =	"
										SELECT * 
										FROM SyndicateMembers
										WHERE SyndicateMembers.UserID = '".$identity['BMD_user']."'
									";
							$query = $db->query($sql);
							$project_member = $query->getResultArray();
							break;
						case 'FreeREG':
							break;
						case 'FreeCEN':
							break;
					}
				
				// found?
				if ( $project_member )
					{
						// coordinator?
						if ( $project_member[0]['CoOrdinator'] == 'Y' )
							{
								// set data for identity update
								$identity_model
									->where('project_index', $session->current_project[0]['project_index'])
									->where('BMD_identity_index', $identity['BMD_identity_index'])
									->where('role_index !=', 1)
									->set(['role_index' => 2])
									->update();
							}
					}
			}
		
		// set return
		$session->set('message_2', 'Coordinator role has been set for all existing coordinators.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('database/database_step1/1') );
	}
	
	public function fix_calibration_step1($start_message, $transcription_index)
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		$detail_data_model = new Detail_Data_Model();
		$allocation_model = new Allocation_Model();
		$session->fix_calib_index = $transcription_index;
	
		// set messages
		switch ($start_message) 
			{
				case 0:
					// get the transcription
					$session->fix_calib_transcription = $transcription_model
						->where('BMD_header_index', $session->fix_calib_index)
						->where('project_index',  $session->current_project[0]['project_index'])
						->find();
					// get transcription detail def
					$session->fix_calib_def_fields = $transcription_detail_def_model
						->where('transcription_index', $session->fix_calib_index)
						->where('project_index', $session->current_project[0]['project_index'])
						->orderby('field_order','ASC')
						->findAll();
					// get transcription details
					$session->fix_calib_details = $detail_data_model
						->where('BMD_header_index', $session->fix_calib_index)
						->where('project_index',  $session->current_project[0]['project_index'])
						->orderby('BMD_line_sequence','ASC')
						->findAll();
					$last_line_key = array_key_last($session->fix_calib_details);
					$session->fix_calib_last_lineno = $session->fix_calib_details[$last_line_key]['BMD_line_sequence'];
					$first_line_key = array_key_first($session->fix_calib_details);
					$session->fix_calib_first_lineno = $session->fix_calib_details[$first_line_key]['BMD_line_sequence'];
					// get allocation
					$session->fix_calib_allocation = $allocation_model	
						->where('BMD_allocation_index',  $session->fix_calib_transcription[0]['BMD_allocation_index'])
						->where('project_index', $session->current_project[0]['project_index'])
						->find();
		
					// setup the image
					// set image parameters
					$session->set('panzoom_x', 1);
					$session->set('panzoom_y', 1);
					$session->set('panzoom_z', $session->fix_calib_transcription[0]['BMD_panzoom_z']);
					$session->image_y = 350;
					
					// set creds
					$user = rawurlencode($session->identity_userid);
					$mdp = rawurlencode($session->identity_password);
							
					// initialse image			
					$url = 	$session->current_project[0]['project_autoimageservertype']
							.$user
							.':'
							.$mdp
							.'@'
							.$session->current_project[0]['project_autoimageurl']
							.$session->fix_calib_allocation[0]['BMD_reference']
							.$session->fix_calib_transcription[0]['BMD_scan_name'];						
					$session->url = $url;
								
					// get image info to get mime type
					$imageInfo = getimagesize($url);
				
					// get mime type
					$session->mime_type = $imageInfo['mime'];
					
					// encode to base 64
					$session->fileEncode = base64_encode(file_get_contents($url));
					
					// message defaults
					$session->cols = 0;
					$session->set('message_1', 'Start by telling me how many columns are in the scan for this Transcription => '.$session->fix_calib_transcription[0]['BMD_file_name']);
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Start by telling me how many columns are in the scan for this Transcription => '.$session->fix_calib_transcription[0]['BMD_file_name']);
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}

		// request fix details
		// show views
		echo view('templates/header');
		echo view('linBMD2/fix_calibration_cols');
		echo view('linBMD2/transcribe_panzoom');
		echo view('templates/footer');		
	}
	
	public function fix_calibration_step2($start_message)
	{
		// initialise method
		$session = session();
		
		// get number of cols
		$session->cols = $this->request->getPost('columns');

		// test input
		if ( $session->cols < 2 OR $session->cols > 6 )
			{
				$session->set('message_2', 'Number of columns must be at least 2 and no more than 6.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('database/fix_calibration_step1/1/'.$session->fix_calib_index) );
			}
			
		// set messages
		switch ($start_message) 
			{
				case 0:
					// Initialise columns array - 6 max
					$columns = array();
					for( $i = 0; $i < $session->cols; $i++ ) 
						{
							$columns[$i]['column'] = $i + 1;
							if ( $i == 0 )
								{
									$columns[$i]['first_line'] = $session->fix_calib_first_lineno;
								}
							else
								{
									$columns[$i]['first_line'] = 0;
								}
							if ( $i == $session->cols - 1 )
								{
									$columns[$i]['last_line'] = $session->fix_calib_last_lineno;
								}
							else
								{
									$columns[$i]['last_line'] = 0;
								}
							$columns[$i]['panzoom_x'] = 0;
							$columns[$i]['panzoom_y'] = 0;
						}
					$session->columns = $columns;
					// message defaults
					$session->set('message_1', 'Fix the calibration for this Transcription => '.$session->fix_calib_transcription[0]['BMD_file_name']. ' <= by providing the data required in the table below.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Fix the calibration for this Transcription => '.$session->fix_calib_transcription[0]['BMD_file_name']. ' <= by providing the data required in the table below.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}

		// request fix details
		// show views
		echo view('templates/header');
		echo view('linBMD2/fix_calibration');
		echo view('templates/footer');		
	}
	
	public function fix_calibration_step3($start_message)
	{
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$transcription_model = new Transcription_Model();
		
		// get input
		$columns_input = array();
		$columns_input = json_decode($this->request->getPost('columns'), true);

		// read columns
		foreach ($columns_input as $column )
			{
				$this_column_first_line = 0;
				foreach ( $session->fix_calib_details as $line )
					{
						if ( $line['BMD_line_sequence'] >= $column['fl'] AND $line['BMD_line_sequence'] <= $column['ll'] )
							{
								if ( $this_column_first_line == 0 )
									{
										$new_py = $column['py'];
										$this_column_first_line = 1;
									}
								else
									{
										$new_py = $new_py - $session->fix_calib_transcription[0]['BMD_image_scroll_step'];
									}
								$detail_data_model
									->set(['BMD_line_panzoom_x' => $column['px']])
									->set(['BMD_line_panzoom_y' => $new_py])
									->where('BMD_index', $line['BMD_index'])
									->update();
							}
					}
			}
		
		// reload details
		$session->fix_calib_details = $detail_data_model
			->where('BMD_header_index', $session->fix_calib_index)
			->where('project_index',  $session->current_project[0]['project_index'])
			->orderby('BMD_line_sequence','ASC')
			->findAll();
		$last_line_key = array_key_last($session->fix_calib_details);
		$session->fix_calib_last_px = $session->fix_calib_details[$last_line_key]['BMD_line_panzoom_x'];
		$transcription_model
			->set(['BMD_panzoom_x' => $session->fix_calib_details[$last_line_key]['BMD_line_panzoom_x']])
			->set(['BMD_panzoom_y' => $session->fix_calib_details[$last_line_key]['BMD_line_panzoom_y']])
			->where('BMD_header_index', $session->fix_calib_details[$last_line_key]['BMD_header_index'])
			->update();
							
		// return
		$session->set('message_2', 'Fixed detail line image coordinates for => '.$session->fix_calib_transcription[0]['BMD_file_name']);
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('syndicate/show_all_transcriptions_step1/'.$session->saved_syndicate_index) );
	}
	
}
