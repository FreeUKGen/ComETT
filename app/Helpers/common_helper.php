<?php

use App\Models\Districts_Model;
use App\Models\Allocation_Model;
use App\Models\Syndicate_Model;
use App\Models\User_Parameters_Model;
use App\Models\Parameter_Model;
use App\Models\Identity_Model;
use App\Models\Transcription_Cycle_Model;

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
		// get districts
		$districts = $districts_model->findAll();
		// get current user values
		$user = $identity_model->where('BMD_identity_index',  $session->BMD_identity_index)->find();
		// get syndicates
		$syndicates = $syndicate_model->orderby('BMD_syndicate_name', 'ASC')->findAll();
		// get allocations
		$allocations = $allocation_model->orderby('BMD_allocation_name', 'ASC')
										->where('BMD_identity_index', $session->BMD_identity_index)
										->findAll();
		// load alphabet
		$alphabet = ["A" => "A", "B" => "B", "C" => "C", "D" => "D", "E" => "E", "F" => "F", "G" => "G", "H" => "H", "I" => "I", "J" => "J", "K" => "K", "L" => "L",
							"M" => "M", "N" => "N",  "O" => "O", "P" => "P", "Q" => "Q", "R" => "R", "S" => "S", "T" => "T",  "U" => "U",  "V" => "V", "W" => "W",  "X" => "X", 
							"Y" => "Y", "Z" => "Z",		
							];
		// load types
		$types_upper = [ "B" => "BIRTHS", "M" => "MARRIAGES", "D" => "DEATHS", ];
		$types_lower = [ "B" => "Births", "M" => "Marriages", "D" => "Deaths", ];
		// load quarters
		$quarters = [ "1" => "MAR", "2" => "JUN", "3" => "SEP", "4" => "DEC"];
		// load quarters long name
		$quarters_short_long = [ "1" => "March", "2" => "June", "3" => "September", "4" => "December"];
		// load month to quarter
		$month_to_quarter = [ "01" => "01", "02" => "01", "03" => "01", "04" => "02", "05" => "02", "06" => "02", "07" => "03", "08" => "03", "09" => "03", "10" => "04",
											"11" => "04", "12" => "04"];
		// load death months
		$session->set('death_days', [ "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", 
											"13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23",
											"24", "25", "26", "27", "28", "29", "30", "31", "- ", "AB" ] );
		// load death months
		$session->set('death_months', [ "JA", "FE", "MR", "AP", "MY", "JE", "JY", "AU", "SE", "OC", "NO", "DE", "- ", "OU" ] );
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
		// load environment
		$parameter = $parameter_model->where('Parameter_key', 'environment')->findAll();
		$session->set('environment', $parameter[0]['Parameter_value']);
		// load image url for curl
		$parameter = $parameter_model->where('Parameter_key', 'autoimageurl')->findAll();
		$session->set('autoimageurl', $parameter[0]['Parameter_value']);
		// load autoupload url for curl - LIVE
		$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_live')->findAll();
		$session->set('autouploadurl_live', $parameter[0]['Parameter_value']);
		// load autoupload url for curl - TEST
		$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_test')->findAll();
		$session->set('autouploadurl_test', $parameter[0]['Parameter_value']);
		// load programme name
		$parameter = $parameter_model->where('Parameter_key', 'programname')->findAll();
		$session->set('programname', $parameter[0]['Parameter_value']);
		// load version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// load uploadagent
		$parameter = $parameter_model->where('Parameter_key', 'uploadagent')->findAll();
		$session->set('uploadagent', $parameter[0]['Parameter_value']);
		// load linbmd2 email
		$parameter = $parameter_model->where('Parameter_key', 'linbmd2_email')->findAll();
		$session->set('linbmd2_email', $parameter[0]['Parameter_value']);
		// initialise reference extension array
		$reference_extension_array = array();
		$reference_extension_control = '0';
		// comment types
		$comment_types = [ "C" => "COMMENT = transcribed data differs in some way from what is in the index", "T" => "THEORY = transcribed data is what is in the index but there is reason to believe the index is wrong", "N" => "no type = Used to give information about the transcription", "B" => "Add a +BREAK line" ];
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
		
		// load to session
		$session->set('districts', $districts);
		$session->set('user', $user);
		$session->set('syndicates', $syndicates);
		$session->set('allocations', $allocations);
		$session->set('alphabet', $alphabet);
		$session->set('types_upper', $types_upper);
		$session->set('types_lower', $types_lower);
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
	
function BMD_file_exists_on_FreeBMD($BMD_file_name) // does this file name already exist on FreeBMD?
	{
		// initialise
		$session = session();
		$session->set('BMD_file_exists_on_FreeBMD', '0');
		// create the curl file
		$cfile = curl_file_create(getcwd()."/DUMMY.BMD", 'application/octet-stream', $BMD_file_name);
		// set up the fields to pass
		$postfields = array(
										"UploadAgent" => $session->uploadagent,
										"user" => $session->user[0]['BMD_user'],
										"password" => $session->user[0]['BMD_password'],
										"file" => $BMD_file_name,
										"content2" => $cfile,
										"data_version" => "districts.txt:??"
										);
		// set up the curl depending on environment, $session->curl_url is set in Home
		$ch = curl_init($session->curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.'1.0.0');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Transcribe::BMD_file_exists_on_FreeBMD, around line954 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/1') );
			}
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);
		// search the curl result for error
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "fileexists") !== FALSE)
				{
					$session->set('BMD_file_exists_on_FreeBMD', '1');
				}
		}
		// close file handle
		fclose($fp);
	}
