<?php

use App\Models\Districts_Model;
use App\Models\Allocation_Model;
use App\Models\Syndicate_Model;
use App\Models\User_Parameters_Model;
use App\Models\Parameter_Model;
use App\Models\Identity_Model;
use App\Models\Transcription_Cycle_Model;
use App\Models\Project_Types_Model;

function load_variables()
	{
		// inialise
		$session = session();
		$districts_model = new Districts_Model;
		$syndicate_model = new Syndicate_Model();
		$allocation_model = new Allocation_Model();
		$user_parameters_model = new User_Parameters_Model();
		$parameter_model = new Parameter_Model();
		$identity_model = new Identity_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$project_types_model = new Project_Types_Model();
		
		// clean up logs - keep logs for 15 days	
		$keep_from = strtotime('-15 days');

		// do log clean up
		$dir = new DirectoryIterator(dirname(WRITEPATH.'logs/*.log'));
		foreach ($dir as $fileinfo) 
			{
				if (!$fileinfo->isDot() AND $fileinfo->getExtension() == 'log' ) 
					{
						$ctime = $fileinfo->getCTime();
						if ( $ctime < $keep_from )
							{
								unlink($fileinfo->getPathname());
							}
					}
			}		
		
		// get districts
		$districts = $districts_model->findAll();
			
		// get syndicates
		$syndicates = $syndicate_model	
			->where('project_index', $session->current_project[0]['project_index'])
			->orderby('status', 'ASC')
			->orderby('BMD_syndicate_name', 'ASC')
			->findAll();		
		
		// get allocations
		$allocations = $allocation_model->orderby('BMD_allocation_name', 'ASC')
			->where('BMD_status', 'Open')
			->where('BMD_identity_index', $session->BMD_identity_index)
			->orwhere('BMD_identity_index', 999999)
			->where('project_index', $session->current_project[0]['project_index'])
			->orwhere('project_index', 999999)
			->findAll();
							
		// load alphabet
		$alphabet = [	"A" => "A", "B" => "B", "C" => "C", "D" => "D", "E" => "E", "F" => "F", "G" => "G", "H" => "H", "I" => "I", "J" => "J", "K" => "K", 					"L" => "L",
							"M" => "M", "N" => "N",  "O" => "O", "P" => "P", "Q" => "Q", "R" => "R", "S" => "S", "T" => "T",  "U" => "U",  "V" => "V", "W" => "W",  "X" => "X", 
							"Y" => "Y", "Z" => "Z",	
							];
		// load types for this project
		$session->project_types = $project_types_model
			->where('project_index', $session->current_project[0]['project_index'])
			->orderby('type_order')
			->find();					
		// load quarters
		$quarters = [ "1" => "MAR", "2" => "JUN", "3" => "SEP", "4" => "DEC"];
		// load quarters long name
		$quarters_short_long = [ "0" => "Select :", "1" => "March", "2" => "June", "3" => "September", "4" => "December"];
		// load month to quarter
		$month_to_quarter = [ "01" => "01", "02" => "01", "03" => "01", "04" => "02", "05" => "02", "06" => "02", "07" => "03", "08" => "03", "09" => "03", "10" => "04",
											"11" => "04", "12" => "04"];
		// load death months
		$session->set('valid_days', [ "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", 
											"13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23",
											"24", "25", "26", "27", "28", "29", "30", "31", "- ", "AB" ] );
		// load death months
		$session->set('valid_2letter_month_codes', [ "JA", "FE", "MR", "AP", "MY", "JE", "JY", "AU", "SE", "OC", "NO", "DE", "- ", "OU", "UT" ] );
		// load marriage months
		$session->set('marriage_months', [ "JAN" => "01", "FEB" => "02", "MAR" => "03", "APR" => "04", "MAY" => "05",
											"JUN" => "06", "JUL" => "07", "AUG" => "08", "SEP" => "09", "OCT" => "10",
											"NOV" => "11", "DEC" => "12" ] );
		
		// load scan name types
		$scan_name_types = [ "Y" => "Year", "Q" => "Quarter", ];
		// load yesno
		$yesno = [ "Y" => "Yes", "N" => "No", ];
		// load current date and login time stamp
		$current_date = date("d-M-Y");
		// load system parameters
		$parameters = $parameter_model->findAll();
		// load programme name
		$parameter = $parameter_model->where('Parameter_key', 'programname')->findAll();
		$session->set('programname', $parameter[0]['Parameter_value']);
		// load version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// load uploadagent
		$parameter = $parameter_model->where('Parameter_key', 'uploadagent')->findAll();
		$session->set('uploadagent', $parameter[0]['Parameter_value']);
		// initialise reference extension array
		$reference_extension_array = array();
		$reference_extension_control = '0';
		// comment types
		$comment_types =	[ 
								"C" => "COMMENT = transcribed data differs in some way from what is in the index", 
								"T" => "THEORY = transcribed data is what is in the index but there is reason to believe the index is wrong", 
								"N" => "no type = Used to give information about the transcription", 
								"B" => "Add a +BREAK line",
								"P" => "Add a +PAGE line (only if 2 or more page scan)",
								"R" => "THEORY REF = used to indicate a reference to a late registration in standard format."
							];
		// load transcrition cycle
		$transcription_cycles = $transcription_cycle_model->orderby('BMD_cycle_sort', 'ASC')->findAll();
		// load fonts from fonts folder
		$dir = new DirectoryIterator(dirname(getcwd().'/Fonts/*.*'));
		$data_entry_fonts = array();
		foreach ($dir as $fileinfo) 
			{
				if (!$fileinfo->isDot()) 
					{
						$font_name_array = explode('.', $fileinfo->getFilename());
						$data_entry_fonts[] = $font_name_array[0];
					}
			}
		asort($data_entry_fonts);
		// load font_styles
		$data_entry_styles = array('normal', 'bold', 'bolder', 'lighter');
		asort($data_entry_styles);
		// create the roman2arabic conversion array - according to M Cope ony numbers 1 to 27 are used.
		$session->roman2arabic = array	(
											"I" => 1,
											"II" => 2,
											"III" => 3,
											"IV" => 4,
											"V" => 5,
											"VI" => 6,
											"VII" => 7,
											"VIII" => 8,
											"IX" => 9,
											"X" => 10,
											"XI" => 11,
											"XII" => 12,
											"XIII" => 13,
											"XIV" => 14,
											"XV" => 15,
											"XVI" => 16,
											"XVII" => 17,
											"XVIII" => 18,
											"XIX" => 19,
											"XX" => 20,
											"XXI" => 21,
											"XXII" => 22,
											"XXIII" => 23,
											"XXIV" => 24,
											"XXV" => 25,
											"XXVI" => 26,
											"XXVII" => 27,
										);
		// scan formats
		$session->scan_formats = array	(	
											'select' => 'Select :',
											'handwritten' =>'Handwritten', 
											'typed' => 'Typed',
											'printed' => 'Printed'
										);
										
		// colours
		$session->colours = array		(	
											'select' => 'Select :',
											'red' =>'Red', 
											'green' => 'Green',
											'blue' => 'Blue',
											'Pink' => 'Pink',
											'black' => 'Black',
											'lightgreen' => 'Light Green',
											'lightblue' => 'Light Blue'
										);	
		
		// load to session
		$session->set('districts', $districts);
		$session->set('syndicates', $syndicates);
		$session->set('allocations', $allocations);
		$session->set('alphabet', $alphabet);
		$session->set('quarters', $quarters);
		$session->set('quarters_short_long', $quarters_short_long);
		$session->set('month_to_quarter', $month_to_quarter);
		$session->set('scan_name_types', $scan_name_types);
		$session->set('yesno', $yesno);
		$session->set('current_date', $current_date);
		$session->set('parameters', $parameters);
		$session->set('reference_extension_array', $reference_extension_array);
		$session->set('reference_extension_control', $reference_extension_control);
		$session->set('comment_types', $comment_types);
		$session->set('transcription_cycles', $transcription_cycles);
		$session->set('data_entry_fonts', $data_entry_fonts);
		$session->set('data_entry_styles', $data_entry_styles);
	}
	
