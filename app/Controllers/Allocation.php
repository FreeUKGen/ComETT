<?php namespace App\Controllers;

// test optimise_code

use App\Models\Allocation_Model;
use App\Models\Syndicate_Model;
use App\Models\Identity_Model;
use App\Models\Parameter_Model;
use App\Models\Transcription_Cycle_Model;
use App\Models\Def_Ranges_Model; //Def = Data entry format
use App\Models\Project_Types_Model;
use App\Models\Transcription_Model;

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
		// initialise method
		$session = session();		
		
		switch ($start_message) 
			{
				case 0:
					// load variables from common_helper.php
					load_variables();
					// input values defaults for first time
					$session->set('name', '');
					$session->set('autocreate', 'Y');
					$session->set('type', $session->project_types[0]['type_code']);
					$session->set('year', '');
					$session->set('start_page', '');
					$session->set('end_page', '');
					$session->set('make_current', 'Y');
					$session->set('reference_extension', '');
					if ( $session->current_identity[0]['last_syndicate'] == null )
						{
							$session->set('last_syndicate', '9999');
						}
					else
						{
							$session->set('last_syndicate', $session->current_identity[0]['last_syndicate']);
						}
					$session->set('field_name', '');
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
		$identity_model = new Identity_Model();
		$syndicate_model = new Syndicate_Model();
		$allocation_model = new Allocation_Model();
		$parameter_model = new Parameter_Model();
		$def_ranges_model = new Def_Ranges_Model();
		$project_types_model = new Project_Types_Model();
		
		// get url and user password for use in curl - there's a lot of curl!
		// depends on masquerading or not.
		if ( $session->masquerade == 1 )
			{
				$user = $session->coordinator_identity_userid;
				$password = $session->coordinator_identity_password;
			}
		else
			{
				$user = $session->identity_userid;
				$password = $session->identity_password;
			}
		
		// load input values to array
		if ( $session->reference_extension_control == 0 )
			{
				$session->set('name', $this->request->getPost('name'));
				$session->set('autocreate', $this->request->getPost('autocreate'));
				$session->set('type', $this->request->getPost('type'));
				$session->set('letter', $this->request->getPost('letter'));
				$session->set('year', $this->request->getPost('year'));
				$session->set('quarter', $this->request->getPost('quarter'));
				$session->set('start_page', $this->request->getPost('start_page'));
				$session->set('end_page', $this->request->getPost('end_page'));
				$session->set('scan_format', $this->request->getPost('scan_format'));
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
				// set last syndicate so as to not have to reselect it on other errors
				$session->set('last_syndicate', $session->syndicate_id);
				
				// test year numeric
				if ( ! is_numeric($session->year) )
					{
						$session->set('message_2', 'Allocation year must be numeric.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'year');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// test year not before records start year
				if ( $session->year < 1837 )
					{
						$session->set('message_2', 'Allocation year cannot be before 1837.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'year');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// test quarter has been selected
				if ( $session->quarter == 0 )
					{
						$session->set('message_2', 'Allocation quarter must be selected.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'quarter');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// create quarter name for later testing to determine reference path
				$session->quarter_name = $session->quarters_short_long[$session->quarter].'/';
			
				// test year not before records start year and quarter
				if ( $session->year == 1837 AND $session->quarter < 3 )
					{
						$session->set('message_2', 'Allocation year is 1837 so allocation quarter cannot be < September.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'year');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// test type has been selected
				if ( $session->type == 'S' )
					{
						$session->set('message_2', 'Allocation type must be selected.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'type');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// removed letter checks here. Saved in snippets
				
				// test start page for numeric
				if ( ! is_numeric($session->start_page) )
					{
						$session->set('message_2', 'Start page must be numeric.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'start_page');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// test end page for numeric
				if (  ! is_numeric($session->end_page) )
					{
						$session->set('message_2', 'End page must be numeric.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'end_page');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				
				// test end page is not less than start page
				if ( $session->end_page < $session->start_page )
					{
						$session->set('message_2', 'End page cannot be less than start page.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'end_page');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
		
				// test scan format
				if ( $session->scan_format == 'select' )
					{
						$session->set('message_2', 'Scan fomat must be selected.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'scan_format');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
						
				// test allocation name and autocreate
				if ( $session->autocreate == 'N' AND $session->name == '' )
					{
						$session->set('message_2', 'If auto create name is No, you must enter a name yourself.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'name');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				if ( $session->autocreate == 'Y' AND $session->name != '' )
					{
						$session->set('message_2', 'If auto create name is Yes, you must leave the allocation name blank.');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('field_name', 'name');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// determine data entry format for allocation by project, type, year and quarter
				// get ranges
				$def_ranges = $def_ranges_model	
					->where('project_index', $session->current_project[0]['project_index'])
					->where('type',  $session->type)
					->find();
				
				// any found?
				if ( ! $def_ranges )
					{
						$session->set('message_2', 'The data entry format for this allocation cannot be determined. Are you sure that your entries are correct? Type? Year? Quarter? Scan Format? If you are sure contact the FreeComETT adminstrator on '.$session->linbmd2_email.'  to report this issue (include the allocation details that you are trying to use) => No data entry range found for year, quarter and scan format in Allocation::create_allocation_step2.');
						$session->set('message_class_2', 'alert alert-danger');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// read though data entry ranges until good range found
				$range_found = 0;
				$yearquarter = $session->year.$session->quarter;
				foreach($def_ranges as $def_range)
					{
						$result = filter_var	(
												$yearquarter, 
												FILTER_VALIDATE_INT, 
												array	(
														'options' => array	(
																			'min_range' => $def_range['from_year'].$def_range['from_quarter'], 
																			'max_range' => $def_range['to_year'].$def_range['to_quarter'],
																			)
														)
												);
						if ( $result )
							{
								$range_found = 1;
								$session->def_format = $def_range['data_entry_format'];
								break;
							}
					}
					
				// was the def found
				if ( $range_found == 0 ) 
					{
						$session->set('message_2', 'The data entry format for this allocation cannot be determined. Are you sure that your entries are correct? Type? Year? Quarter? Scan Format? If you are sure contact the FreeComETT adminstrator on '.$session->linbmd2_email.'  to report this issue (include the allocation details that you are trying to use) => No data entry range found for year, quarter and scan format in Allocation::create_allocation_step2.');
						$session->set('message_class_2', 'alert alert-warning');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}				
			}
		else
			{
				// if here extention reference control is = 1, so check that the user chose something
				if ( $session->reference_extension == 0 )
					{
						// nothing was chosen
						$session->set('message_2', 'Please choose a reference extension from the dropdown list');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '1');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
			}
			
		// test if an allocation already exists which has the same pages as the being created
		$allocations =	$allocation_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_syndicate_index', $session->syndicate_id)
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('BMD_year', $session->year)
			->where('BMD_quarter', $session->quarter)
			->where('BMD_letter', $session->letter)
			->where('BMD_type', $session->type)
			->findAll();
		// found ? read each one to see if new pages are covered
		if ( $allocations )
			{
				foreach ( $allocations as $all )
					{
						// is new same as existing?
						if ( $session->start_page == $all['BMD_start_page'] AND $session->end_page == $all['BMD_end_page'] )
							{
								$session->set('message_2', 'You already have an allocation in this syndicate for the same year, quarter, and type exactly matching your new start and end pages');
								$session->set('message_class_2', 'alert alert-danger');
								return redirect()->to( base_url('allocation/create_allocation_step1/1') );
							}
							
						// is new in an existing?
						if ( $session->start_page > $all['BMD_start_page'] AND $session->end_page < $all['BMD_end_page'] )
							{
								$session->set('message_2', 'You already have an allocation in this syndicate for the same year, quarter, and type which includes your new start and end pages');
								$session->set('message_class_2', 'alert alert-danger');
								return redirect()->to( base_url('allocation/create_allocation_step1/1') );
							}
							
						// does existing partial cover new - start page?
						if ( $session->start_page >= $all['BMD_start_page'] AND $session->start_page <= $all['BMD_end_page'] )
							{
								$session->set('message_2', 'You already have an allocation in this syndicate for the same year, quarter, and type which partially covers your new start and end pages');
								$session->set('message_class_2', 'alert alert-danger');
								return redirect()->to( base_url('allocation/create_allocation_step1/1') );
							}
							
						// does existing partial cover new end page?
						if ( $session->end_page <= $all['BMD_end_page'] AND $session->end_page >= $all['BMD_start_page'] )
							{
								$session->set('message_2', 'You already have an allocation in this syndicate for the same year, quarter, and type which partially covers your new start and end pages');
								$session->set('message_class_2', 'alert alert-danger');
								return redirect()->to( base_url('allocation/create_allocation_step1/1') );
							}	
					}
			}
			
		// all good
		
		// remove no checks flag at end of data if there is one
		if ( substr($session->letter, -1) == '#' ) 
			{
				$session->letter = substr($session->letter, 0, -1);
			}
		
		// get current project type
		$session->current_project_type = $project_types_model
			->where('type_code', $session->type)
			->find();

		// get curl stuff but only if reference_extension_control is 0. If it is = 1, we have already validated this stuff
		// the idea here is that a scan matching the allocation parameters can be found. Scans won't be downloaded until a transcription in the allocation is created
		if ( $session->reference_extension_control == '0' )
			{
				// this must depend on the project - need more info
				// kickstart the scan path
				$session->set('scan_path', 'GUS/'.$session->year.'/'.$session->current_project_type[0]['type_name_lower'].'/');
			}
		else
			{
				// do letter test only if length of entered letter = 1 ie a single letter was entered, which can be a range, eg A-C
				if ( strlen($session->letter) == 1 )
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
											$session->set('field_name', 'letter');
											return redirect()->to( base_url('allocation/create_allocation_step1/1') );
										}
								}
						}
					}
				// add user selection to scan path
				$session->set('scan_path', $session->scan_path.$session->reference_extension_array[$session->reference_extension]);
			}
			
		// now search through the scan path until a scan is found
		// image url does not depend on environment
		$session->set('scan_found', 0);
		while ( $session->scan_found == 0 )
			{
				// setup curl
				$curl_url = $session->current_project[0]['project_autoimageservertype'].$session->current_project[0]['project_autoimageurl'].$session->scan_path;
				$ch = curl_init($curl_url);
				curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				// debug options
				//curl_setopt($ch, CURLOPT_VERBOSE, true);
				//curl_setopt($ch, CURLOPT_STDERR, fopen(getcwd()."/curl.log", 'a+'));
								
				// do the curl
				$curl_result = curl_exec($ch);
				
				// anything found
				if ( $curl_result == '' )
					{
						// problem so send error message
						$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Allocation::create_allocation_step2 => '.$curl_url);
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '0');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				
				curl_close($ch);
							
				// load returned data to array
				$lines = preg_split("/\r\n|\n|\r/", $curl_result);
							
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
								// get the href
								$href = get_string_between($line, "<li><a href='", "'>");
								// I have a href; check its not already in the array, store if not
								if ( array_search($href, $hrefs) === false )
									{
										$hrefs[] = $href;
									}
							}
					}
					
				// does the quarter requested by the user exist in hrefs? if so avoid requesting the quarter again by removing unrequired hrefs.
				$result = array_search($session->quarter_name, $hrefs);
				if ( $result !== false )
					{
						// the quarter was found so use it
						$hrefs = array();
						$hrefs[] = $session->quarter_name;
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
						$session->set('message_2', 'Path to scans cannot be identified, Please review your Allocation entries. => Malformed URL in Allocation::create_allocation_step2 => '.$curl_url);
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '0');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// if hrefs contains more than one entry ask user to choose which one
				if ( count($hrefs) > 1 )
					{
						array_unshift($hrefs, "Please select the source for your scans");
						$session->set('message_2', 'There are multiple sources for the scans for this allocation. Please choose the correct one. If quarters, no scans where found for the quarter you entered, '.$session->quarter_name.'.');
						$session->set('message_class_2', 'alert alert-warning');
						$session->set('reference_extension_array', $hrefs);
						$session->set('reference_extension_control', '1');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
					
				// hrefs contains only one entry and it is not a scan, add it to the path and loop
				// save scan path to session 
				$session->set('scan_path', $session->scan_path.$hrefs[0]);
			}	// end loop

		// scans were found so scan path is known. hrefs contains all the scan names. 
		// Now test that the page range is consistent with the letter
		$valid_hrefs = array();
		foreach ( $hrefs as $key => $value )
			{
				// explode the scan name on . to test the file extension
				$exploded_scan_name = explode('.', $value);
				
				// very browsers can display tif or tiff files so exclude them
				if ( $exploded_scan_name[1] == 'tif' OR $exploded_scan_name[1] == 'tiff' )
					{
						$session->set('message_2', 'You have selected a scan source which contains .tif file images. Very few browsers can display tif images. Please selected a source that contains jpg images (usually ANC-nn).');
						$session->set('message_class_2', 'alert alert-danger');
						$session->set('reference_extension_control', '0');
						return redirect()->to( base_url('allocation/create_allocation_step1/1') );
					}
				
				// now explode scan name on - to get the letter and page.
				$exploded_scan_name = explode('-', $exploded_scan_name[0]);
				// element 1 contains the letter, element 2 contains the page.
				// test that I have the letter or letter range I am looking for 
				if ( count($exploded_scan_name) == 3 )
					{
						// single character letter
						if ( $exploded_scan_name[1] == $session->letter )
							{
								// I have found the start of the letter range
								// $valid_hrefs contains the full range by page of all scans for this letter
								$valid_hrefs[] = $exploded_scan_name[2];
							}
					}
				else
					{
						// composite character letter
						if ( $exploded_scan_name[1].'-'.$exploded_scan_name[2] == $session->letter )
							{
								// I have found the start of the letter range
								// $valid_hrefs contains the full range by page of all scans for this letter
								$valid_hrefs[] = $exploded_scan_name[3];
							}
					}
			}

		// test will fail if $valid_hrefs is empty
		if ( empty($valid_hrefs) )
			{
				$session->set('message_2', 'Cannot check page range is OK for the letter you entered as no images have been found. Check the letter or letter range is correct => '.$session->letter.'.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('allocation/create_allocation_step1/1') );
			}
		
		
		// test start and end hrefs values for numerics; if not make them numeric
		$start_test = $valid_hrefs[0];
		if ( ! is_numeric($valid_hrefs[0]) )
			{
				$start_test = '0000';
			}
		$end_test = end($valid_hrefs);
		if ( ! is_numeric(end($valid_hrefs)) )
			{
				$end_test = '9999';
			}
			
		// Now I can test if the start page and end page are in the scans for this letter range
		if ( $session->start_page < $start_test OR $session->end_page > $end_test )
			{
				$session->set('message_2', 'The page range is not valid for the scan letter you entered. The scan page range for this letter, '.$session->letter.', using scan path, '.$session->scan_path.', starts at '.$valid_hrefs[0].' and ends at '.end($valid_hrefs).'. Please review your Allocation entries.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('allocation/create_allocation_step1/1') );
			}
	
		// is the start page in the valid hrefs
		$found_flag = 0;
		$session->start_page = str_pad($session->start_page, 4, "0", STR_PAD_LEFT);
		foreach ( $valid_hrefs as $href )
			{
				if ( $href == $session->start_page )
					{
						$found_flag = 1;
					}
			}
		if ( $found_flag == 0 )
			{
				$session->set('message_2', 'The start page is not in the list of scans found on the image server which means that a scan does not exist for the start page you entered. Please review your Allocation entries.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('allocation/create_allocation_step1/1') );
			}
		
		// is the end page in the valid hrefs
		$found_flag = 0;
		$session->end_page = str_pad($session->end_page, 4, "0", STR_PAD_LEFT);	
		foreach ( $valid_hrefs as $href )
			{
				if ( $href == $session->end_page )
					{
						$found_flag = 1;
					}
			}
		if ( $found_flag == 0 )
			{
				$session->set('message_2', 'The end page is not in the list of scans found on the image server which means that a scan does not exist for the end page you entered. Please review your Allocation entries.');
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('reference_extension_control', '0');
				return redirect()->to( base_url('allocation/create_allocation_step1/1') );
			}		

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
						$session->set('name', $session->year.' '.$exploded_scan_path[3].' '.$session->current_project_type[0]['type_name_lower'].', '.$session->letter.' surnames, pages '.$session->start_page.' to '.$session->end_page.', using scan format '.$session->scan_format);
					}
				else
					{
						// quarter was not found
						$session->set('name', $session->year.' '.$session->current_project_type[0]['type_name_lower'].', '.$session->letter.' surnames, pages '.$session->start_page.' to '.$session->end_page.', using scan format '.$session->scan_format);
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
					'project_index' => $session->current_project[0]['project_index'],
					'BMD_identity_index' => $session->BMD_identity_index,
					'BMD_syndicate_index' => $session->syndicate_id,
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
					'BMD_sequence' => 'SEQUENCED',
					'data_entry_format' => $session->def_format,
					'scan_format' => $session->scan_format,
				];
		$id = $allocation_model->insert($data);
		
		// update identity with last syndicate and last allocation
		$data =	[
					'last_syndicate' => $session->syndicate_id,
					'last_allocation' => $id,
				];
		$identity_model->update($session->BMD_identity_index, $data);
		// reload identity
		$session->current_identity = $identity_model	
			->where('BMD_identity_index', $session->BMD_identity_index)
			->where('project_index', $session->current_project[0]['project_index'])
			->find();
		// reload allocation
		load_variables();
			
		// return
		$session->set('scan_name', '');
		$session->set('message_2',  'Your new Allocation has been been created. Go to Create a new Transcription to start using it to create a Transcription.');
		$session->set('message_class_2', 'alert alert-success');
		$session->set('reference_extension_control', '0');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
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
					// sort
					if ( ! isset($session->alloc_sort_by) )
						{
							$session->alloc_sort_by = 'allocation.Change_date';
							$session->alloc_sort_order = 'DESC';
							$session->alloc_sort_name = 'Last change date/time';
						}
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
			->where('allocation.BMD_identity_index', $session->BMD_identity_index)
			->where('allocation.project_index', $session->current_project[0]['project_index'])
			->where('allocation.BMD_syndicate_index', $session->syndicate_id)
			->where('allocation.BMD_status', $session->allocation_status)
			->join('syndicate', 'allocation.BMD_syndicate_index = syndicate.BMD_syndicate_index')
			->orderBy($session->alloc_sort_by, $session->alloc_sort_order)
			->findAll();
			
		// show allocations
		echo view('templates/header');
		echo view('linBMD2/manage_allocations');
		echo view('linBMD2/sortTableNew');
		echo view('linBMD2/searchTableNew');
		echo view('templates/footer');
	}
	
	public function next_action()
	{
		// initialise method
		$session = session();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$transcription_model = new Transcription_Model();
		
		// get inputs
		$BMD_allocation_index = $this->request->getPost('BMD_allocation_index');
		$session->set('BMD_cycle_code', $this->request->getPost('BMD_next_action'));
		$session->set('BMD_cycle_text', $transcription_cycle_model	->where('BMD_cycle_code', $session->BMD_cycle_code)
																	->where('BMD_cycle_type', 'ALLOC')
																	->find());
		
		// get allocation 
		$session->current_allocation = $allocation_model	->where('BMD_allocation_index',  $BMD_allocation_index)
															->where('BMD_identity_index', $session->BMD_identity_index)
															->where('project_index', $session->current_project[0]['project_index'])
															->find();
		// should never happen but ...
		if ( ! $session->current_allocation )
			{
				$session->set('message_2', 'Invalid allocation. If you get this message, please contact '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('allocation/manage_allocations/2') );
			}
			
		// get syndicate 
		$session->current_syndicate = $syndicate_model		->where('BMD_syndicate_index',  $session->current_allocation[0]['BMD_syndicate_index'])
															->where('project_index', $session->current_project[0]['project_index'])
															->find();
		// should never happen but...
		if ( ! $session->current_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate. If you get this message, please contact '.$session->linbmd2_email);
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
					$session->set('message_2', 'The Allocation you selected was closed successfully.');
					$session->set('message_class_2', 'alert alert-success');
					return redirect()->to( base_url('allocation/manage_allocations/2') );
					break;
				case 'REOPA': // reopen
					$data =	[
										'BMD_status' => 'Open',
										'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
									];
					$allocation_model->update($BMD_allocation_index, $data);
					$session->set('message_2', 'The Allocation you selected was re-opened successfully.');
					$session->set('message_class_2', 'alert alert-success');
					return redirect()->to( base_url('allocation/manage_allocations/2') );
					break;
				case 'SNDEM': //Send email
					// only if allocation is closed
					if ( $session->current_allocation[0]['BMD_status'] == 'Closed' )
						{
							$data =	[
										'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
									];
							$allocation_model->update($BMD_allocation_index, $data);
							
							// send email
							return redirect()->to(base_url('email/send_email/allocation') );
						}
					else
						{
							$session->set('message_2', 'Cannot send email to request new Allocation as the current Allocation is not closed.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('allocation/manage_allocations/2') );
						}
					break;
				case 'DELEA': // delete allocation
					// only if no transcriptions exist against this allocation
					// get transcriptions for this allocation
					$transcriptions = $transcription_model	->where('BMD_allocation_index',  $BMD_allocation_index)
															->where('BMD_identity_index', $session->BMD_identity_index)
															->where('project_index', $session->current_project[0]['project_index'])
															->find();
					// if any found cannot delete
					if ( $transcriptions )
						{
							$session->set('message_2', 'Cannot delete this allocation because Transcriptions exist against it.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('allocation/manage_allocations/2') );
						}
					else
						{
							// delete it
							$allocation_model->delete($BMD_allocation_index);
							$session->set('message_2', 'Allocation, '.$session->current_allocation[0]['BMD_allocation_name'].', has been deleted.');
							$session->set('message_class_2', 'alert alert-success');
							return redirect()->to( base_url('allocation/manage_allocations/2') );
						}
					break;
			}
		// no action found - Oops should never happen
		$session->set('message_2', 'No action performed. Selected action not recognised. Report to '.$session->linbmd2_email);
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('allocation/manage_allocations/2') );			
	}
	
	public function sort($by)
	{
		// initialise method
		$session = session();
		
		// set sort by
		switch ($by) 
			{
				case 1:
					$session->alloc_sort_by = 'syndicate.BMD_syndicate_name';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Syndicate Name';
					break;
				case 2:
					$session->alloc_sort_by = 'allocation.BMD_allocation_name';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Allocation Name';
					break;
				case 3:
					$session->alloc_sort_by = 'allocation.BMD_start_date';
					$session->alloc_sort_order = 'DESC';
					$session->alloc_sort_name = 'Start Date';
					break;
				case 4:
					$session->alloc_sort_by = 'allocation.BMD_end_date';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'End Date';
					break;
				case 5:
					$session->alloc_sort_by = 'allocation.BMD_last_uploaded';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Last Page Uploaded';
					break;
				case 6:
					$session->alloc_sort_by = 'allocation.BMD_status';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Status';
					break;
				case 7:
					$session->alloc_sort_by = 'allocation.BMD_last_action';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Last Action Performed';
					break;
				case 8:
					$session->alloc_sort_by = 'allocation.Change_date';
					$session->alloc_sort_order = 'ASC';
					$session->alloc_sort_name = 'Last Change Date';
					break;
				default:
					$session->alloc_sort_by = 'allocation.Change_date';
					$session->alloc_sort_order = 'DESC';
					$session->alloc_sort_name = 'Last Change Date/Time';
			}
				
		return redirect()->to( base_url('allocation/manage_allocations/1') );
	}
	
	public function toogle_allocations()
	{
		// initialise
		$session = session();
		
		// change status
		if ( $session->allocation_status == 'Open' )
			{
				$session->allocation_status = 'Closed';
			}
		else
			{
				$session->allocation_status = 'Open';
			}
			
		// redirect to transcribe
		return redirect()->to( base_url('allocation/manage_allocations/0') );
	}
	
}
