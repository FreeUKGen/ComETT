<?php namespace App\Controllers;

use App\Models\Transcription_Model;
use App\Models\Transcription_Detail_Def_Model;
use App\Models\Transcription_Comments_Model;
use App\Models\Header_Table_Details_Model;
use App\Models\Detail_Data_Model;
use App\Models\Detail_Comments_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Identity_Model;
use App\Models\Identity_Last_Indexes_Model;
use App\Models\Transcription_Cycle_Model;
use App\Models\Parameter_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;
use App\Models\Def_Ranges_Model;
use App\Models\Def_Fields_Model;
use App\Models\Def_Image_Model;
use App\Models\Transcription_CSV_File_Model;
use App\Models\Project_Types_Model;

class Transcribe extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('image');
        helper('transcribe');
        helper('email');
        helper('update_names');
        helper('text');
        helper('report');
    }
	
	public function transcribe_step1($start_message)
	{ 		
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle = new Transcription_Cycle_Model();
		$detail_data_model = new Detail_Data_Model();
		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Please select the action you wish to perform on the '.$session->current_project[0]['project_name'].'  transcription and click GO. Or create a new '.$session->current_project[0]['project_name'].' transcription. The list is initially ordered by Last change date. Click on column name to change sort order.');
					$session->set('message_class_1', 'alert alert-primary');
					if ( $session->message_2 == '' )
						{
							$session->set('message_2', '');
							$session->set('message_class_2', '');
						}
					// flow control
					$session->BMD_cycle_code = '';
					$session->set('show_view_type', 'transcribe');
					// set defaults
					$session->set('close_header', 'N');
					// sort
					if ( ! isset($session->sort_by) )
						{
							$session->sort_by = 'transcription.Change_date';
							$session->sort_order = 'DESC';
							$session->sort_name = 'Last change date/time';
						}
					break;
				case 1:
					break;
				case 2:
					$session->BMD_cycle_code = '';
					$session->set('message_1', 'Please select the action you wish to perform on the '.$session->current_project[0]['project_name'].'  transcription and click GO. Or create a new '.$session->current_project[0]['project_name'].' transcription. The list is initially ordered by Last change date. Click on column name to change sort order.');
					break;
				default:
			}
		
		// for open transcriptions check verfied status
		if ( $session->status == '0' )
			{
				$transcriptions = $transcription_model	
					->where('project_index', $session->current_project[0]['project_index'])
					->where('BMD_syndicate_index', $session->syndicate_id)	
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('BMD_header_status', $session->status)
					->findAll();
					
				foreach ( $transcriptions as $transcription )
					{
						// any lines with verified = NO?
						$lines = $detail_data_model
							->where('BMD_header_index', $transcription['BMD_header_index'])
							->where('line_verified', 'NO')
							->findAll();
							
						// any found?
						if ( $lines )
							{
								$transcription_model
									->set(['verified' => 'NO'])
									->update($transcription['BMD_header_index']);
							}
						else
							{
								$transcription_model
									->set(['verified' => 'YES'])
									->update($transcription['BMD_header_index']);
							}
					}
			}
			
		// get headers as per status flag.
		$session->transcriptions = $transcription_model	
			->join('allocation', 'transcription.BMD_allocation_index = allocation.BMD_allocation_index')
			->join('syndicate', 'transcription.BMD_syndicate_index = syndicate.BMD_syndicate_index')
			->join('transcription_comments', 'transcription.BMD_header_index = transcription_comments.transcription_index', 'left')
			->where('transcription.project_index', $session->current_project[0]['project_index'])
			->where('transcription.BMD_syndicate_index', $session->syndicate_id)	
			->where('transcription.BMD_identity_index', $session->BMD_identity_index)
			->where('transcription.BMD_header_status', $session->status)
			->select('	transcription.BMD_header_index, 
						transcription.BMD_file_name, 
						transcription.BMD_scan_name, 			
						transcription.BMD_records, 
						transcription.BMD_start_date, 
						transcription.Change_date,
						transcription.verified,
						transcription.BMD_submit_date, 
						transcription.BMD_submit_status, 
						transcription.BMD_last_action, 
						allocation.BMD_allocation_name, 
						allocation.scan_format, 
						syndicate.BMD_syndicate_name,
						transcription_comments.comment_text
					')
			->orderBy($session->sort_by, $session->sort_order)
			->findAll();

		// were any found?
		if ( ! $session->transcriptions )
			{
				if ( $session->status == '0' )
					{
						$session->set('message_2', 'You have no ACTIVE '.$session->current_project[0]['project_name'].' transcriptions to work on. Please create a new one.');
					}
				else
					{
						$session->set('message_2', 'You have no CLOSED '.$session->current_project[0]['project_name'].' transcriptions.');
					}
				$session->set('message_class_2', 'alert alert-danger');
			}
			
		
								
		
		// show open headers for this user for view transcribe_home														
		echo view('templates/header');
		switch ($session->show_view_type) 
			{
				case 'transcribe':
					echo view('linBMD2/transcribe_home');
					echo view('linBMD2/sortTableNew');
					echo view('linBMD2/searchTableNew');
					break;
				case 'close_header':
					echo view('linBMD2/transcribe_close_header');
					break;
				case 'verify_BMD':
					echo view('linBMD2/transcribe_verify_BMD');
					break;
				case 'image_parameters':
					echo view('linBMD2/transcribe_image_parameters');
					break;
				case 'enter_parameters':
					echo view('linBMD2/transcribe_enter_parameters');
					break;
				case 'show_raw_BMD':
					echo view('linBMD2/show_raw_BMD');
					break;
			}
		echo view('templates/footer');
	}	
	
	public function transcribe_next_action()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$detail_data_model = new Detail_Data_Model;
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		$identity_last_indexes_model = new Identity_Last_Indexes_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// destroy any feh windows
		$session->remove('feh_show');

		// if coming here from Transcribe or Verify set variables without getPost
		switch ($session->BMD_cycle_code) 
			{
				case 'INPRO':
					$BMD_header_index = $session->current_transcription[0]['BMD_header_index'];
					$session->last_cycle_code = $session->BMD_cycle_code;
					$session->verifytranscribe_calibrate = 'Y';
					$session->BMD_cycle_code = 'CALIB';
					break;
				case 'VERIT':
					$BMD_header_index = $session->current_transcription[0]['BMD_header_index'];
					$session->last_cycle_code = $session->BMD_cycle_code;
					$session->verifytranscribe_calibrate = 'Y';
					$session->BMD_cycle_code = 'CALIB';
					break;
				default:
					// get inputs
					$BMD_header_index = $this->request->getPost('BMD_header_index');
					$session->BMD_cycle_code = $this->request->getPost('BMD_next_action');
																	
					// get transcription
					$session->current_transcription = $transcription_model
						->where('BMD_header_index',  $BMD_header_index)
						->where('BMD_identity_index', $session->BMD_identity_index)
						->where('project_index', $session->current_project[0]['project_index'])
						->find();	
					if ( ! $session->current_transcription )
						{
							$session->set('message_2', 'Invalid transcription, please select again. Please inform '.$session->linbmd2_email);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/transcribe_step1/2') );
						}
						
					// get transcription details
					$transcription_details = $detail_data_model
						->where('BMD_header_index', $BMD_header_index)
						->where('project_index',  $session->current_project[0]['project_index'])
						->orderby('BMD_line_sequence','ASC')
						->findAll();
					if ( $transcription_details )
						{
							$last_line_key = array_key_last($transcription_details);
						}
					else
						{
							$last_line_key = 0;
						}
						
					// set current transcription for highlight in list
					$session->set('current_header_index', $session->current_transcription[0]['BMD_header_index']);
						
					// set the calibrate flag
					$session->verifytranscribe_calibrate = 'N';
					
					// if NONE selected, try to start last action - issue 162
					if ( $session->BMD_cycle_code == 'NONE' )
						{
							// get last action code
							$session->last_cycle_code = $transcription_cycle_model
								->where('BMD_cycle_type', 'TRANS')
								->where('BMD_cycle_name', $session->current_transcription[0]['BMD_last_action'])
								->select('BMD_cycle_code')
								->find();
							// found?
							if ( $session->last_cycle_code )
								{
									$session->BMD_cycle_code = $session->last_cycle_code[0]['BMD_cycle_code'];
								}
						}
					break;
			}
			
		// get allocation
		$session->current_allocation = $allocation_model	
			->where('BMD_allocation_index',  $session->current_transcription[0]['BMD_allocation_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->find();
										
		if ( ! $session->current_allocation )
			{
				$session->set('message_2', 'Invalid allocation, please select again in transcribe/transcribe_next_action. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// get syndicate
		$session->current_syndicate = $syndicate_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_syndicate_index',  $session->current_transcription[0]['BMD_syndicate_index'])
			->find();

		if ( ! $session->current_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate, please select again in transcribe/transcribe_next_action. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
	
		// perform action selected
		switch ($session->BMD_cycle_code) 
			{
				case 'NONE': // nothing was selected
					// if here = no last action code found
					$session->set('message_2', 'Please select an action to perform from the dropdown.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/transcribe_step1/0') );
					break;
				case 'INPRO': // Transcribe from scan
					// set flags flag
					$session->verify = 0;
					$session->detail_line = ['BMD_index' => 0];
					$session->initialise_image_for_panzoom = 0;
					
					// setup image and parameters
					$this->setup_image_and_parameters();
					
					// update last action
					$this->update_last_action($BMD_header_index);
									
					// redirect to controller for the type. BMD_type is set when allocation created. Type table is loaded in common helper for each projet
					switch ($session->current_allocation[0]['BMD_type']) 
						{
							case 'B': // = Births in FreeBMD
								return redirect()->to( base_url('births/transcribe_births_step1/0') );
								break;
							case 'M': // = Marriages in FreeBMD
								return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
								break;
							case 'D': // = Deaths in FreeBMD
								return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
								break;
								// cases for types in other projects, FreeREG, FreeCEN
							default:
						}
				case 'UPBMD': // upload BMD file
					
					// update last action
					$this->update_last_action($BMD_header_index);
							
					return redirect()->to( base_url('transcribe/upload_BMD_file/') );
					break;
				case 'UPDET': // show upload return message
					
					// update last action
					$this->update_last_action($BMD_header_index);
							
					return redirect()->to( base_url('transcribe/submit_details/') );
					break;
				case 'CLOST': // close BMD file
					// update last action - this is done in the close routine.
					$session->set('close_header', 'N');
					return redirect()->to( base_url('transcribe/close_header_step1/'.$BMD_header_index) );
					break;
				case 'VERIT': // verify transcription file
					// initialise
					$session->detail_line_index = $session->current_transcription[0]['last_verified_detail_index'];	
					
					// setup image and parameters
					$this->setup_image_and_parameters();
					
					// update last action
					$this->update_last_action($BMD_header_index);
										
					return redirect()->to( base_url('transcribe/verify_step1/'.$BMD_header_index) );
					break;
				case 'CALIB': // calibrate
					
					if ( $last_line_key != 0 )
						{
							if ( $transcription_details[$last_line_key]['BMD_line_sequence'] > 1000 )
								{
									$session->set('message_2', 'You have already transcribed quite a few records for this transcription so calibration is not available.');
									$session->set('message_class_2', 'alert alert-danger');
									return redirect()->to( base_url('transcribe/transcribe_step1/2') );
								}
						}
					
					// setup image and parameters
					$session->image_processed = '';
					$this->setup_image_and_parameters();
					
					// update last action
					$this->update_last_action($BMD_header_index);
						
					$session->calibrate = 0;
					$session->stop_calibrate = '';
					return redirect()->to( base_url('transcribe/calibrate_step1/0') );
					break;
				case 'CRBMD': // create BMD file only, no upload
					
					// update last action
					$this->update_last_action($BMD_header_index);
						
					return redirect()->to( base_url('transcribe/store_BMD_file/'.$BMD_header_index) );
					break;
				case 'VEBMD': // show raw BMD file
					
					// update last action
					$this->update_last_action($BMD_header_index);
						
					return redirect()->to( base_url('transcribe/show_raw_BMD_file/'.$BMD_header_index) );
					break;
				case 'UPDBM': // send BMD file to syndicate leader
					
					// update last action
					$this->update_last_action($BMD_header_index);
						
					return redirect()->to( base_url('transcribe/send_BMD_file_to_syndicate_leader/') );
					break;
				case 'DELTR': // delete transcription
					// update last action - no point as the transcription is deleted
					return redirect()->to( base_url('transcription/delete/'.$BMD_header_index) );
					break;
				case 'UPCOM': // Add / Change / remove Transcription Comments
					
					// update last action
					$this->update_last_action($BMD_header_index);
						
					return redirect()->to( base_url('transcription/comments_step1/0') );
					break;
			}							
	}
	
	public function create_BMD_file()
	{
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$detail_comments_model = new Detail_Comments_Model();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
				
		// transcription detail def
		$session->current_transcription_def_fields = $transcription_detail_def_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
			->where('scan_format', $session->current_allocation[0]['scan_format'])
			->orderby('field_order','ASC')
			->findAll();
			
		// build data file and populate depending on project: methods are in transcribe helper
		switch ($session->current_project[0]['project_name'])
			{
				case 'FreeBMD':
					freebmd_createCSV();
					break;
				case 'FreeREG':
					freereg_createCSV();
					break;
				case 'FreeCEN':
					freecen_createCSV();
					break;
			}
	}
	
	public function store_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		// get detail data
		$session->detail_data =	$detail_data_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_status', '0')
			->where('BMD_surname !=', '')
			->orderby('BMD_line_sequence','ASC')
			->findAll();
			
		if ( ! $session->detail_data )
			{
				$session->set('message_2', 'No detail data found for this Transcription. Cannot create BMD file. Have you completed transcribing the scan?');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		// create the BMD file
		$this->create_BMD_file($BMD_header_index);
		if ( $session->unknown_char == 1 )
			{
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// show message
		$session->set('message_2', 'BMD file successfully created but not uploaded to '.$session->current_project[0]['project_name'].'. Use option \'Show raw BMD file\' to see it.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
		
	public function show_raw_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$transcription_CSV_file_model = new Transcription_CSV_File_Model();
		
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// get the csv_file
		$session->csv_file =	$transcription_CSV_file_model
								->where('transcription_index', $BMD_header_index)
								->find();
				
		// found?
		if ( ! $session->csv_file )
			{
				$session->set('message_2', 'The BMD data for this transcription does not exist. Please create it first if you want to read it.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		
		// show the raw file BMD file
		$session->set('message_1', 'Here is the raw BMD data you requested = '.$session->current_transcription[0]['BMD_file_name'].'. You cannot change it here.');
		$session->set('message_class_1', 'alert alert-primary');
		$session->set('show_view_type', 'show_raw_BMD');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
	}
	
	public function send_BMD_file_to_syndicate_leader()
	{
		// initialise method
		$session = session();
		$transcription_CSV_file_model = new Transcription_CSV_File_Model();
		
		// get the csv data from the DB
		$csv_file =	$transcription_CSV_file_model
					->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
					->find();
		if ( ! $csv_file )
			{
				$session->set('message_2', 'The BMD data for this transcription, '.$session->current_transcription[0]['BMD_file_name'].', does not exist. Please create it first if you want to send it to your syndicate coordinator.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		if ( $csv_file[0]['csv_string'] == '' )
			{
				$session->set('message_2', 'The BMD data for this transcription, '.$session->current_transcription[0]['BMD_file_name'].', does not exist. Please create it first if you want to send it to your syndicate coordinator.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		
		// set bmd data variable
		$session->BMD_data = $csv_file[0]['csv_string'];
		
		// send email
		return redirect()->to( base_url('email/send_email/BMD_file') );
	}
	
	public function upload_BMD_file()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$allocation_model = new Allocation_Model();
		$detail_data_model = new Detail_Data_Model();
		$transcription_CSV_file_model = new Transcription_CSV_File_Model();
		
		// get detail data
		$session->detail_data =	$detail_data_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
								->where('BMD_identity_index', $session->BMD_identity_index)
								->where('BMD_status', '0')
								->orderby('BMD_line_sequence','ASC')
								->findAll();
		if ( ! $session->detail_data )
			{
				$session->set('message_2', 'No detail data found for this Transcription. Cannot upload BMD file. Have you completed transcribing the scan?');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		
		// has the transcription been verified? Can't upload unless it has
		if ( $session->current_transcription[0]['verified'] == 'NO' )
			{
				$session->set('message_2', 'Before uploading your transcribed data you must verify it. Please do so by selecting the \'Verify Transcription\' option.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		
		// create the BMD upload file - this stores the csv file in the DB
		$this->create_BMD_file();
		
		// get the csv data from the DB
		$csv_file =	$transcription_CSV_file_model
					->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
					->find();
		if ( ! $csv_file )
			{
				// if found update
				$session->set('message_2', 'No CSV data found for this Transcription. Cannot upload BMD file. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Transcribe::upload_BMD_file.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}

		// create the temporary upload curl file
		// see here for solution - https://stackoverflow.com/questions/56664413/how-to-use-the-content-of-the-file-instead-of-filename-for-curl-file-create-fu
		$fhup = tmpfile();
		fwrite($fhup, $csv_file[0]['csv_string']);
		$tmpf = stream_get_meta_data($fhup)['uri'];
		$cfile = curl_file_create($tmpf);
		
		// set up the standard fields to pass to curl - do not URL encode this data.
		$postfields = array(
							"UploadAgent" => $session->uploadagent,
							"user" => $session->identity_userid,
							"password" => $session->identity_password,
							"content2" => $cfile,
							"data_version" => "districts.txt:??"
							);
		
		// set the specific fields to pass to curl, depends on whether CSV file exists of not.
		// does BMD file already exist on FreePROJECT? : method in common_helper
		BMD_file_exists_on_project($session->current_transcription[0]['BMD_file_name']);
		switch ( $session->BMD_file_exists_on_project )
		{
			case '0': // file does not already exist on project
				$postfields['file'] = $session->current_transcription[0]['BMD_file_name'];
				break;
			case '1': // file already exists on project
				$postfields['file_update'] = $session->current_transcription[0]['BMD_file_name'];
				break;
		}
		
		// set up the curl - $session->curl_url is set in Identity
		$ch = curl_init($session->curl_url);
						
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.$session->version);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// run the curl
		$curl_result = curl_exec($ch);
		
		if ( $curl_result === false OR $curl_result == '' )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to post CSV file to server in Transcribe::upload_BMD_file => '.$session->curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		
		// close the curl handle and file handle
		fclose($fhup);
		
		// load returned data to array
		$lines = preg_split("/\r\n|\n|\r/", $curl_result);
					
		// now test to see if a valid return was found
		if ( strpos($lines[0], "fileupload result") !== FALSE )
			{
				$upload_status = explode("=", $lines[0]);
				// test status
				$upload_status[1] = rtrim($upload_status[1]);
				// take appropriate action depending on status
				switch ($upload_status[1]) 
				{
					case "OK":
						// update header
						$data =	[
									'BMD_submit_date' => $session->current_date,
									'BMD_submit_status' => $upload_status[1],
									'BMD_submit_message' => $curl_result,
									'BMD_header_status' => '1',
								];
						$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
						
						// update allocation with last page uploaded
						$data =	[
									'BMD_last_uploaded' => $session->current_transcription[0]['BMD_current_page']
								];
						$allocation_model->update($session->current_transcription[0]['BMD_allocation_index'], $data);
						
						// action depending on whether UPLOAD or REPLACE
						switch ($session->BMD_file_exists_on_project)
							{
								case '0': // file did not already exist on FreeBMD
									$session->set('message_2', 'Transcription, '.$session->current_transcription[0]['BMD_file_name'].', successfully UPLOADED to '.$session->current_project[0]['project_name'].' and closed by FreeComETT. You can see it in your CLOSED Transcriptions list.');
									break;
									
								case '1': // file already existed on FreeBMD
									$session->set('message_2', 'Transcription '.$session->current_transcription[0]['BMD_file_name'].', successfully REPLACED on '.$session->current_project[0]['project_name'].' and closed by FreeComETT. You can see it in your CLOSED Transcriptions list.');
									break;
							}
						$session->set('message_class_2', 'alert alert-success');
						break;
					case "failed":
						// isolate errors
						$errors = explode("<errors>", $curl_result);
						// convert error[0] to string
						$error_string = $errors[0];
						// do I have an error string or a reason
						if ( $error_string != $curl_result )
							{
								// I have an error string
								$errors = explode("</errors>", $errors[1]); 
								$errors = trim($errors[0]);
							}
						else
							{
								// Do I have a reason string == explode failed
								$errors = explode("reason=", $curl_result);
								// was reason found
								$error_string = $errors[0];
								if ( $error_string != $curl_result )
									{
										// I have an reason string
										$errors = explode(">", $errors[1]);
										$errors = trim($errors[0]);
									}
								else
									{
										// unknown error
										$errors = 'Unknown';
									}
							}

						$data =	[
									'BMD_submit_date' => $session->current_date,
									'BMD_submit_status' => $upload_status[1],
									'BMD_submit_message' => $errors,
								];
						$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
						//
						$session->set('message_2', 'Transcription upload FAILED for '.$session->current_transcription[0]['BMD_file_name'].'. See errors by clicking on the status of the file concerned. Transcription remains in your ACTIVE Transcriptions list.');
						$session->set('message_class_2', 'alert alert-danger');
						break;
					case "warnings":
						// isolate warnings
						$warnings = explode("<warnings>", $curl_result);
						$warnings = explode("</warnings>", $warnings[1]); 
						$warnings = trim($warnings[0]);
						$data =	[
									'BMD_submit_date' => $session->current_date,
									'BMD_submit_status' => $upload_status[1],
									'BMD_submit_message' => $warnings,
								];
						$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
						
						// update allocation with last page uploaded
						$data =	[
									'BMD_last_uploaded' => $session->current_transcription[0]['BMD_current_page']
								];
						$allocation_model->update($session->current_transcription[0]['BMD_allocation_index'], $data);
						
						// action depending on whether UPLOAD or REPLACE
						switch ($session->BMD_file_exists_on_project)
							{
								case '0': // file did not already exist on FreeBMD
									$session->set('message_2', 'Transcription, '.$session->current_transcription[0]['BMD_file_name'].', successfully UPLOADED to '.$session->current_project[0]['project_name'].' but with warnings. See warnings by clicking on the status of the file concerned. Transcription remains in your ACTIVE Transcriptions list.');
									break;
								case '1': // file already existed on FreeBMD
									$session->set('message_2', 'Transcription, '.$session->current_transcription[0]['BMD_file_name'].', successfully REPLACED on '.$session->current_project[0]['project_name'].' but with warnings. See warnings by clicking on the status of the file concerned. Transcription remains in your ACTIVE Transcriptions list.');
									break;
							}
						$session->set('message_class_2', 'alert alert-warning');
						break;
				}
			}
		else
			{
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to determine curl result in Transcribe::upload_BMD_file => '.$session->curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
				
		// all done
		
		// redirect
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );
	}
	
	public function submit_details()
	{
		// initialise method
		$session = session();
		
		// show upload details for this header																				
		echo view('templates/header');
		echo view('linBMD2/transcribe_submit_details');
		echo view('templates/footer');
	}
	
	public function close_header_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		// can I close this file = if not uploaded successfully
		if ( $session->current_transcription[0]['BMD_submit_date'] == '' OR $session->current_transcription[0]['BMD_submit_status'] == 'failed' )
			{
				$session->set('message_2', 'This transcription has not been uploaded or it was not uploaded successfully. Normally you should not close it. Instead fix the upload errors.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		else
			{
				$session->set('message_2', 'Please confirm close of this transcription file. You can reopen it by clicking the reopen button on the transcription home page');
				$session->set('message_class_2', 'alert alert-primary');
			}
		// ask for confirmation
		$session->set('show_view_type', 'close_header');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function close_header_step2()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		
		// get inputs
		$session->set('close_header', $this->request->getPost('close_header'));
		
		// test for close

		if ( $session->close_header == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm close. The Transcription, '.$session->current_transcription[0]['BMD_file_name'].', is still open.');
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		else
			{
				// get the cycle text
				$session->set('BMD_cycle_text',	$transcription_cycle_model	
											->where('BMD_cycle_code', 'CLOST')
											->where('BMD_cycle_type', 'TRANS')
											->find());
				
				// update the header as closed
				$data =	[
							'BMD_end_date' => $session->current_date,
							'BMD_header_status' => '1',
							'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
						];
				$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
				
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'Transcription, '.$session->current_transcription[0]['BMD_file_name'].', has been closed successfully. Since it is closed, it is no longer shown in your ACTIVE transcriptions list. Go to CLOSED transcriptions if you need to reopen it.');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
	}
	
	public function verify_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		$def_ranges_model = new Def_Ranges_Model();
		$detail_data_model = new Detail_Data_Model();
		$transcription_comments_model = new Transcription_Comments_Model();
		$session->calibrate = 0;
		$session->stop_calibrate = '';
		$session->message_2 = '';
		$session->message_class_2 = '';
					
		// load $session->transcribe_detail_data in case a line has been changed
		$session->transcribe_detail_data = $detail_data_model	
			->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->orderby('BMD_line_sequence','ASC')
			->findAll();
													
		// if no detail, end
		if ( ! $session->transcribe_detail_data )
			{
				$session->BMD_cycle_code = '';
				$session->set('message_2', 'There is no detail data to verify for this transcription!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
			
		// set number of elements in order to stop loop
		$session->count_detail_lines = count($session->transcribe_detail_data);
		
		// get detail line - detail line index is set in next action method
		$session->detail_line = $session->transcribe_detail_data[$session->detail_line_index];
		
		// set previous line
		if ( $session->detail_line_index - 1 < 0 )
			{
				$session->lastEl = '';
			}
		else
			{
				$session->lastEl = $session->transcribe_detail_data[$session->detail_line_index - 1];
			}
		
		// set last detail index for line highlight and scrollintoview
		$session->set('last_detail_index', $session->detail_line['BMD_index']);
		
		// set position cursor in surname
		$session->set('position_cursor', 'surname');
			
		// get def fields
		$session->current_transcription_def_fields = $transcription_detail_def_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
			->where('scan_format', $session->current_allocation[0]['scan_format'])
			->orderby('field_order','ASC')
			->findAll();
			
		// get the current data entry format for this transcription - here, this is required for the transcribe.script to work
		$session->def_range = $def_ranges_model
			->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
			->findAll();
		
		// set image and panzoom parameters
		$session->panzoom_x = $session->detail_line['BMD_line_panzoom_x'];
		$session->panzoom_y = $session->detail_line['BMD_line_panzoom_y'];
		$session->panzoom_z = $session->detail_line['BMD_line_panzoom_z'];
		$session->sharpen = $session->current_transcription[0]['BMD_sharpen'];
		$session->scroll_step = $session->current_transcription[0]['BMD_image_scroll_step'];
		$session->image_y = $session->current_transcription[0]['BMD_image_y'];
		$session->zoom_lock = $session->current_transcription[0]['zoom_lock'];
		
		// get any header comments.
		$session->comment_text = '';
		$session->comment_text_array =	$transcription_comments_model
			->select('comment_text')
			->where('project_index', $session->current_project[0]['project_index'])
			->where('identity_index', $session->BMD_identity_index)
			->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
			->where('comment_sequence', 10)
			->find();
		// any found ?
		if ( $session->comment_text_array )
			{
				$session->comment_text = $session->comment_text_array[0]['comment_text'];
			}										
		
		// message
		$session->set('message_1', 'Verify data that you have keyed for this transcripton '.$session->current_transcription[0]['BMD_scan_name'].'. To update a line in error select Modify Line.');
		$session->set('message_class_1', 'alert alert-primary');

		if ( $session->end_of_verify == 'Y' )
			{
				// tell user they are at the end
				$session->set('message_2', 'You have finished verifying the detail data for this scan.');
				$session->set('message_class_2', 'alert alert-success');
			}
		else
			{
				if ( $session->current_transcription[0]['zoom_lock'] == 'Y' )
					{
						$session->message_2 = 'Image zoom locked by yourself during Calibration.';
						$session->message_class_2 = 'alert alert-info';
					}
			}
		
		// show views																
		echo view('templates/header');
		echo view('linBMD2/verify_step1');
		echo view('linBMD2/transcribe_details_show');
		echo view('linBMD2/transcribe_cheat_sheet');
		echo view('linBMD2/transcribe_panzoom');
		echo view('linBMD2/transcribe_script');
		echo view('templates/footer');	
	}
	
	public function verify_step2()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_comments_model = new Transcription_Comments_Model();
		$detail_data_model = new Detail_Data_Model();
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// get comment text input
		$session->set('comment_text', $_POST['comment_text']);
		
		// test length of comment_text
		if ( strlen($session->comment_text) > 100 )
			{
				$session->set('message_2', 'Please limit your transcription comment text to 100 characters max.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/verify_step1/'.$session->detail_line['BMD_header_index']));
			}
		
		// delete sequence 10 for any transcription comments
		$transcription_comments_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('identity_index', $session->BMD_identity_index)
			->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
			->where('comment_sequence', 10)
			->delete();
		// now add it again
		$data =	[
					'transcription_index' => $session->current_transcription[0]['BMD_header_index'],
					'project_index' => $session->current_project[0]['project_index'],
					'identity_index' => $session->BMD_identity_index,
					'comment_sequence' => 10,
					'comment_text' => $session->comment_text,
				];
		$transcription_comments_model->insert($data);
		
		// get input = go to line number
		$session->goto_line_seq = $this->request->getPost('goto_line');

		if ( $session->goto_line_seq != null )
			{
				// find index of the line with sequence
				$success = 0;
				foreach ( $session->transcribe_detail_data as $key => $detail )
					{
						// have I got line sequence selected?
						if ( $session->goto_line_seq == $detail['BMD_line_sequence'] )
							{
								$session->detail_line_index = $key;
								$success = 1;
								break;
							}
					}
					
				if ( $success == 0 )
					{
						$session->set('message_2', 'No line exists with the number you entered.');
						$session->set('message_class_2', 'alert alert-danger');
					}		
			}
		else
			{
				// increase detail data index
				$session->detail_line_index = $session->detail_line_index + 1;
			}
			
		// update verified line
		$data =	[
					'line_verified' => 'YES',
				];
		$detail_data_model->update($session->detail_line['BMD_index'], $data);
		
		// check end of detail data
		$session->end_of_verify = 'N';
		if ( $session->detail_line_index == $session->count_detail_lines )
			{
				// set to end of file
				$session->detail_line_index = $session->count_detail_lines - 1;
				$session->end_of_verify = 'Y';
				// update verified flag
				$data =	[
							'verified' => 'YES',
							'last_verified_detail_index' => $session->detail_line_index,
						];
				$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
				// return
				return redirect()->to( base_url('transcribe/verify_step1/'.$session->detail_line['BMD_header_index']));
			}

		// save last detail line and get new detail line
		$session->lastEl = $session->detail_line;
		$session->detail_line = $session->transcribe_detail_data[$session->detail_line_index];
		
		// set last detail index for line highlight and scrollintoview
		$session->set('last_detail_index', $session->detail_line['BMD_index']);
		
		// update last verified detail index
		$data =	[
					'last_verified_detail_index' => $session->detail_line_index,
				];
		$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
		
		// set panzoom y 
		$session->set('panzoom_y', $session->detail_line['BMD_line_panzoom_y']);
		
		return redirect()->to( base_url('transcribe/verify_step1/'.$session->detail_line['BMD_header_index']));	
	}
	
	public function verify_back_one_line()
	{
		// initialise method
		$session = session();
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// increase detail data index
		$session->detail_line_index = $session->detail_line_index - 1;
		
		// check beginning of detail data
		if ( $session->detail_line_index < 0 )
			{
				$session->detail_line_index = 0;
				$session->set('message_2', 'Cannot go back; you are already at the beginning of the data.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		
		// get new detail line
		$session->detail_line = $session->transcribe_detail_data[$session->detail_line_index];
		
		// set last detail index for line highlight and scrollintoview
		$session->set('last_detail_index', $session->detail_line['BMD_index']);
		
		//  set panzoom y 
		$session->set('panzoom_y', $session->detail_line['BMD_line_panzoom_y']);
		
		return redirect()->to( base_url('transcribe/verify_step1/'.$session->detail_line['BMD_header_index']));	
	}
	
	public function verify_delete_line()
	{
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// delete the line
		$data =	[
					'BMD_status' => '1',
								
				];
		$detail_data_model->update($session->detail_line['BMD_index'], $data);
		
		// set message
		$session->set('message_2', 'Selected line has been deleted. Pink colour applied!');
		$session->set('message_class_2', 'alert alert-success');
		
		// go round
		return redirect()->to( base_url('transcribe/verify_step1/'.$session->detail_line['BMD_header_index']));	
	}
	
	public function verify_onthefly()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_comments_model = new Transcription_Comments_Model();
		$detail_data_model = new Detail_Data_Model();
		
		$session->set('message_2', 'Verify Line-by-line active : Please verify your transcription entries. Then press Verified button.');
		$session->set('message_class_2', 'alert alert-warning');
		
		$session->verify_onthefly = 1;
		
		transcribe_show_step1($session->controller);
	}
	
	public function verify_onthefly_confirm()
	{
		// initialise method
		$session = session();
				
		transcribe_update($session->controller);
		
		return redirect()->to( base_url($session->controller.'/transcribe_'.$session->controller.'_step1/0') );
	}
	
	public function verify_back_step1($start_message)
	{
		// now that we can verify on the fly and that each line can be marked as verified,
		// we need to know whether the transcription is complete and whether all lines have been verfied.
		// to do this we need to ask the transcriber if he has finished the transcription
		// if YES, we need to check whether all lines have been verified, ie verified flag = YES
		// if NO, just back out as normal.
		// If any lines have not been verified, tell the user that the transcription cannot be considered as complete.
		// If all lines have been verified and the transcriber confirms transcription complete, then update the transcription to say that the whole transcription has been verified.
		// Remember that the upload BMD file checks this flag and allows upload only if it is YES.
		// Remember also that any change to the transcription resets this flag to no.
		// There is a "Complete Transcripton" menu item in the transcription drop down menu.
		
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_comments_model = new Transcription_Comments_Model();
		$detail_data_model = new Detail_Data_Model();
		
		// set messages
		$session->set('message_1', 'You requested to back out of Transcription .');
		$session->set('message_class_1', 'alert alert-primary');
		$session->set('message_2', 'Verify line-by-line active');
		$session->set('message_class_2', 'alert alert-warning');
		
		// get all details this header
		$session->transcribe_detail_data = $detail_data_model	
			->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_status', 0)
			->findAll();
			
		// read the details
		if ( ! $session->transcribe_detail_data )
			{
				// none found
				$session->total_lines = 0;
				$session->verified_lines = 0;
			}
		else
			{
				// records found and get the number of verified lines
				$session->total_lines = count($session->transcribe_detail_data);
				$session->transcribe_detail_data_verified = $detail_data_model	
					->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('project_index', $session->current_project[0]['project_index'])
					->where('BMD_status', 0)
					->where('line_verified', 'YES')
					->findAll();
				$session->verified_lines = count($session->transcribe_detail_data_verified);
			}
			
		// if no records found then transcription cannot be complete
		if ( $session->total_lines == 0 OR $session->total_lines != $session->verified_lines)
			{
				$data =	[
							'verified' => 'NO',
						];	
			}
			
		// if counts are the same the transcription cannot be complete
		if ( $session->total_lines == $session->verified_lines )
			{
				$data =	[
							'verified' => 'YES',
						];
			}
		
		// update transcription header
		$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
		
		// return
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );

	}
	
	public function search_synonyms()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching synonym
		$results = $districts_model	->like('District_name', $search_term, 'after')
														->findAll();
		// now read all results to find only those with a volume matching this registration
		// set values in order to find this registration in range
		switch ($session->current_allocation[0]['BMD_type']) 
			{
				case 'B':
					$registration = explode('.', $session->registration);
					$year = $session->current_allocation[0]['BMD_year'];
					$quarter = $session->month_to_quarter[$registration[0]];
					break;
				case 'M':
					$year = $session->current_allocation[0]['BMD_year'];
					$quarter = str_pad($session->current_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
				case 'D':
					$year = $session->current_allocation[0]['BMD_year'];
					$quarter = str_pad($session->current_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
			}
		// find volume range
		foreach ( $results as $result )
			{
				$volumes =  $volumes_model
							->where('district_index', $result['district_index'])
							->where('BMD_type', $session->current_allocation[0]['BMD_type'])
							->findAll();
				if ( $volumes )
					{
						foreach ( $volumes as $volume )
							{	
								if ( $year.$quarter >= $volume['volume_from'] AND $year.$quarter <= $volume['volume_to'])
									{
										$search_result[] = $result['District_name'];
									}
							}
					}
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_districts()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$search_result = array();
		
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		
		// get matching districts
		$results = 	$districts_model
					->like('District_name', $search_term, 'after')
					->where('active', 'YES')
					->findAll();

		foreach($results as $result)
			{
				$search_result[] = $result['District_name'];
			}
			
		// return result
		echo json_encode($search_result);
	}
	
	public function search_volumes()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		$def_ranges_model = new Def_Ranges_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		
		// covert to deimal if volume roman
		if ( array_key_exists(strtoupper($search_term), $session->roman2arabic) )
			{
				$search_term = $session->roman2arabic[strtoupper($search_term)];
			}
			
		// construct the volume_range
		$volume_range = $session->current_allocation[0]['BMD_year'].'0'.$session->current_allocation[0]['BMD_quarter'];
			
		// get matching volumes join to districts master
		$results = $volumes_model	
			->where('volume', $search_term)
			->where('volume_from <=', $volume_range)
			->where('volume_to >=', $volume_range)
			->join('districts_master', 'volumes.district_index = districts_master.district_index')
			->where('BMD_type', $session->current_allocation[0]['BMD_type'])
			->select('District_name')
			->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				if ( array_search($result['District_name'], $search_result) === false )
					{
						$search_result[] = $result['District_name'];
					}
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_firstnames()
	{
		// initialise
		$session = session();
		$firstname_model = new Firstname_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// explode on space to find second/third first names
		$search_array = explode(" ", $search_term);
		// examine array to determine what to search for
		$count = count($search_array);
		switch ($count) 
			{
				case 0:
					$search_string = "";
					break;
				case 1:
					$search_string = $search_array[0];
					break;
				case 2:
					$search_string = $search_array[1];
					break;
				case 3:
					$search_string = $search_array[2];
					break;
				default:
					$search_string = end($search_array);
					break;
			}
		// get matching firstnames but only if input is not blank
		$results = array();
		if ( $search_string != '' )
			{
				$results = $firstname_model		->like('Firstname', $search_string, 'after')
												->orderby('Firstname_popularity', 'DESC')
												->findAll();
			}
		// prepare return array if anything found
		$search_result = array();
		if ( $results )
			{
				foreach($results as $result)
					{
						switch ($count) 
							{
								case 0:
									$search_result[] = "";
									break;
								case 1:
									$search_result[] = $result['Firstname'].' ';
									break;
								case 2:
									$search_result[] = $search_array[0].' '.$result['Firstname'].' ';
									break;
								case 3:
									$search_result[] = $search_array[0].' '.$search_array[1].' '.$result['Firstname'].' ';
									break;
								default:
									break;
							}
					}
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function search_surnames()
	{
		// initialise
		$session = session();
		$surname_model = new Surname_Model();
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching surname
		$results = $surname_model	->like('Surname', $search_term, 'after')
														->orderby('Surname_popularity', 'DESC')
														->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				$search_result[] = $result['Surname'];
			}
		// return result
		echo json_encode($search_result);
	}
	
	public function image_parameters_step1($start_message)
	{
		// initialise
		$session = session();
		//set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Set the vertical image size, scroll_step and image rotation to suit your requirements for this image.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Set these parameters to suit your requirements for this image.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		// show current settings and allow change to them
		$session->set('show_view_type', 'image_parameters');
		$session->set('message_2', 'Current image parameters are shown.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
	}
	
	public function image_parameters_step2($BMD_header_index)
	{
		// initialise
		$session = session();
		$transcription_model = new Transcription_Model();
		
		// get inputs
		$session->set('image_y', $this->request->getPost('image_height'));
		$session->set('scroll_step', $this->request->getPost('image_scroll_step'));
		$session->set('rotation', $this->request->getPost('image_rotate'));

		// do tests
		// height
		if ( $session->image_y == '' OR $session->image_y == '0' OR is_numeric($session->image_y) === false OR  $session->image_y < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image HEIGHT cannot be blank, zero, non_numeric or less than zero.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		// scroll step
		if ( $session->scroll_step == '' OR $session->scroll_step == '0' OR is_numeric($session->scroll_step) === false OR  $session->scroll_step < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image SCROLL STEP cannot be blank, non_numeric or negative');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		// rotate
		if ( $session->rotation == '' OR is_numeric($session->rotation) === false )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image ROTATE cannot be blank, non_numeric. It can be negative for rotate left.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
			
		// all good
		// update header
		$data =	[
					'BMD_image_y' => $session->image_y,
					'BMD_image_x' => '',
					'BMD_image_scroll_step' => $session->scroll_step,
					'BMD_image_rotate' => $session->rotation,
				];
		$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
		
		// reload header 
		$session->current_transcription =	$transcription_model
											->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
											->where('BMD_identity_index', $session->BMD_identity_index)
											->find();
											
		// get image parameters
		$session->set('panzoom_x', $session->current_transcription[0]['BMD_panzoom_x']);
		$session->set('panzoom_y', $session->current_transcription[0]['BMD_panzoom_y']);
		$session->set('panzoom_z', $session->current_transcription[0]['BMD_panzoom_z']);
		$session->set('sharpen', $session->current_transcription[0]['BMD_sharpen']);
		$session->set('image_x', $session->current_transcription[0]['BMD_image_x']);
		$session->set('image_y', $session->current_transcription[0]['BMD_image_y']);
		$session->set('scroll_step', $session->current_transcription[0]['BMD_image_scroll_step']);
		$session->set('rotation', $session->current_transcription[0]['BMD_image_rotate']);
		
		// reset image
		$session->feh_show = 0;
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function enter_parameters_step1($start_message)
	{
		// initialise
		$session = session();
		$def_fields_model = new Def_Fields_Model();
		
		//set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Set the data entry parameters eg. font size, colour etc for the data entry matrix.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Set these parameters to suit your requirements for this data entry.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		// get default entries
		// get the standard def
		$session->set('standard_def', $def_fields_model	
				->where('project_index', $session->current_project[0]['project_index'])
				->where('syndicate_index', $session->current_transcription[0]['BMD_syndicate_index'])
				->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
				->where('scan_format', $session->current_allocation[0]['scan_format'])
				->orderby('field_order','ASC')
				->find());		
		
		// show current settings and allow change to them
		$session->set('show_view_type', 'enter_parameters');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
	}
	
	public function enter_parameters_step2($start_message, $def_key)
	{
		// initialise
		$session = session();
		
		// store current field key
		$session->current_field_key = $def_key;
		$session->current_field_index = $session->current_transcription_def_fields[$session->current_field_key]['field_index'];
		
		switch ($start_message) 
			{
				case 0:
					// set message
					$session->set('message_1', 'Change parameters for field -> '.$session->current_transcription_def_fields[$session->current_field_key]['column_name'].'.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					// set message
					$session->set('message_1', 'Change parameters for field -> '.$session->current_transcription_def_fields[$session->current_field_key]['column_name'].'.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
			}
					
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/transcribe_field_parameters');
		echo view('templates/footer');
	}
	
	public function enter_parameters_step3()
	{
		// initialise
		$session = session();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		
		// get inputs
		$session->set('font_size', $this->request->getPost('font_size'));
		$session->set('font_applytoall', $this->request->getPost('font_applytoall'));
		$session->set('font_weight', $this->request->getPost('font_weight'));
		$session->set('pad_left', $this->request->getPost('pad_left'));
		$session->set('field_align', $this->request->getPost('field_align'));
		$session->set('capitalise', $this->request->getPost('capitalise'));
		$session->set('volume_roman', $this->request->getPost('volume_roman'));
		$session->set('auto_full_stop', $this->request->getPost('auto_full_stop'));
		$session->set('auto_copy', $this->request->getPost('auto_copy'));
		$session->set('auto_focus', $this->request->getPost('auto_focus'));
		$session->set('colour', $this->request->getPost('colour'));
		$session->set('colour_applytoall', $this->request->getPost('colour_applytoall'));
		$session->set('field_format', $this->request->getPost('field_format'));
			
		// test inputs - font size
		if ( $session->font_size <= 0 OR empty($session->font_size) OR ! is_numeric($session->font_size) OR $session->font_size > 4 )
			{
				$session->set('message_2', 'Font size must be numeric, not equal or less than 0, not empty, and not greater than 3. You entered '.$session->font_size);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
		
		// test inputs - font weight
		$test_array = array("normal", "bold");
		if ( ! in_array($session->font_weight, $test_array) )
			{
				$session->set('message_2', 'Font weight must be normal or bold. You entered '.$session->font_weight);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - pad left
		if ( $session->pad_left < 0 OR ! is_numeric($session->pad_left) )
			{
				$session->set('message_2', 'Pad Left must be numeric, not less than 0 and not empty. You entered '.$session->pad_left);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key) );
			}
			
		// test inputs - field align
		$test_array = array("left", "center", "right");
		if ( ! in_array($session->field_align, $test_array) )
			{
				$session->set('message_2', 'Field align must be left, center or right. You entered '.$session->field_align);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
	
		// test inputs - capitalise
		$test_array = array("UPPER", "lower", "First", "none");
		if ( ! in_array($session->capitalise, $test_array) )
			{
				$session->set('message_2', 'Capitalise must be UPPER, lower, First or none. You entered '.$session->capitalise);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - volume roman
		$test_array = array("roman", "none");
		if ( ! in_array($session->volume_roman, $test_array) )
			{
				$session->set('message_2', 'Roman Volume must be roman, or none. You entered '.$session->volume_roman);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
		
		// test inputs - auto full stop
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_full_stop, $test_array) )
			{
				$session->set('message_2', 'Auto Full-stop must be Y, or N. You entered '.$session->auto_full_stop);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - auto copy
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_copy, $test_array) )
			{
				$session->set('message_2', 'Auto copy must be Y, or N. You entered '.$session->auto_copy);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - auto focus
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_focus, $test_array) )
			{
				$session->set('message_2', 'Auto focus must be Y, or N. You entered '.$session->auto_focus);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - field_format
		$test_array = array("text", "number");
		if ( ! in_array($session->field_format, $test_array) )
			{
				$session->set('message_2', 'Field Format must be text, or number. You entered '.$session->field_format);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step2/1/'.$session->current_field_key));
			}				
													
		// tests passed - update parameters for this transcription field
		$data =	[
					'font_size' => $session->font_size,
					'font_weight' => $session->font_weight,
					'pad_left' => $session->pad_left,
					'field_align' => $session->field_align,
					'pad_left' => $session->pad_left,
					'blank_OK' => 'N',
					'capitalise' => $session->capitalise,
					'volume_roman' => $session->volume_roman,
					'auto_full_stop' => $session->auto_full_stop,
					'auto_copy' => $session->auto_copy,
					'auto_focus' => $session->auto_focus,
					'colour' => $session->colour,
					'field_format' => $session->field_format,
				];
		$transcription_detail_def_model->update($session->current_transcription_def_fields[$session->current_field_key]['field_index'], $data);
		
		// test apply to all flags
		if ( $session->colour_applytoall == 'Y' )
			{
				$session->current_transcription_def_fields = $transcription_detail_def_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
						->where('scan_format', $session->current_allocation[0]['scan_format'])
						->orderby('field_order','ASC')
						->findAll();
						
				foreach ( $session->current_transcription_def_fields as $df )
					{

						$data =	[
									'colour' => $session->colour,
								];
						$transcription_detail_def_model->update($df['field_index'], $data);
					}
			}
			
		if ( $session->font_applytoall == 'Y' )
			{
				$session->current_transcription_def_fields = $transcription_detail_def_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
						->where('scan_format', $session->current_allocation[0]['scan_format'])
						->orderby('field_order','ASC')
						->findAll();
						
				foreach ( $session->current_transcription_def_fields as $df )
					{

						$data =	[
									'font_size' => $session->font_size,
								];
						$transcription_detail_def_model->update($df['field_index'], $data);
					}
			}
								
		// reload data entry fields
		$session->current_transcription_def_fields = $transcription_detail_def_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
						->where('scan_format', $session->current_allocation[0]['scan_format'])
						->orderby('field_order','ASC')
						->findAll();
						
		// go round again
		return redirect()->to( base_url('transcribe/enter_parameters_step1/0') );	
	}

	public function default_field_parms_coord_step1($start_message)
	{
		// initialise
		$session = session();

		//set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Set the data entry parameters eg. font size, colour etc for the data entry matrix.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Set these parameters to suit your requirements for this data entry.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}		
		
		// show current settings and allow change to them
		echo view('templates/header');
		echo view('linBMD2/default_field_parms_coords');
		echo view('templates/footer');	
	}
	
	public function default_field_parms_coord_step2($start_message, $def_key)
	{
		// initialise
		$session = session();
		
		// store selected field key
		$session->current_field_key = $def_key;
		
		switch ($start_message) 
			{
				case 0:
					// set message
					$session->set('message_1', 'Set Default Parameters for field -> '.$session->default_field_parms[$def_key]['column_name'].'.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					// set message
					$session->set('message_1', 'Set Default Parameters for field -> '.$session->default_field_parms[$def_key]['column_name'].'.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
			}
					
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/default_field_coords');
		echo view('templates/footer');
	}
	
	public function default_field_parms_coord_step3()
	{
		// initialise
		$session = session();
		$def_fields_model = new Def_Fields_Model();
		
		// get inputs
		$session->set('font_size', $this->request->getPost('font_size'));
		$session->set('font_applytoall', $this->request->getPost('font_applytoall'));
		$session->set('font_weight', $this->request->getPost('font_weight'));
		$session->set('pad_left', $this->request->getPost('pad_left'));
		$session->set('field_align', $this->request->getPost('field_align'));
		$session->set('capitalise', $this->request->getPost('capitalise'));
		$session->set('volume_roman', $this->request->getPost('volume_roman'));
		$session->set('auto_full_stop', $this->request->getPost('auto_full_stop'));
		$session->set('auto_copy', $this->request->getPost('auto_copy'));
		$session->set('auto_focus', $this->request->getPost('auto_focus'));
		$session->set('colour', $this->request->getPost('colour'));
		$session->set('colour_applytoall', $this->request->getPost('colour_applytoall'));
		$session->set('field_format', $this->request->getPost('field_format'));
			
		// test inputs - font size
		if ( $session->font_size <= 0 OR empty($session->font_size) OR ! is_numeric($session->font_size) OR $session->font_size > 4 )
			{
				$session->set('message_2', 'Font size must be numeric, not equal or less than 0, not empty, and not greater than 3. You entered '.$session->font_size);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
		
		// test inputs - font weight
		$test_array = array("normal", "bold");
		if ( ! in_array($session->font_weight, $test_array) )
			{
				$session->set('message_2', 'Font weight must be normal or bold. You entered '.$session->font_weight);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - pad left
		if ( $session->pad_left < 0 OR ! is_numeric($session->pad_left) )
			{
				$session->set('message_2', 'Pad Left must be numeric, not less than 0 and not empty. You entered '.$session->pad_left);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key) );
			}
			
		// test inputs - field align
		$test_array = array("left", "center", "right");
		if ( ! in_array($session->field_align, $test_array) )
			{
				$session->set('message_2', 'Field align must be left, center or right. You entered '.$session->field_align);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
	
		// test inputs - capitalise
		$test_array = array("UPPER", "lower", "First", "none");
		if ( ! in_array($session->capitalise, $test_array) )
			{
				$session->set('message_2', 'Capitalise must be UPPER, lower, First or none. You entered '.$session->capitalise);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - volume roman
		$test_array = array("roman", "none");
		if ( ! in_array($session->volume_roman, $test_array) )
			{
				$session->set('message_2', 'Roman Volume must be roman, or none. You entered '.$session->volume_roman);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
		
		// test inputs - auto full stop
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_full_stop, $test_array) )
			{
				$session->set('message_2', 'Auto Full-stop must be Y, or N. You entered '.$session->auto_full_stop);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - auto copy
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_copy, $test_array) )
			{
				$session->set('message_2', 'Auto copy must be Y, or N. You entered '.$session->auto_copy);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - auto focus
		$test_array = array("Y", "N");
		if ( ! in_array($session->auto_focus, $test_array) )
			{
				$session->set('message_2', 'Auto focus must be Y, or N. You entered '.$session->auto_focus);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}
			
		// test inputs - field_format
		$test_array = array("text", "number");
		if ( ! in_array($session->field_format, $test_array) )
			{
				$session->set('message_2', 'Field Format must be text, or number. You entered '.$session->field_format);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/default_field_parms_coord_step2/1/'.$session->current_field_key));
			}				
													
		// tests passed - update parameters for this transcription field
		$data =	[
					'font_size' => $session->font_size,
					'font_weight' => $session->font_weight,
					'pad_left' => $session->pad_left,
					'field_align' => $session->field_align,
					'pad_left' => $session->pad_left,
					'blank_OK' => 'N',
					'capitalise' => $session->capitalise,
					'volume_roman' => $session->volume_roman,
					'auto_full_stop' => $session->auto_full_stop,
					'auto_copy' => $session->auto_copy,
					'auto_focus' => $session->auto_focus,
					'colour' => $session->colour,
					'field_format' => $session->field_format,
				];
		$def_fields_model->update($session->default_field_parms[$session->current_field_key]['field_index'], $data);
		
		// test apply to all flags
		if ( $session->colour_applytoall == 'Y' )
			{						
				foreach ( $session->default_field_parms as $df )
					{

						$data =	[
									'colour' => $session->colour,
								];
						$def_fields_model->update($df['field_index'], $data);
					}
			}
			
		if ( $session->font_applytoall == 'Y' )
			{						
				foreach ( $session->default_field_parms as $df )
					{

						$data =	[
									'font_size' => $session->font_size,
								];
						$def_fields_model->update($df['field_index'], $data);
					}
			}
								
		// reload data entry fields
		$session->default_field_parms = $def_fields_model
				->where('project_index', $session->current_project[0]['project_index'])
				->where('syndicate_index', $session->reference_synd)
				->where('data_entry_format', $session->reference_data_entry_format)
				->where('scan_format', $session->reference_scan_format)
				->orderby('field_order', 'ASC')
				->find();
						
		// go round again
		return redirect()->to( base_url('transcribe/default_field_parms_coord_step1/0') );	
	}
	
	public function reset_defaults()
	{
		// initialise
		$session = session();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();

		// reset to defaults
		$data =	[
					'font_size' => $session->standard_def[$session->current_field_key]['font_size'],
					'font_weight' => $session->standard_def[$session->current_field_key]['font_weight'],
					'field_align' => $session->standard_def[$session->current_field_key]['field_align'],
					'pad_left' => $session->standard_def[$session->current_field_key]['pad_left'],
					'blank_OK' => $session->standard_def[$session->current_field_key]['blank_OK'],
					'capitalise' => $session->standard_def[$session->current_field_key]['capitalise'],
					'volume_roman' => $session->standard_def[$session->current_field_key]['volume_roman'],
					'auto_full_stop' => $session->standard_def[$session->current_field_key]['auto_full_stop'],
					'auto_copy' => $session->standard_def[$session->current_field_key]['auto_copy'],
					'auto_focus' => $session->standard_def[$session->current_field_key]['auto_focus'],
					'colour' => $session->standard_def[$session->current_field_key]['colour'],
					'field_format' => $session->standard_def[$session->current_field_key]['field_format'],
				];
		$transcription_detail_def_model->update($session->current_transcription_def_fields[$session->current_field_key]['field_index'], $data);		
		
		// reload data entry fields
		$session->current_transcription_def_fields = $transcription_detail_def_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
						->where('scan_format', $session->current_allocation[0]['scan_format'])
						->orderby('field_order','ASC')
						->findAll();
						
		// go round again
		return redirect()->to( base_url($session->controller.'/transcribe_'.$session->controller.'_step1/0') );
	}
	
	public function toogle_line_step1($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		
		// get the line and load fields
		$session->current_line = $detail_data_model
			->where('BMD_index', $line_index)
			->where('BMD_identity_index', $session->BMD_identity_index)
			->find();
			
		// set message depending on status
		if ( $session->current_line[0]['BMD_status'] == 0 )
			{
				$session->action = 'DE-ACTIVATE';
			}
		else
			{
				$session->action = 'RE-ACTIVATE';
			}
		$session->set('message_2', 'You requested to '.$session->action.' this line. Please confirm.');
		$session->set('message_class_2', 'alert alert-danger');
			
		// show view
		echo view('templates/header');
		echo view('linBMD2/toogle_line_confirmation');
		echo view('templates/footer');
	}
	
	public function toogle_line_step2()
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$transcription_model = new Transcription_Model();
		
		// get input
		$session->set('toogle_ok', $this->request->getPost('confirm'));
		
		// if confirmed toogle line status
		if ( $session->toogle_ok == 'Y' )
			{
				// set detail line status and header record counts
				switch ($session->action) 
					{
						case 'DE-ACTIVATE':
							$data_line =	[
												'BMD_status' => '1',
											];
							$data_head =	[
												'BMD_records' => $session->current_transcription[0]['BMD_records'] - 1,
											];
							$do_what = 'sub';
							break;
						case 'RE-ACTIVATE':
							$data_line =	[
												'BMD_status' => '0',
											];
							$data_head =	[
												'BMD_records' => $session->current_transcription[0]['BMD_records'] + 1,
											];
							$do_what = 'add';
							break;
					}
					
				// update the DB
				$detail_data_model->update($session->current_line[0]['BMD_index'], $data_line);
				$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data_head);
				
				// load the header again
				$session->current_transcription = $transcription_model
					->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
					->where('BMD_identity_index', $session->BMD_identity_index)
					->find();
					
				// create reporting data
				$last_detail_line_report = $detail_data_model
					->where('BMD_index', $session->current_line[0]['BMD_index'])
					->find();
				$detail_line = $last_detail_line_report[0];
				load_report_data($detail_line, $do_what);
			}
		
		// return
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function insert_line_step1($line_index)
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		
		// in order to insert a line we need to identify the line sequence to be used.
		// the user selected the line before which the new line should be inserted
		// line sequence increments by 10 for each line added.
		// in order to release spaces between lines we need to resequence all the detail lines just in case the user has inserted many lines before the same sequence.
		// line index stays the same no matter what the sequence is.

		// initialise sequence
		$new_sequence = 0;
		$session->modify_line_sequence == 0;
		
		// get detail lines in sequence order
		$all_detail_lines = $detail_data_model	
			->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->orderby('BMD_line_sequence','ASC')
			->findAll();						
		
		// loop through all detail lines incrementing sequence by 10 each time and update, leave all other data same
		foreach ( $all_detail_lines as $dd )
			{
				$new_sequence = $new_sequence + 10;
				$data =	[
							'BMD_line_sequence' => $new_sequence,
						];
				$detail_data_model->update($dd['BMD_index'], $data);
			}
					
		// load $session->transcribe_detail_data again
		$session->transcribe_detail_data = 	$detail_data_model	
			->where('BMD_header_index',  $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->orderby('BMD_line_sequence','ASC')
			->findAll();
													
		// now that lines are resequenced, I am sure to be able to insert a line before the line selected by the user.
		// get the insert before line using the index of the line selected by the user
		$insert_before_line = $detail_data_model	
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_header_index', $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_index', $line_index)
			->find();
		
		// Since we are inserting a line BEFORE, let's subtract 5 from the line sequence and see if a record exists with this sequence
		//  - it shouldn't since we just requenced but you never know!?!
		$session->insert_line_sequence = $insert_before_line[0]['BMD_line_sequence'] - 5;
		$insert_line =	$detail_data_model	
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_header_index', $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_line_sequence', $session->insert_line_sequence)
			->find();
						
		// does it exist
		if ( $insert_line )
			{
				$session->insert_line_flag = 0;
				$session->set('message_2', 'Sorry, I cannot insert a line. A line already exists with the new sequence. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url($session->controller.'/transcribe_'.$session->controller.'_step1/1') );
			}
			
		// get previous line for surname and first name comparison
		$session->prevEl = $detail_data_model	
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_header_index', $session->current_transcription[0]['BMD_header_index'])
			->where('BMD_line_sequence', $insert_before_line[0]['BMD_line_sequence'] - 10)
			->find();
			
		// line doesn't exist so I can insert it.
		// initialise input fields
		foreach ( $session->current_transcription_def_fields as $field )
			{
				// blank input and dup fields
				$fn = $field['html_name'];
				$dn = $field['dup_fieldname'];
				$session->$fn = '';
				$session->$dn = '';
			}
		$session->insert_line_flag = 1;
		$session->line_edit_flag = 0;
		$session->insert_before_line_sequence = $insert_before_line[0]['BMD_line_sequence'];
		$session->surname = '';
		$session->set('position_cursor', 'surname');
		$session->set('message_2', 'OK, ready to insert a line BEFORE sequence => '.$session->insert_before_line_sequence.'. Position the scan to show the line you want to insert, enter the data as normal and Submit. Your new line will be inserted BEFORE the line you selected');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url($session->controller.'/transcribe_'.$session->controller.'_step1/1') );
	}
	
	public function get_volume()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$volumes_model = new Volumes_Model();
		
		$year = $session->current_allocation[0]['BMD_year'];
		// quarter - depends on value of volume_quarterformat
		$quarter = str_pad($session->current_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
		
		// get data from javascript
		$request_input = json_decode(file_get_contents('php://input'));
        
        // get district record for district entered
		$districts = 	$districts_model
						->where('District_name', $request_input[0])
						->findAll();
							
		// any found?
		if ( ! $districts )
			{
				$volume = '';
				return  json_encode($volume);
			}
			
		// too many found?
		if ( count($districts) > 1 )
			{
				$volume = '';
				return  json_encode($volume);
			}
			
		// get volumes for this district
		$volumes =	$volumes_model
					->where('district_index', $districts[0]['district_index'])
					->where('BMD_type', $session->current_allocation[0]['BMD_type'])
					->where('volume_from <=', $year.$quarter)
					->where('volume_to >=', $year.$quarter)
					->find();
        
		// volume found
		if ( ! $volumes )
			{
				$volume = '';
				return  json_encode($volume);
			}
								
        // is this a roman volume
        if ( $request_input[1] == 'roman' )
			{
				$volume = array_search($volumes[0]['volume'], $session->roman2arabic);
			}
		else
			{
				$volume = $volumes[0]['volume'];
			}

        // return the volume found
     
		return json_encode($volume);
	}
	
	public function toogle_transcriptions()
	{
		// initialise
		$session = session();
		
		// change status
		if ( $session->status == '0' )
			{
				$session->status = '1';
			}
		else
			{
				$session->status = '0';
			}
			
		// set message
		$session->set('message_2', '');
		$session->set('message_class_2', '');
			
		// redirect to transcribe
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );
	}
	
	public function calibrate_step1($start_message)
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$detail_data_model = new Detail_Data_Model();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		$def_fields_model = new Def_Fields_Model();
	
		// initialise messages
		switch ($start_message) 
			{
				case 0:
					$session->set('message_class_1', 'alert alert-primary');
					
					// select calibrate stage
					switch ($session->calibrate) 
						{
							case 0:
								$session->set('message_1', 'Calibrate Stage 1 of 3 - Image Parameters - Rotation, Zoom and Image Position');
								$session->set('panzoom_x', $session->current_transcription[0]['BMD_panzoom_x']);
								$session->set('panzoom_y', $session->current_transcription[0]['BMD_panzoom_y']);
								$session->set('panzoom_z', $session->current_transcription[0]['BMD_panzoom_z']);
								$session->set('zoom_lock', $session->current_transcription[0]['zoom_lock']);
								$session->set('rotation', $session->current_transcription[0]['BMD_image_rotate']);
								$session->set('panzoom_s', $session->current_transcription[0]['BMD_image_scroll_step']);
								$session->set('sharpen', $session->current_transcription[0]['BMD_sharpen']);

								// save image height
								$session->set('save_image_y', $session->current_transcription[0]['BMD_image_y']);
								// image height at start needs to be high in order to see enough lines for calibration
								$session->image_y = 350;
								$session->panzoom_l = 10;
								$session->height_l = 3;
								break;
							case 1:
								$session->set('message_1', 'Calibrate Stage 2 of 3 - Image Parameters - Scroll Step, Height');
								break;
							case 2:
								$session->set('message_1', 'Calibrate Stage 3 of 3 - Data Entry Parameters - Fields');
								$session->current_transcription_def_fields =	
									$transcription_detail_def_model
									->where('project_index', $session->current_project[0]['project_index'])
									->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
									//->where('scan_format', $session->current_allocation[0]['scan_format'])
									->orderby('field_order','ASC')
									->findAll();
								break;
						}
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Calibrate - Image Parameters');
					break;
				default:
			}									
		
		// show views																
		echo view('templates/header');
		echo view('linBMD2/calibrate_step1');
		echo view('linBMD2/transcribe_panzoom');
		if ( $session->calibrate > 0)
			{
				echo view('linBMD2/transcribe_dragable');
				echo view('linBMD2/transcribe_ruler');
			}
		echo view('templates/footer');	
	}
	
	public function calibrate_step2()
	{
		// initialise method
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		$identity_model = new Identity_Model();
		$def_fields_model = new Def_Fields_Model();
		$def_image_model = new Def_Image_Model();
		$detail_data_model = new Detail_Data_Model();
		
		// get and update data entry per stage
		switch ($session->calibrate) 
			{
				case 0:
					$session->set('rotation', $this->request->getPost('rotation'));
					$session->set('panzoom_x', $this->request->getPost('panzoom_x'));
					$session->set('panzoom_y', $this->request->getPost('panzoom_y'));
					$session->set('panzoom_z', $this->request->getPost('panzoom_z'));
					$session->set('zoom_lock', $this->request->getPost('zoom_lock'));
					
					$data =	[
								'BMD_image_rotate' => $session->rotation,
								'BMD_panzoom_x' => $session->panzoom_x,
								'BMD_panzoom_y' => $session->panzoom_y,
								'BMD_panzoom_z' => $session->panzoom_z,
								'zoom_lock' => $session->zoom_lock,
							];
					$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);
					break;
				case 1:
					// get inputs
					$session->set('panzoom_s', $this->request->getPost('panzoom_s'));
					$session->set('image_y', $this->request->getPost('image_y'));
					
					// check that the height was calculated
					if ( $session->image_y == 350 )
						{
							// set to original height
							$session->set('image_y', $session->save_image_y);
							$session->set('message_2', 'Image height not calculated in previous stage. It has been restored to original setting.');
							$session->set('message_class_2', 'alert alert-info');
						}
					
					// update transcription
					$data =	[
								'BMD_image_y' => $session->image_y,
								'BMD_image_scroll_step' => $session->panzoom_s,
							];
					$transcription_model->update($session->current_transcription[0]['BMD_header_index'], $data);	 
					break;
				case 2:
					// get inputs
					foreach ($session->current_transcription_def_fields as $td) 
						{
							// get the value
							$width = $this->request->getPost($td['html_name']);
						
							// if 
							//update file
							$data =	[
										'column_width' => $width,
									];
							$transcription_detail_def_model->update($td['field_index'], $data);
						}
						
					$session->stop_calibrate = 'stop';
					break;
			}
			
		// reload current transcription
		$BMD_header_index = $session->current_transcription[0]['BMD_header_index'];
		$session->current_transcription =	$transcription_model
			->where('BMD_header_index',  $BMD_header_index)
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->find();
											
		// reload current_transcription_def_fields
		$session->current_transcription_def_fields = $transcription_detail_def_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('transcription_index', $BMD_header_index)
			->where('scan_format', $session->current_allocation[0]['scan_format'])
			->orderby('field_order','ASC')
			->findAll();
			
		// set next stage
		$session->calibrate = $session->calibrate + 1;
			
		// go to next stage or back to transcription home or verify or transcribe
		if ( $session->stop_calibrate == 'stop' )
			{
				// set message
				$session->set('message_2', 'Image parameters have been re-calibrated.');
				$session->set('message_class_2', 'alert alert-success');
				
				// where was calibrate called from?
				if ( $session->verifytranscribe_calibrate == 'N' )
					{
						// from the menu
						return redirect()->to( base_url('transcribe/transcribe_step1/2') );
					}
				else
					{
						// from Verify or Transcribe
						switch ($session->last_cycle_code) 
							{
								// called from Verify
								case 'VERIT':
									$session->verifytranscribe_calibrate = 'N';
									$session->BMD_cycle_code = $session->last_cycle_code;
									return redirect()->to(base_url('transcribe/verify_step1/'.$BMD_header_index));
									break;
								// called from transcribe
								case 'INPRO':
									$session->verifytranscribe_calibrate = 'N';
									$session->BMD_cycle_code = 'INPRO';
									switch ($session->current_allocation[0]['BMD_type']) 
										{
											case 'B': // = Births in FreeBMD
												return redirect()->to( base_url('births/transcribe_births_step1/0') );
												break;
											case 'M': // = Marriages in FreeBMD
												return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
												break;
											case 'D': // = Deaths in FreeBMD
												return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
												break;
												// cases for types in other projects, FreeREG, FreeCEN
											default:
												break;
										}
									break;									
							}
					}	
			}
		else
			{
				return redirect()->to( base_url('transcribe/calibrate_step1/0') );
			}
	}
	
	public function sort($by)
	{
		// initialise method
		$session = session();
		
		// set sort by
		switch ($by) 
			{
				case 1:
					$session->sort_by = 'syndicate.BMD_syndicate_name';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Syndicate Name';
					break;
				case 2:
					$session->sort_by = 'allocation.BMD_allocation_name';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Allocation Name';
					break;
				case 3:
					$session->sort_by = 'transcription.BMD_file_name';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Transcription';
					break;
				case 4:
					$session->sort_by = 'transcription.BMD_scan_name';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Scan name';
					break;
				case 5:
					$session->sort_by = 'transcription.BMD_records';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Records transcribed';
					break;
				case 6:
					$session->sort_by = 'transcription.BMD_start_date';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Start date';
					break;
				case 7:
					$session->sort_by = 'transcription.Change_date';
					$session->sort_order = 'DESC';
					$session->sort_name = 'Last change date/time';
					break;
				case 8:
					$session->sort_by = 'transcription.BMD_submit_date';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Upload date';
					break;
				case 9:
					$session->sort_by = 'transcription.BMD_submit_status';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Upload status';
					break;
				case 10:
					$session->sort_by = 'transcription.BMD_last_action';
					$session->sort_order = 'ASC';
					$session->sort_name = 'Last action performed';
					break;
				default:
					$session->sort_by = 'transcription.Change_date';
					$session->sort_order = 'DESC';
					$session->sort_name = 'Last Change Date Time';
			}
				
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );
	}
	
	public function message_to_coord_step1($start_message)
	{
		// The function added - issue 172 / 142 in issue tracker
		// initialise
		$session = session();
		
		// set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'You are sending a message to '.$session->current_syndicate[0]['BMD_syndicate_leader'].' at '.$session->current_syndicate[0]['BMD_syndicate_name']);
					$session->set('message_class_1', 'alert alert-primary');
					
					switch ($session->BMD_cycle_code) 
						{
							case 'INPRO':
								$session->subject1 = 'Email from '.$session->realname.', currently transcribing '.$session->current_transcription[0]['BMD_file_name'];
								break;
							case 'VERIT':
								$session->subject1 = 'Email from '.$session->realname.', currently verifying '.$session->current_transcription[0]['BMD_file_name'];
								break;
						}
					$session->subject2 = '';
					$session->body = '';
					break;
				case 1:
					break;
				case 2:
					switch ($session->BMD_cycle_code) 
						{
							case 'INPRO':
								$session->subject1 = 'Email from '.$session->realname.', currently transcribing '.$session->current_transcription[0]['BMD_file_name'];
								break;
							case 'VERIT':
								$session->subject1 = 'Email from '.$session->realname.', currently verifying '.$session->current_transcription[0]['BMD_file_name'];
								break;
						}
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		// show view
		echo view('templates/header');
		echo view('linBMD2/message_to_coord');
		echo view('templates/footer');	
	}
	
	public function message_to_coord_step2()
	{
		// initialise
		$session = session();
		
		// get inputs
		$session->set('subject2', $this->request->getPost('subject2'));
		$session->set('myfile', basename($_FILES["myfile"]["name"]));
		$session->set('body', $this->request->getPost('body'));
		$session->body = "<pre>" . $session->body . "</pre>";
		
		// test inputs
		// subject2
		if ( $session->subject2 == '' )
			{
				$session->set('message_2', 'You must enter a subject.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/message_to_coord_step1/1'));
			}
			
		// test file size
		if ( $_FILES["myfile"]["size"] > 2000000) 
			{
				$session->set('message_2', 'Sorry, the file you have chosen is too large to attach to your email.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/message_to_coord_step1/1'));
			} 
			
		// if file was selected
		if ( $session->myfile != '' )
			{
				// delete it if it exists
				$session->cfile = getcwd().'/tmp/'.$session->myfile;
				if ( file_exists($session->cfile) )
					{
						unlink($session->cfile);
					}
			
				// and upload the file to tmp directory
				if ( ! move_uploaded_file($_FILES["myfile"]["tmp_name"], $session->cfile)) 
					{
						$session->set('message_2', 'Sorry, I cannot attach your file to your email. Your email has not been sent.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('transcribe/message_to_coord_step1/1'));
					}
			}
				
		// send email
		return redirect()->to( base_url('email/send_email/transcribe') );	
	}
	
	public function inherit_parameters()
	{
		// initialise
		$session = session();
		$transcription_model = new Transcription_Model();
		$transcription_detail_def_model = new Transcription_Detail_Def_Model();
		
		// get all transcriptions, this project, this user, this syndicate, this allocation and ! this page 'id!='
		$transcriptions = $transcription_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_identity_index', $session->current_transcription[0]['BMD_identity_index'])	
			->where('BMD_syndicate_index', $session->current_transcription[0]['BMD_syndicate_index'])
			->where('BMD_allocation_index', $session->current_transcription[0]['BMD_allocation_index'])
			->where('BMD_current_page!=', $session->current_transcription[0]['BMD_current_page'])
			->orderby('BMD_current_page')
			->findAll();
									
		// any found
		if ( ! $transcriptions )
			{
				$session->set('message_2', 'Sorry, I cannot inherit parameters because I have not found a previous transcription in the same project, same user, same syndicate, same allocation.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/enter_parameters_step1/1'));
			}
		
		// OK I have transcriptions - get the last one to find the transcription index
		$last_transcription = end($transcriptions);
		
		// now read the definitions from currrent and update with last
		foreach ( $session->current_transcription_def_fields as $def )
			{
				// get this field from last_transcription_def - this should only produce one record
				$last_field_def	= $transcription_detail_def_model
					->where('project_index', $last_transcription['project_index'])
					->where('transcription_index', $last_transcription['BMD_header_index'])
					->where('table_fieldname', $def['table_fieldname'])
					->findAll();
				
				// if last def found and only one element is returned
				if ( count($last_field_def) == 1 )
					{
						// set the transcription parameters for the current transcription
						$transcription_detail_def_model
						->set(['font_size' => $last_field_def[0]['font_size']])
						->set(['font_weight' => $last_field_def[0]['font_weight']])
						->set(['field_align' => $last_field_def[0]['field_align']])
						->set(['capitalise' => $last_field_def[0]['capitalise']])
						->set(['volume_roman' => $last_field_def[0]['volume_roman']])
						->set(['auto_full_stop' => $last_field_def[0]['auto_full_stop']])
						->set(['auto_copy' => $last_field_def[0]['auto_copy']])
						->set(['auto_focus' => $last_field_def[0]['auto_focus']])
						->set(['colour' => $last_field_def[0]['colour']])
						->update($def['field_index']);
					}
			}
			
		// reload defs for current transcription
		$session->current_transcription_def_fields = $transcription_detail_def_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('transcription_index', $session->current_transcription[0]['BMD_header_index'])
			->where('scan_format', $session->current_allocation[0]['scan_format'])
			->orderby('field_order','ASC')
			->findAll();
						
		// return
		$session->set('message_2', 'Transcription parameters for this transcription have been inherited from last transcription.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('transcribe/enter_parameters_step1/1'));
	}
	
	public function update_last_action($BMD_header_index)
		{
			$session = session();
			$transcription_model = new Transcription_Model();
			$transcription_cycle_model = new Transcription_Cycle_Model();
			
			// get the cycle text
			$session->set('BMD_cycle_text',	$transcription_cycle_model	
				->where('BMD_cycle_code', $session->BMD_cycle_code)
				->where('BMD_cycle_type', 'TRANS')
				->find());
			
			// update last action
			$data =	[
						'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
					];
			$transcription_model->update($BMD_header_index, $data);
		}

	public function setup_image_and_parameters()
		{
			// initialise
			$session = session();
			$identity_last_indexes_model = new Identity_Last_Indexes_Model();
			
			// set image parameters
			$session->set('sharpen', $session->current_transcription[0]['BMD_sharpen']);
			$session->set('scroll_step', $session->current_transcription[0]['BMD_image_scroll_step']);
			$session->set('image_y', $session->current_transcription[0]['BMD_image_y']);
			$session->set('image_x', $session->current_transcription[0]['BMD_image_x']);
			$session->set('rotation', $session->current_transcription[0]['BMD_image_rotate']);
			$session->set('image', $session->current_transcription[0]['BMD_scan_name']);
			
			// only process the image if image scan has changed
			if ( $session->image_processed != $session->image )
				{
					// set creds
					// now need to set creds depending on whether a coordinator is masquerading and one of his transcribers
					if ( $session->masquerade == 1 )
						{
							// masquerade is on, so use coordinator creds 
							$user = rawurlencode($session->coordinator_identity_userid);
							$mdp = rawurlencode($session->coordinator_identity_password);
						}
					else
						{
							// masquerade is off, so use transcriber creds 
							$user = rawurlencode($session->identity_userid);
							$mdp = rawurlencode($session->identity_password);
						}
					
					// initialse image			
					$url = 	$session->current_project[0]['project_autoimageservertype']
							.$user
							.':'
							.$mdp
							.'@'
							.$session->current_project[0]['project_autoimageurl']
							.$session->current_allocation[0]['BMD_reference']
							.$session->current_transcription[0]['BMD_scan_name'];						
					$session->url = $url;
						
					// get image info to get mime type
					$imageInfo = getimagesize($url);
					
					// get image size
					$session->x_size = $imageInfo[0];
					$session->y_size = $imageInfo[1];
					
					// get mime type
					$session->mime_type = $imageInfo['mime'];
					
					// encode to base 64
					$session->fileEncode = base64_encode(file_get_contents($url));
					
					// set the image processed flag
					$session->image_processed = $session->current_transcription[0]['BMD_scan_name'];
				}
			
			// get current font parameters
			$session->set('enter_font_family', $session->current_transcription[0]['BMD_font_family']);
					
			// set controller
			switch ($session->current_allocation[0]['BMD_type']) 
				{
					case 'B':
						$session->controller = 'births';
						break;
					case 'M':
						$session->controller = 'marriages';
						break;
					case 'D':
						$session->controller = 'deaths';
						break;
				}
				
			// set the identity last indexes by data entry format
			$last_indexes = $identity_last_indexes_model
				->where('identity_index', $session->BMD_identity_index)
				->where('project_index', $session->current_project[0]['project_index'])
				->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
				->find();
			
			// record found
			if ( $last_indexes )
				{
					// record found, so update
					$identity_last_indexes_model
						->where('identity_index', $session->BMD_identity_index)
						->where('project_index', $session->current_project[0]['project_index'])
						->where('data_entry_format', $session->current_allocation[0]['data_entry_format'])
						->set(['transcription_index' => $session->current_transcription[0]['BMD_header_index']])
						->set(['allocation_index' => $session->current_transcription[0]['BMD_allocation_index']])
						->set(['syndicate_index' => $session->current_transcription[0]['BMD_syndicate_index']])
						->update();
				}
			else
				{
					// record not found, so insert
					$identity_last_indexes_model
						->set(['identity_index' => $session->BMD_identity_index])
						->set(['project_index' => $session->current_project[0]['project_index']])
						->set(['data_entry_format' => $session->current_allocation[0]['data_entry_format']])
						->set(['transcription_index' => $session->current_transcription[0]['BMD_header_index']])
						->set(['allocation_index' => $session->current_transcription[0]['BMD_allocation_index']])
						->set(['syndicate_index' => $session->current_transcription[0]['BMD_syndicate_index']])
						->insert();
				}
		}

		public function calibrate_reference_step0($start_message)
		{
			// initialise
			$session = session();
			if ( $start_message == 0 )
				{
					$session->reference_synd = '';
					$session->reference_type = '';
					$session->set('message_1', 'Create default transcription set - Please enter the information requested.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
				}
				
			// show views																
			echo view('templates/header');
			echo view('linBMD2/calibrate_reference_step0');
			echo view('templates/footer');	
		}
	
	public function calibrate_reference_step1($start_message)
		{
			// initialise
			$session = session();
			$def_ranges_model = new Def_Ranges_Model();
			$def_image_model = new Def_Image_Model();	
			
			// set message
			if ( $start_message == 0 )
				{
					// get inputs
					$session->reference_synd = $this->request->getPost('reference_synd');
					$session->reference_type = $this->request->getPost('reference_type');
				
					// test inputs
					if ( $session->reference_synd == '' 
							OR $session->reference_type == 'S' )
						{
							$session->set('message_2', 'Please select from drop down lists.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/calibrate_reference_step0/1') );
						}
					
					// a DBADMIN can change any syndicates.
					// a COORD can only change his own.
					if ( $session->current_identity[0]['role_index'] == 2 )
						{
							if ( $session->reference_synd != $session->saved_syndicate_index )
								{
									$session->set('message_2', 'Please select your own syndicate');
									$session->set('message_class_2', 'alert alert-danger');
									return redirect()->to( base_url('transcribe/calibrate_reference_step0/1') );
								}
						}
						
					// get the name of the syndicate selected
					foreach ( $session->syndicates as $syndicate )
						{
							$syndicates_array_index = array_search($session->reference_synd, $syndicate);
							if ( $syndicates_array_index !== false )
								{
									$session->reference_synd_name = $syndicate['BMD_syndicate_name'];
									break;
								}
						}
						
					// get type name selected
					foreach ( $session->project_types as $project_type )
						{
							$types_array_index = array_search($session->reference_type, $project_type);
							if ( $types_array_index !== false )
								{
									$session->reference_type_name = $project_type['type_name_lower'];
									break;
								}
						}
					
					// get default transcription sets for this type
					$session->transcription_sets = $def_ranges_model
						->join('def_image', 'def_ranges.data_entry_format = def_image.data_entry_format')
						->where('def_ranges.project_index', $session->current_project[0]['project_index'])
						->where('def_ranges.type', $session->reference_type)
						->where('def_image.syndicate_index', $session->reference_synd)
						->find();
			
					$session->set('message_1', 'Default Transcription Sets are shown below. Reference scan and path show that you have previously calibrated this Transcription Set.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
				}
			
			// show views																
			echo view('templates/header');
			echo view('linBMD2/calibrate_reference_step1');
			echo view('linBMD2/sortTableNew');
			echo view('linBMD2/searchTableNew');
			echo view('templates/footer');	
		}
		
	public function calibrate_reference_step2()
		{
			// initialise
			$session = session();
			$def_ranges_model = new Def_Ranges_Model();
			$project_types_model = new Project_Types_Model();
			$def_fields_model = new Def_Fields_Model();
			$def_image_model = new Def_Image_Model();
	
			// get the input
			$session->reference_image_index = $this->request->getPost('reference_image_index');
			$session->reference_data_entry_format = $this->request->getPost('reference_data_entry_format');
			$session->reference_scan_format = $this->request->getPost('reference_scan_format');
			$session->reference_x_start = $this->request->getPost('reference_x_start');
			$session->reference_y_start = $this->request->getPost('reference_y_start');
			$session->reference_z_start = $this->request->getPost('reference_z_start');
			$session->reference_scan = $this->request->getPost('reference_scan');
			$session->reference_path = $this->request->getPost('reference_path');
			$session->base_on = $this->request->getPost('base_on');

			// do reference scan and reference path exist on selected base_on index?
			if ( $session->base_on != '' )
				{
					// find base_on index in array
					foreach ( $session->transcription_sets as $key => $transcription_set )
						{
							$base_on_index = array_search($session->base_on, $transcription_set);
							if ( $base_on_index !== false )
								{
									$session->reference_scan = $transcription_set['reference_scan'];
									$session->reference_path = $transcription_set['reference_path'];
									$session->base_on_data_entry_format = $transcription_set['data_entry_format'];
									$session->base_on_scan_format = $transcription_set['scan_format'];
									break;
								}
						}
				}	

			// test inputs
			if ( 	$session->reference_x_start == '' 
					OR 
					$session->reference_y_start == ''
					OR 
					$session->reference_z_start == '')
				{
					$session->set('message_2', 'X start, Y start and Zoom cannot be blank for index: '.$session->reference_image_index);
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
				}
			
			if ( 	$session->reference_scan == '' 
					OR 
					$session->reference_path == '')
				{
					$session->set('message_2', 'Please enter missing data for Index '.$session->reference_image_index.' in order to continue Calibration OR the based on index has not been calibrated.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
				}
	
			// set message 2		
			$session->set('message_2', '');
			$session->set('message_class_2', '');
						
			// strip / from first and last position - just in case user has entered this
			$reference_path = trim($session->reference_path, "/");
				
			// does the scan exist on the project image server?
			$curl_url =	$session->current_project[0]['project_autoimageservertype']
						.$session->current_project[0]['project_autoimageurl']
						.$session->reference_path.'/'
						.$session->reference_scan;
		
			// set up the curl
			$ch = curl_init($curl_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, rawurlencode($session->identity_userid).':'.rawurlencode($session->identity_password));
		
			// do the curl
			$curl_result = curl_exec($ch);
			curl_close($ch);	
	
			// anything found
			if ( $curl_result == '' )
				{
					// problem so send error message
					$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch scan in Transcribe::calibrate_reference_step2 => '.$curl_url);
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
				}
			
			// load returned data to array to check for not found
			$lines = preg_split("/\r\n|\n|\r/", $curl_result);
					
			// now test to see if a valid scan was found
			foreach($lines as $line)
				{
					if ( strpos($line, "404 Not Found") !== false )
						{
							$session->set('message_2', 'I cannot find the scan you requested on the image server for Index '.$session->reference_image_index.'. Please check your entries. '.$curl_url.' => Does not exist.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
						}
				}
			
			// Ok I now have a valid scan so load it

			// initialse image			
			$url = 	$session->current_project[0]['project_autoimageservertype']
					.rawurlencode($session->identity_userid)
					.':'
					.rawurlencode($session->identity_password)
					.'@'
					.$session->current_project[0]['project_autoimageurl']
					.$session->reference_path.'/'
					.$session->reference_scan;
					
			// set up the image
			$session->url = $url;
			$imageInfo = getimagesize($url);
			$session->mime_type = $imageInfo['mime'];
			$session->fileEncode = base64_encode(file_get_contents($url));
			$x_size = $imageInfo[0];
			$y_size = $imageInfo[1];

			// get default transcription set image parms for this syndicate
			if ( $session->base_on != '' )
				{
					$default_image_parms = $def_image_model
						->where('image_index', $session->base_on)
						->find();
				}
			else
				{
					$default_image_parms = $def_image_model
						->where('image_index', $session->reference_image_index)
						->find();
				}
		
			// if no image default found for this syndicate use null setup defaults
			if ( ! $default_image_parms )
				{
					// get null syndicate records
					$default_image_parms = $def_image_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', null)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->find();
					
					// create the record for this syndicate based on null syndicate records
					$def_image_model
						->set(['project_index' => $session->current_project[0]['project_index']])
						->set(['syndicate_index' => $session->reference_synd])
						->set(['data_entry_format' => $session->def_format])
						->set(['scan_format' => $session->reference_format])
						->set(['image_x' => $default_image_parms[0]['image_x']])
						->set(['image_y' => $default_image_parms[0]['image_y']])
						->set(['image_rotate' => $default_image_parms[0]['image_rotate']])
						->set(['image_scroll_step' => $default_image_parms[0]['image_scroll_step']])
						->set(['panzoom_x' => $default_image_parms[0]['panzoom_x']])
						->set(['panzoom_y' => $default_image_parms[0]['panzoom_y']])
						->set(['panzoom_z' => $default_image_parms[0]['panzoom_z']])
						->set(['sharpen' => $default_image_parms[0]['sharpen']])
						->set(['zoom_lock' => $default_image_parms[0]['zoom_lock']])
						->set(['reference_scan' => ''])
						->set(['reference_path' => ''])
						->insert();
						
					// now get the image parms for this syndicate again
					$default_image_parms = $def_image_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->find();
					
					// if still not found, give up
					if ( ! $default_image_parms )
						{
							$session->set('message_2', 'I cannot find the image defaults for this reference scan for Index '.$session->reference_image_index.'. Please check your entries.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
						}
				}
			else
				{
					// if found then set panzoom x and y from x and y start
					$default_image_parms[0]['panzoom_x'] = $session->reference_x_start;
					$default_image_parms[0]['panzoom_y'] = $session->reference_y_start;
					$default_image_parms[0]['panzoom_z'] = $session->reference_z_start;
					$session->x_min = $x_size * -1;
					$session->x_max = $x_size;
					$session->y_min = $y_size * -1;
					$session->y_max = $y_size;
				}
			
			// set session
			$session->default_image_parms = $default_image_parms;		
										
			// get default transcription set field parms
			if ( $session->base_on != '' )
				{
					$session->default_field_parms = $def_fields_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->base_on_data_entry_format)
						->where('scan_format', $session->base_on_scan_format)
						->orderby('field_order', 'ASC')
						->find();
				}
			else
				{
						$session->default_field_parms = $def_fields_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->orderby('field_order', 'ASC')
						->find();
				}
			
			// if no field default found for this syndicate use setup defaults
			if ( ! $session->default_field_parms )
				{
					// get null syndicate records
					$session->default_field_parms = $def_fields_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', null)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->find();
						
					// create the records for this syndicate based on null syndicate records
					foreach ( $session->default_field_parms as $field_parms )
						{
							$def_fields_model
								->set(['project_index' => $session->current_project[0]['project_index']])
								->set(['syndicate_index' => $session->reference_synd])
								->set(['data_entry_format' => $session->def_format])
								->set(['scan_format' => $session->reference_format])
								->set(['field_order' => $field_parms['field_order']])
								->set(['field_name' => $field_parms['field_name']])
								->set(['column_name' => $field_parms['column_name']])
								->set(['column_width' => $field_parms['column_width']])
								->set(['font_size' => $field_parms['font_size']])
								->set(['font_weight' => $field_parms['font_weight']])
								->set(['field_align' => $field_parms['field_align']])
								->set(['pad_left' => $field_parms['pad_left']])
								->set(['html_name' => $field_parms['html_name']])
								->set(['html_id' => $field_parms['html_id']])
								->set(['field_type' => $field_parms['field_type']])
								->set(['blank_OK' => $field_parms['blank_OK']])
								->set(['date_format' => $field_parms['date_format']])
								->set(['volume_quarterformat' => $field_parms['volume_quarterformat']])
								->set(['volume_roman' => $field_parms['volume_roman']])
								->set(['table_fieldname' => $field_parms['table_fieldname']])
								->set(['capitalise' => $field_parms['capitalise']])
								->set(['dup_fieldname' => $field_parms['dup_fieldname']])
								->set(['dup_fromfieldname' => $field_parms['dup_fromfieldname']])
								->set(['special_test' => $field_parms['special_test']])
								->set(['virtual_keyboard' => $field_parms['virtual_keyboard']])
								->set(['input_first_line' => $field_parms['input_first_line']])
								->set(['js_event' => $field_parms['js_event']])
								->set(['js_function' => $field_parms['js_function']])
								->set(['auto_full_stop' => $field_parms['auto_full_stop']])
								->set(['auto_copy' => $field_parms['auto_copy']])
								->set(['auto_focus' => $field_parms['auto_focus']])
								->set(['colour' => $field_parms['colour']])
								->set(['field_format' => $field_parms['field_format']])
								->insert();
						}
						
					// now get default transcription set field parms again
					$session->default_field_parms = $def_fields_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->find();
						
					// if still not found, give up
					if ( ! $session->default_field_parms )
						{
							$session->set('message_2', 'I cannot find the field defaults for this reference scan for Index '.$session->reference_image_index.'. Please check your entries.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/calibrate_reference_step1/1') );
						}
				}		
		
			// get the name of the syndicate selected
			foreach ( $session->syndicates as $syndicate )
				{
					$syndicates_array_index = array_search($session->reference_synd, $syndicate);
					if ( $syndicates_array_index !== false )
						{
							$session->reference_synd_name = $syndicate['BMD_syndicate_name'];
							break;
						}
				}
				
			// has this transcription set been calibrated already		
			if ( $session->default_image_parms[0]['reference_scan'] != '' AND $session->base_on == '' )
				{
					$session->set('message_2', 'ATTENTION : You have already calibrated this Reference Set with '.$session->default_image_parms[0]['reference_scan'].', '.$session->default_image_parms[0]['reference_path'].'. You can continue to calibrate it again but be aware that you will lose any previous settings.');
					$session->set('message_class_2', 'alert alert-info');
				}
			else
				{
					$session->set('message_2', '');
					$session->set('message_class_2', '');
				}
			
			// do the calibration
			$session->BMD_cycle_code = 'INPRO';
			$session->calibrate = 0;
			$session->stop_calibrate = '';
			return redirect()->to( base_url('transcribe/calibrate_coord_step1/0') );
		}
	
	public function calibrate_coord_step1($start_message, $back='')
	{
		// initialise method
		$session = session();
		$def_fields_model = new Def_Fields_Model();
		$def_image_model = new Def_Image_Model();	

		// initialise messages
		switch ($start_message) 
			{
				case 0:
					$session->set('message_class_1', 'alert alert-primary');
					
					// if back == back, reduce calibrate stage by 1
					if ( $back == 'back' )
						{
							$session->calibrate = $session->calibrate -1;
						}
					
					// select calibrate stage
					switch ($session->calibrate) 
						{
							case 0:
								$session->set('message_1', 'Calibrate Stage 1 of 3 - Image Parameters - Rotation, Zoom and Image Position');
								$session->set('panzoom_x', $session->default_image_parms[0]['panzoom_x']);
								$session->set('panzoom_y', $session->default_image_parms[0]['panzoom_y']);
								$session->set('panzoom_z', $session->default_image_parms[0]['panzoom_z']);
								$session->set('zoom_lock', $session->default_image_parms[0]['zoom_lock']);
								$session->set('rotation', $session->default_image_parms[0]['image_rotate']);
								$session->set('panzoom_s', $session->default_image_parms[0]['image_scroll_step']);
								$session->set('sharpen', $session->default_image_parms[0]['sharpen']);
								// save image height
								$session->set('save_image_y', $session->default_image_parms[0]['image_y']);
								// set image height for display and in order to allow scroll step calculation
								$session->image_y = 350;
								break;
							case 1:
								$session->set('message_1', 'Calibrate Stage 2 of 3 - Image Parameters - Scroll Step, Height');
								$session->set('message_2', '');
								// save image height
								$session->set('save_image_y', $session->default_image_parms[0]['image_y']);
								// set image height for display and in order to allow scroll step calculation
								$session->image_y = 350;
								// set number of lines to display on transcribe and verify screens and number of lines to use in scroll step calculation
								$session->panzoom_l = 10;
								$session->height_l = 3;
								break;
							case 2:
								$session->set('message_1', 'Calibrate Stage 3 of 3 - Data Entry Parameters - Fields');
								$session->set('message_2', '');
								break;
						}
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Calibrate - Image Parameters');
					break;
				default:
			}									
		
		// show views																
		echo view('templates/header');
		echo view('linBMD2/calibrate_coords_step1');
		echo view('linBMD2/transcribe_panzoom');
		if ( $session->calibrate == 1)
			{
				echo view('linBMD2/transcribe_ruler');
			}
		if ( $session->calibrate == 2)
			{
				echo view('linBMD2/transcribe_dragable');
				echo view('linBMD2/transcribe_ruler');
			}
		echo view('templates/footer');	
	}
	
	public function calibrate_coord_step2()
	{
		// initialise method
		$session = session();
		$def_fields_model = new Def_Fields_Model();
		$def_image_model = new Def_Image_Model();
		
		// get and update data entry per stage
		switch ($session->calibrate) 
			{
				case 0:
					// get inputs
					$session->set('rotation', $this->request->getPost('rotation'));
					$session->set('panzoom_x', $this->request->getPost('panzoom_x'));
					$session->set('panzoom_y', $this->request->getPost('panzoom_y'));
					$session->set('panzoom_z', $this->request->getPost('panzoom_z'));
					$session->set('zoom_lock', $this->request->getPost('zoom_lock'));				
					
					// update default image set
					$def_image_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->set(['image_rotate' => $session->rotation])
						->set(['panzoom_x' => $session->panzoom_x])
						->set(['panzoom_y' => $session->panzoom_y])
						->set(['panzoom_z' => $session->panzoom_z])
						->set(['zoom_lock' => $session->zoom_lock])
						->set(['reference_scan' => $session->reference_scan])
						->set(['reference_path' => $session->reference_path])
						->update();
					break;
				case 1:
					// get inputs
					$session->set('panzoom_s', $this->request->getPost('panzoom_s'));
					$session->set('image_y', $this->request->getPost('image_y'));
					
					// check that the height was calculated
					if ( $session->image_y == 350 )
						{
							// set to original height
							$session->set('image_y', $session->save_image_y);
						}
					
					// update default image set
					$def_image_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('syndicate_index', $session->reference_synd)
						->where('data_entry_format', $session->reference_data_entry_format)
						->where('scan_format', $session->reference_scan_format)
						->set(['image_y' => $session->image_y])
						->set(['image_scroll_step' => $session->panzoom_s])
						->update();
					break;
				case 2:
					// get inputs		
					foreach ($session->default_field_parms as $td) 
						{
							// get the value
							$width = $this->request->getPost($td['html_name']);
						
							//update file
							$def_fields_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('syndicate_index', $session->reference_synd)
								->where('data_entry_format', $session->reference_data_entry_format)
								->where('scan_format', $session->reference_scan_format)
								->where('html_name', $td['html_name'])
								->set(['column_width' => $width])
								->update();
						}
						
					// stop calibration
					$session->stop_calibrate = 'stop';
					break;
			}
			
		// go to next stage or back to transcription home
		if ( $session->stop_calibrate == 'stop' )
			{
				// set message
				$session->set('message_2', 'Image parameters have been re-calibrated for this Transcription Set.');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('transcribe/calibrate_reference_step1/0') );
			}
		else
			{
				// get the default image set
				$session->default_image_parms = $def_image_model
					->where('project_index', $session->current_project[0]['project_index'])
					->where('syndicate_index', $session->reference_synd)
					->where('data_entry_format', $session->reference_data_entry_format)
					->where('scan_format', $session->reference_scan_format)
					->find();
				
				// set next calibrate stage
				$session->calibrate = $session->calibrate + 1;
				return redirect()->to( base_url('transcribe/calibrate_coord_step1/0') );
			}
	}
}
