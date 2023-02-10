<?php namespace App\Controllers;

// test optimise_code

use App\Models\Allocation_Model;
use App\Models\Syndicate_Model;
use App\Models\Parameter_Model;
use App\Models\Transcription_Cycle_Model;

class Allocation extends BaseController
{
	function __construct() 
	{
        helper('common');
    }
	
	public function index()
	{
		// initialise method
		$session = session();
	}

	public function create_allocation_step1($start_message)
	{
		// From the CI 4 manual,
		// When a page is loaded, the session class will check to see if a valid session cookie is sent by the user’s browser. If a session's cookie does not exist (or if it doesn’t match one stored on the server or has expired) a new session will be created and saved.
		$session = session();
		
		// So if the login time out doesn't exist, it must mean that the session had expired.
		if ( ! isset($session->login_time_stamp) )
			{
				$session->set('session_expired', 1);
				return redirect()->to( base_url('/') );
			}
		
		// initialise method
		switch ($start_message) 
			{
				case 0:
					// load variables from common_helper.php
					load_variables();
					// input values defaults for first time
					$session->set('name', '');
					$session->set('autocreate', 'Y');
					$session->set('type', 'B');
					$session->set('letter', 'M');
					$session->set('year', '1988');
					$session->set('start_page', 'from...');
					$session->set('end_page', '...to');
					$session->set('make_current', 'Y');
					$session->set('reference_extension', '');
					// message defaults
					$session->set('message_1', 'Please enter the data required to create your allocation.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Please enter the data required to create your allocation.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		echo view('templates/header');
		if ( $session->reference_extension_control == 0 )
			{
				echo view('linBMD2/create_allocation_step1');
			}
		else
			{
				echo view('linBMD2/create_allocation_reference');
			}
		echo view('templates/footer');
	}
	
	public function create_allocation_step2()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$allocation_model = new Allocation_Model();
		$parameter_model = new Parameter_Model();
		// get url and user password for use in curl - there's a lot of curl!
		$user = $session->user[0]['BMD_user'];
		$password = $session->user[0]['BMD_password'];
		
		// load input values to array
		if ( $session->reference_extension_control == 0 )
			{
				$session->set('syndicate', $this->request->getPost('syndicate'));
				$session->set('name', $this->request->getPost('name'));
				$session->set('autocreate', $this->request->getPost('autocreate'));
				$session->set('type', $this->request->getPost('type'));
				$session->set('letter', $this->request->getPost('letter'));
				$session->set('year', $this->request->getPost('year'));
				$session->set('quarter', $this->request->getPost('quarter'));
				$session->set('start_page', $this->request->getPost('start_page'));
				$session->set('end_page', $this->request->getPost('end_page'));
				$session->set('make_current', $this->request->getPost('make_current'));
				$session->set('reference_extension', $this->request->getPost('reference_extension'));
			}
		else
			{
				$session->set('reference_extension', $this->request->getPost('reference_extension'));
			}						
		
		// do tests but only if reference_extension_control is 0. If it is = 1, we have already validated this stuff
		if ( $session->reference_extension_control == '0' )
			{
				// get syndicate
				$input_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->syndicate)->find();
				if ( ! $input_syndicate )
					{
						$session->set('message_2', 'You must select a syndicate from the dropdown list.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}									
				// test allocation name and autocreate
				if ( $session->autocreate == 'N' AND $session->name == '' )
					{
						$session->set('message_2', 'If auto create name is No, you must enter a name yourself.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				if ( $session->autocreate == 'Y' AND $session->name != '' )
					{
						$session->set('message_2', 'If auto create name is Yes, you must leave the allocation name blank.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
			
				// test year numeric
				if ( ! is_numeric($session->year) )
					{
						$session->set('message_2', 'Allocation year must be numeric.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
			
				// test start and end page for numeric
				if ( ! is_numeric($session->start_page) OR ! is_numeric($session->end_page) )
					{
						$session->set('message_2', 'Start and End page must be numeric.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
			}
		
		// all good
		// get curl stuff but only if reference_extension_control is 0. If it is = 1, we have already validated this stuff
		if ( $session->reference_extension_control == '0' )
			{
				// kickstart the scan path
				$session->set('scan_path', 'GUS/'.$session->year.'/'.$session->types_lower[$session->type].'/');
			}
		else
			{
				// test that scan letter is in the letter range if this reference extension is a letter range
				$letters = array();
				$letters = explode('-', $session->reference_extension_array[$session->reference_extension]); // explode the extension
				if ( isset($letters[1]) )
				{
					$letters[1] = substr($letters[1], 0, -1); // remove last character = remove the /
					// letter range?
					if ( array_search($letters[0], $session->alphabet) !== false AND  array_search($letters[1], $session->alphabet) !== false )
						{
							// if so is the scan letter in the range?
							$letter_found = 0;
							foreach  ( range($letters[0], $letters[1]) as $letter )
								{
									if ( $letter == $session->letter )
										{
											$letter_found = 1;
										}
								}
							// was it found
							if ( $letter_found == 0 )
								{
									// oops wrong letter range
									$session->set('message_2', 'Please choose the correct range for the allocation letter you entered => '.$session->letter);
									$session->set('message_class_2', 'alert alert-danger');
									$session->set('reference_extension_control', '1');
									return redirect()->to( base_url('allocation/create_allocation_step1/1') );
								}
						}
				}
				// add user selection to scan path
				$session->set('scan_path', $session->scan_path.$session->reference_extension_array[$session->reference_extension]);
			}
			
		// 	now search through the scan path until a scan is found
		// image url does not depend on environment
		$session->set('scan_found', 0);
		while ( $session->scan_found == 0 )
			{
				$curl_url = $session->autoimageurl.'/'.$session->scan_path;
				$ch = curl_init($curl_url);
				$fp = fopen(getcwd()."/curl_result.txt", "w");
				curl_setopt($ch, CURLOPT_FILE, $fp);
				curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");				
				if ( curl_exec($ch) === false )
					{
						// problem so send error message
						$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Allocation::create_allocation_step2, around line 190 => '.$curl_url);
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '0');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				curl_close($ch);
				fclose($fp);	
				
				// load file to array
				$lines = file(getcwd()."/curl_result.txt");
				
				// now test to see if a valid page was found
				foreach($lines as $line)
					{
						if ( strpos($line, "404 Not Found") !== false )
							{
								$session->set('message_2', 'A technical problem occurred. Please send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Malformed URL in Allocation::create_allocation_step2, , around line 198 => '.$curl_url);
								$session->set('message_class_2', 'alert alert-danger');
								$session->set('reference_extension_control', '0');
								return redirect()->to( base_url('allocation/create_allocation_step1/1') );
							}
					}
					
				// get all unique hrefs
				$search = "<li><a href='";
				$hrefs = array();
				foreach($lines as $line)
					{
						if ( strpos($line, $search) !== false )
							{
								// I have a href; check its not already in the array, store if not
								$href = get_string_between($line, "<li><a href='", "'>");
								if ( array_search($href, $hrefs) === false )
									{
										$hrefs[] = $href;
									}
							}
					}
				// does hrefs contain scans? if so break the while loop. a scan starts with the year and the type (B, M, D)
				$search = $session->year.$session->type;
				foreach ( $hrefs as $key => $value )
					{
						if ( strpos($value, $search) !== false )
							{
								$session->set('scan_found', 1);
								$session->set('reference_extension_control', '0');
								break 2;
							}
					}
				// so, if here, no scans were detected, continue building the scan path
				// if hrefs is empty, there is a problem, report it back to the user.
				if ( count($hrefs) == 0 )
					{
						$session->set('message_2', 'Path to scans cannot be identified, Please reveiw your Allocation entries. => Malformed URL in Allocation::create_allocation_step2, , around line 240 => '.$curl_url);
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '0');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				// if hrefs contains more than one entry ask user to choose which one
				if ( count($hrefs) > 1 )
					{
						$session->set('message_2', 'There are multiple sources for the scans for this allocation. Please choose the correct one.');
						$session->set('message_class_2', 'alert alert-warning');
						$session->set('reference_extension_array', $hrefs);
						$session->set('reference_extension_control', '1');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				// hrefs contains only one entry and it is not a scan, add it to the path and loop
				// save scan path to session 
				$session->set('scan_path', $session->scan_path.$hrefs[0]);
			}	// end loop
			
		// scans were found so scan path is known
		// get scan type eg jpg
		foreach ( $hrefs as $key => $value )
			{
				if ( strpos($value, '.') !== false )
					{
						$scan_type = substr($value, strpos($value, '.')+1);
						break;
					}
			}
		// explode the scan path
		$exploded_scan_path = array();
		$exploded_scan_path = explode('/', $session->scan_path);
		
		// Create the name if autocreate = yes
		if ( $session->autocreate == 'Y' )
			{
				// create the name depending if a quarter was found
				if ( array_search($exploded_scan_path[3], $session->quarters_short_long) !== false ) 
					{
						// quarter was found
						$session->set('name', $session->year.' '.$exploded_scan_path[3].' '.$session->types_lower[$session->type].', pages '.$session->start_page.' to '.$session->end_page.', from the '.$session->letter.' surnames');
					}
				else
					{
						// quarter was not found
						$session->set('name', $session->year.' '.$session->types_lower[$session->type].', pages '.$session->start_page.' to '.$session->end_page.', from the '.$session->letter.' surnames');
					}
			}
		
		// create quarter if year based
		if ( array_search($exploded_scan_path[3], $session->quarters_short_long) === false ) 
			{
				// quarter was not  found = year based, so set quarter = 4
				$session->set('quarter', '4');
			}
			
		// add allocation to table
		// create the data for the insert
		$data =	[
							'BMD_identity_index' => $session->BMD_identity_index,
							'BMD_syndicate_index' => $session->syndicate,
							'BMD_allocation_name' => $session->name,
							'BMD_reference' => $session->scan_path,
							'BMD_start_date' => $session->current_date,
							'BMD_end_date' => '',
							'BMD_start_page' => $session->start_page,
							'BMD_end_page' => $session->end_page,
							'BMD_year' => $session->year,
							'BMD_quarter' => $session->quarter,
							'BMD_letter' => $session->letter,
							'BMD_type' => $session->type,
							'BMD_scan_type' => $scan_type,
							'BMD_last_action' => 'Create Allocation',
							'BMD_status' => 'Open',
							'BMD_sequence' => 'SEQUENCED'
						];
		$id = $allocation_model->insert($data);
			
		// return
		$session->set('scan_name', '');
		$session->set('message_2',  'Your new Allocation has been been created. Select it from the dropdown list. ');
		$session->set('message_class_2', 'alert alert-success');
		$session->set('reference_extension_control', '0');
		return redirect()->to( base_url('header/create_BMD_step1/2') );
	}
	
	public function manage_allocations($start_message)
	{
		// initialise method
		$session = session();
		$allocation_model = new Allocation_Model();
		// set messages
		switch ($start_message) 
			{
				case 0:
					$session->set('message_1', 'Manage Allocations.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Manage Allocations.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
			}
		
		// get all allocations
		$session->allocations = $allocation_model
								->where('BMD_identity_index', $session->BMD_identity_index)
								->findAll();
		if (  ! $session->allocations )
			{
				$session->set('message_2',  'No allocations found. You should create one.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('allocation/create_allocation_step1/0') );
			}
		// show allocations
		echo view('templates/header');
		echo view('linBMD2/manage_allocations');
		echo view('templates/footer');
	}
	
	public function next_action()
	{
		// initialise method
		$session = session();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		// get inputs
		$BMD_allocation_index = $this->request->getPost('BMD_allocation_index');
		$session->set('BMD_cycle_code', $this->request->getPost('BMD_next_action'));
		$session->set('BMD_cycle_text', $transcription_cycle_model	->where('BMD_cycle_code', $session->BMD_cycle_code)
																												->where('BMD_cycle_type', 'ALLOC')
																												->find());
		// get allocation 
		$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $BMD_allocation_index)
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find();
		if ( ! $session->transcribe_allocation )
			{
				$session->set('message_2', 'Invalid allocation, please select again.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('allocation/manage_allocations/2') );
			}
		// perform action selected
		switch ($session->BMD_cycle_code) 
			{
				case 'NONEA': // nothing was selected
					$session->set('message_2', 'Please select an action to perform from the dropdown.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('allocation/manage_allocations/2') );
					break;
				case 'CLOSA': // close 
					$data =	[
										'BMD_status' => 'Closed',
										'BMD_end_date' => $session->current_date,
										'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
									];
					$allocation_model->update($BMD_allocation_index, $data);
					$session->set('message_2', 'Allocation was closed');
					$session->set('message_class_2', 'alert alert-success');
					return redirect()->to( base_url('allocation/manage_allocations/2') );
					break;
				case 'REOPA': //open
					$data =	[
										'BMD_status' => 'Open',
										'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
									];
					$allocation_model->update($BMD_allocation_index, $data);
					$session->set('message_2', 'Allocation was opened');
					$session->set('message_class_2', 'alert alert-success');
					return redirect()->to( base_url('allocation/manage_allocations/2') );
					break;
				case 'SNDEM': //Send email
					// only if allocation is closed
					if ( $session->transcribe_allocation[0]['BMD_status'] == 'Closed' )
						{
							$data =	[
												'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
											];
							$allocation_model->update($BMD_allocation_index, $data);
							// get syndicate 
							$session->transcribe_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->transcribe_allocation[0]['BMD_syndicate_index'])->find();
							if ( ! $session->transcribe_allocation )
								{
									$session->set('message_2', 'Invalid allocation, please select again.');
									$session->set('message_class_2', 'alert alert-danger');
									return redirect()->to( base_url('allocation/manage_allocations/2') );
								}
							// send email
							return redirect()->to( base_url('transcribe/send_email/allocation') );
						}
					else
						{
							$session->set('message_2', 'Cannot send message as the allocation is not closed.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('allocation/manage_allocations/2') );
						}
					break;
			}
		// no action found
		$session->set('message_2', 'No action performed. Selected action not recognised.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('allocation/manage_allocations/2') );			
	}
	
}