function get_string_between($string, $start, $end)
	{
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}
	
function BMD_file_exists_on_project($BMD_file_name) // does this file name already exist on FreeBMD?
	{
		// initialise
		$session = session();
		$session->set('BMD_file_exists_on_project', '0');
		
		// test bmd file exists on server
		$curl_url = $session->curl_url;
		
		// create the curl parameters
		$encoding = 'iso8859-1';
		//$encoding = 'utf8';
		// set up the fields to pass
		$postfields =	array	(
									"__bmd_0" => "Download",
									"__bmd_1" => $session->identity_userid,
									"__bmd_2" => $session->identity_password,
									"encoding" => $encoding,
									"downloaddo_".$BMD_file_name => "Download",
								);
		// set up the curl depending on environment
		$ch = curl_init($session->curl_url);			
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// execute the curl.	
		$curl_result = curl_exec($ch);

		// do I have a bmd file?
		$data_array = explode(',', $curl_result);
		if ( $data_array[0] == '+INFO' )
			{
				$session->set('BMD_file_exists_on_project', '1');
			}	
	}
	
function manage_syndicate_DB()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		
		// depends on project
		switch ($session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':
					// set all not active
					$syndicate_model
						->where('project_index', $session->current_project[0]['project_index'])
						->set(['status' => '1'])
						->update();
					
					// get syndicates from FreeBMD server
					$db = \Config\Database::connect($session->syndicate_DB);
					$sql =	"
								SELECT * 
								FROM SyndicateTable 
								WHERE SyndicateTable.SyndicateShortDesc NOT LIKE 'This syndicate is no longer active having completed its agreed allocations.'
								ORDER BY SyndicateTable.SyndicateID
							";
					$query = $db->query($sql);
					$active_project_syndicates = $query->getResultArray();
					
					// read active project syndicates
					foreach ( $active_project_syndicates as $active_syndicate )
						{
							// does this syndicate exist in FreeComETT syndicates table
							$exists	= $syndicate_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('BMD_syndicate_name', $active_syndicate['SyndicateName'])
								->find();
								
							// found?
							if ( $exists )
								{
									// Update it as active
									$syndicate_model
										->where('project_index', $session->current_project[0]['project_index'])
										->where('BMD_syndicate_name', $active_syndicate['SyndicateName'])
										->set(['BMD_syndicate_email' => $active_syndicate['SyndicateEmail']])
										->set(['status' => '0'])
										->update();
								}
							else
								{
									// insert it as active
									$syndicate_model
										->set(['project_index' => $session->current_project[0]['project_index']])
										->set(['BMD_syndicate_index' => $active_syndicate['SyndicateID']])
										->set(['BMD_syndicate_name' => $active_syndicate['SyndicateName']])
										->set(['BMD_syndicate_leader' => $active_syndicate['CorrectionsContact']])
										->set(['BMD_syndicate_email' => $active_syndicate['SyndicateEmail']])
										->set(['saved_email' => $active_syndicate['SyndicateEmail']])
										->set(['BMD_syndicate_credit' => 'N'])
										->set(['status' => '0'])
										->set(['new_user_environment' => 'TEST'])
										->insert();
								}
						}
					break;
				case 'FreeREG':
					// create mongodb syndicate collection definitions
					$client = new \MongoDB\Client($session->project_DB['DBDriver'].$session->project_DB['hostname'].':'.$session->project_DB['port']);
					$database = $client->selectDatabase($session->project_DB['database']);
					$collection_syndicates = $database->{'syndicates'};
					$collection_userid = $database->{'userid_details'};
					
					// get all syndicate details
					$active_project_syndicates = $collection_syndicates->find()->toArray();
					
					// read active project syndicates
					foreach ( $active_project_syndicates as $active_syndicate )
						{
							// get coordinator details for this syndicate
							$coord = $identity_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('BMD_user', $active_syndicate['syndicate_coordinator'])
								->find();

							// coord exists in FreeComETT identity table?
							if ( ! $coord )
								{
									// add it if not
									$identity_model
										->set(['BMD_user' => $active_syndicate['syndicate_coordinator']])
										->set(['role_index' => 2])
										->set(['project_index' => $session->current_project[0]['project_index']])
										->insert();
								}
							
							// get coordinator details
							$coord = $collection_userid->findOne
								(
									[
										'userid' => $active_syndicate['syndicate_coordinator']
									]
								);
								
							// does this syndicate exist in FreeComETT syndicates table
							$exists	= $syndicate_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('BMD_syndicate_name', $active_syndicate['syndicate_code'])
								->find();
													
							// found?
							if ( $exists )
								{
									// Update it
									$syndicate_model
										->where('project_index', $session->current_project[0]['project_index'])
										->where('BMD_syndicate_name', $active_syndicate['syndicate_code'])
										->set(['BMD_syndicate_email' => $coord['email_address']])
										->update();
								}
							else
								{
									// decode syndicate IDid
									$id = $active_syndicate['_id']->__toString();
																
									// insert it as active
									$syndicate_model
										->set(['project_index' => $session->current_project[0]['project_index']])
										->set(['BMD_syndicate_index' => $id])
										->set(['BMD_syndicate_name' => $active_syndicate['syndicate_code']])
										->set(['BMD_syndicate_leader' => $coord['person_forename'].' '.$coord['person_surname']])
										->set(['BMD_syndicate_email' => $coord['email_address']])
										->set(['saved_email' => $coord['email_address']])
										->set(['BMD_syndicate_credit' => 'N'])
										->set(['status' => '0'])
										->set(['new_user_environment' => 'TEST'])
										->insert();
								}
						}
					break;
				case 'FreeCEN':
					break;
			}
	}
