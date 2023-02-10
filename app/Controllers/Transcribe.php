<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Header_Table_Details_Model;
use App\Models\Detail_Data_Model;
use App\Models\Detail_Comments_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Identity_Model;
use App\Models\Transcription_Cycle_Model;
use App\Models\Parameter_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require APPPATH.'/ThirdParty/PHPMailer/src/Exception.php';
require APPPATH.'/ThirdParty/PHPMailer/src/PHPMailer.php';
require APPPATH.'/ThirdParty/PHPMailer/src/SMTP.php';

class Transcribe extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('image');
        helper('transcribe');
    }
	
	public function transcribe_step1($start_message)
	{ 
		// From the CI 4 manual
		// When a page is loaded, the session class will check to see if a valid session cookie is sent by the user’s browser. If a session's cookie does not exist (or if it doesn’t match one stored on the server or has expired) a new session will be created and saved.
		$session = session();
		
		// So if the login time out doesn't exist, it must mean that the session had expired.
		if ( ! isset($session->login_time_stamp) )
			{
				$session->set('session_expired', 1);
				return redirect()->to( base_url('/') );
			}		
		
		// initialise method
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle = new Transcription_Cycle_Model();
		
		switch ($start_message) 
			{
				case 0:
					// load variables from common_helper.php
					load_variables();
					// message defaults
					$session->set('message_1', 'Please select the action you wish to perform on the BMD file and click GO. Or create a new BMD file.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					// flow control
					$session->set('show_view_type', 'transcribe');
					// set defaults
					$session->set('close_header', 'N');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Please select the action you wish to perform on the BMD file and click GO. Or create a new BMD file.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
			
		// get uncompleted headers
		$session->headers = $header_model	->where('header.BMD_identity_index', $session->BMD_identity_index)
											->where('header.BMD_header_status', '0')
											->join('allocation', 'header.BMD_allocation_index = allocation.BMD_allocation_index')
											->join('syndicate', 'header.BMD_syndicate_index = syndicate.BMD_syndicate_index')
											->select('header.BMD_header_index, header.BMD_file_name, header.BMD_scan_name, header.BMD_records, header.BMD_start_date, header.Change_date,
													header.BMD_submit_date, header.BMD_submit_status, header.BMD_last_action, allocation.BMD_allocation_name, syndicate.BMD_syndicate_name')
											->orderBy('header.Change_date', 'DESC')
											->findAll();
				
		// were any found?
		if ( ! $session->headers )
			{
				$session->set('message_2', 'You have no open BMD files to work on. Please create a new one.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		
		// show open headers for this user for view user_home																	
		echo view('templates/header');
		switch ($session->show_view_type) 
			{
				case 'transcribe':
					echo view('linBMD2/user_home');
					break;
				case 'close_header':
					echo view('linBMD2/transcribe_close_header');
					break;
				case 'verify_BMD':
					echo view('linBMD2/transcribe_verify_BMD');
					break;
				case 'verify_trans':
					echo view('linBMD2/transcribe_verify_trans');
					echo view('linBMD2/transcribe_details_show');
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
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		// destroy any feh windows
		$session->remove('feh_show');
		// get inputs
		$BMD_header_index = $this->request->getPost('BMD_header_index');
		$session->set('BMD_cycle_code', $this->request->getPost('BMD_next_action'));
		$session->set('BMD_cycle_text', $transcription_cycle_model	
			->where('BMD_cycle_code', $session->BMD_cycle_code)
			->where('BMD_cycle_type', 'TRANS')
			->find());
		
		// get header 
		$session->transcribe_header = $header_model
			->where('BMD_header_index',  $BMD_header_index)
			->where('BMD_identity_index', $session->BMD_identity_index)
			->find();
		if ( ! $session->transcribe_header )
			{
				$session->set('message_2', 'Invalid header, please select again.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// update header current action with selected next action
		$data =	[
							'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
						];
		$header_model->update($BMD_header_index, $data);
		// set current header for highlight in list
		$session->set('current_header_index', $session->transcribe_header[0]['BMD_header_index']);
		// get image parameters
		$session->set('panzoom_x', $session->transcribe_header[0]['BMD_panzoom_x']);
		$session->set('panzoom_y', $session->transcribe_header[0]['BMD_panzoom_y']);
		$session->set('panzoom_z', $session->transcribe_header[0]['BMD_panzoom_z']);
		$session->set('sharpen', $session->transcribe_header[0]['BMD_sharpen']);
		$session->set('image_x', $session->transcribe_header[0]['BMD_image_x']);
		$session->set('image_y', $session->transcribe_header[0]['BMD_image_y']);
		$session->set('scroll_step', $session->transcribe_header[0]['BMD_image_scroll_step']);
		$session->set('image_r', $session->transcribe_header[0]['BMD_image_rotate']);
		// get current font parameters
		$session->set('enter_font_family', $session->transcribe_header[0]['BMD_font_family']);
		$session->set('enter_font_style', $session->transcribe_header[0]['BMD_font_style']);
		$session->set('enter_font_size', $session->transcribe_header[0]['BMD_font_size']);
		// get current field parameters
		$session->set('enter_font_family', $session->transcribe_header[0]['BMD_font_family']);
		$session->set('enter_font_style', $session->transcribe_header[0]['BMD_font_style']);
		$session->set('enter_font_size', $session->transcribe_header[0]['BMD_font_size']);
		// perform action selected
		switch ($session->BMD_cycle_code) 
			{
				case 'NONE': // nothing was selected
					$session->set('message_2', 'Please select an action to perform from the dropdown.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/transcribe_step1/2') );
					break;
				case 'INPRO': // in progress
					// get allocation
					$session->transcribe_allocation = $allocation_model	
						->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])
						->where('BMD_identity_index', $session->BMD_identity_index)
						->find();
					$session->remove('feh_show');
					if ( ! $session->transcribe_allocation )
						{
							$session->set('message_2', 'Invalid allocation, please select again in transcribe/transcribe_next_action. Send email to '.$session->linbmd2_email);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('transcribe/transcribe_step1/2') );
						}
					// test scan exists in scan library
					if ( ! file_exists(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/Scans/'.$session->transcribe_header[0]['BMD_scan_name']) )
						{
							$session->set('message_2', 'webBMD cannot find the scan for this transcription in your scan library.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return redirect()->to( base_url('transcribe/transcribe_step1/2') );
						}
					// test scan corrupted
					if ( ! getimagesize(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/Scans/'.$session->transcribe_header[0]['BMD_scan_name']) )
						{
							$session->set('message_2', 'webBMD cannot open the scan for this transcription in your scan library. It does not appear to be an image.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('message_error', 'error');
							return redirect()->to( base_url('transcribe/transcribe_step1/2') );
						}
					// redirect to controller for the type
					switch ($session->transcribe_allocation[0]['BMD_type']) 
						{
							case 'B':
								return redirect()->to( base_url('births/transcribe_births_step1/0') );
								break;
							case 'M':
								return redirect()->to( base_url('marriages/transcribe_marriages_step1/0') );
								break;
							case 'D':
								return redirect()->to( base_url('deaths/transcribe_deaths_step1/0') );
								break;
							default:
						}
				case 'UPBMD': // upload BMD file
					return redirect()->to( base_url('transcribe/upload_BMD_file/'.$BMD_header_index) );
					break;
				case 'UPDET': // show upload return message
					return redirect()->to( base_url('transcribe/submit_details/'.$BMD_header_index) );
					break;
				case 'CLOST': // close BMD file
					$session->set('close_header', 'N');
					return redirect()->to( base_url('transcribe/close_header_step1/'.$BMD_header_index) );
					break;
				case 'VERIT': // verify transcription file
					$session->set('message_2', 'Sorry, Verification function is not available at present. Please try later.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('transcribe/transcribe_step1/2') );
					//return redirect()->to( base_url('transcribe/verify_BMD_trans_step1/'.$BMD_header_index) );
					break;
				case 'CRBMD': // create BMD file only, no upload
					return redirect()->to( base_url('transcribe/store_BMD_file/'.$BMD_header_index) );
					break;
				case 'VEBMD': // show raw BMD file
					return redirect()->to( base_url('transcribe/show_raw_BMD_file/'.$BMD_header_index) );
					break;
				case 'UPDBM': // send BMD file to syndicate leader
					return redirect()->to( base_url('transcribe/send_BMD_file_to_syndicate_leader/'.$BMD_header_index) );
					break;
			}							
	}
	
	public function create_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		$identity_model = new Identity_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		$detail_data_model = new Detail_Data_Model();
		$detail_comments_model = new Detail_Comments_Model();
		// session trancribe header already exists, now get other stuff
		// get allocation
		$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find();
		if ( ! $session->transcribe_allocation )
			{
				$session->set('message_2', 'Invalid allocation in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get syndicate
		$session->transcribe_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->transcribe_header[0]['BMD_syndicate_index'])->find();
		if ( ! $session->transcribe_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate inTranscribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get identity
		$session->transcribe_identity = $identity_model->where('BMD_identity_index',  $session->transcribe_header[0]['BMD_identity_index'])->find();
		if ( ! $session->transcribe_identity )
			{
				$session->set('message_2', 'Invalid identity in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get detail data
		$session->detail_data = $detail_data_model
			->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
			->where('BMD_identity_index', $session->BMD_identity_index)
			->findAll();
		if ( ! $session->detail_data )
			{
				$session->set('message_2', 'No detail data found for this header. Have you completed transcribing the scan?');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// build BMD file header lines
		// build file path/name
		$BMD_file = getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD';
		// test BMD file exists, delete it if so, else open it.
		if ( file_exists($BMD_file) === true )
			{
				unlink($BMD_file);
			}
		// create and open file in append mode
		$fp = fopen($BMD_file, 'a');
		if ( $fp === false )
			{
				$session->set('message_2', 'Cannot create BMD file in Transcribe/create_BMD_file. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// write header lines to file
		// first header line, eg +INFO,dreamstogo@gmail.com,Password,SEQUENCED,BIRTHS
		$write_line = "+INFO,".$session->transcribe_identity[0]['BMD_email'].",Password,SEQUENCED,".$session->types_upper[$session->transcribe_allocation[0]['BMD_type']]."\r\n";
		fwrite($fp, $write_line);
		// second header line, eg #,99,dreamstogo,Richard Oliver,1988BD0430.BMD,04-Aug-2020,Y,N,N,D,0,8.2
		$write_line = "#,99,".$session->transcribe_identity[0]['BMD_user'].",".$session->transcribe_syndicate[0]['BMD_syndicate_name'].",".$session->transcribe_header[0]['BMD_file_name'].".BMD,".$session->transcribe_header[0]['BMD_start_date'].",Y,N,N,".$session->transcribe_allocation[0]['BMD_letter'].",0,8.2\r\n";
		fwrite($fp, $write_line);
		// third header line, eg #,
		$write_line = "#,\r\n";
		fwrite($fp, $write_line);
		// fourth header line, eg +S,1988,,GUS/1988/Births/OFHS-03,05-Aug-2020 or
		// fourth header line, eg +S,1988,Sep,GUS/1988/Births/OFHS-03,05-Aug-2020 if quarter based
		// look for quarter
		$exploded_scan_path = explode('/', $session->transcribe_allocation[0]['BMD_reference']);
		$quarter_number = array_search($exploded_scan_path[3], $session->quarters_short_long);
		// explode BMD_reference to get third segment
		$scan_reference = explode('/', $session->transcribe_allocation[0]['BMD_reference']);
		// write line
		if ( $quarter_number ) 
			{
				$write_line = "+S,".$session->transcribe_allocation[0]['BMD_year'].",".$session->quarters[$quarter_number].",".$scan_reference[3].",".$session->current_date."\r\n";
			}
		else
			{
				$write_line = "+S,".$session->transcribe_allocation[0]['BMD_year'].",,".$scan_reference[3].",".$session->current_date."\r\n";
			}
		fwrite($fp, $write_line);
		// fifth header line, eg +CREDIT,Hilary Wright,dreamstogo@gmail.com,dreamstogo, only if syndicate leader allows this.
		if ( $session->transcribe_syndicate[0]['BMD_syndicate_credit'] == 'Y' )
			{
				$write_line = "+CREDIT,".$session->transcribe_identity[0]['BMD_realname'].",".$session->transcribe_identity[0]['BMD_email'].",".$session->transcribe_identity[0]['BMD_user']."\r\n";
				fwrite($fp, $write_line);
			}
		// current page line, eg +PAGE,0430
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':
					if ( $session->transcribe_allocation[0]['BMD_year'] <= 1992 )
						{
							// 1992 and before
							$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_current_page'],4,"0",STR_PAD_LEFT).$session->transcribe_header[0]['BMD_current_page_suffix']."\r\n";
						}
					else
						{
							// after 1992
							$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_current_page'],3,"0",STR_PAD_LEFT).$session->transcribe_header[0]['BMD_current_page_suffix']."\r\n";
						}
					break;
				case 'M':
					$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_current_page'],4,"0",STR_PAD_LEFT).$session->transcribe_header[0]['BMD_current_page_suffix']."\r\n";
					break;
				case 'D':
					$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_current_page'],4,"0",STR_PAD_LEFT).$session->transcribe_header[0]['BMD_current_page_suffix']."\r\n";
					break;
				default:
					break;
			}
		fwrite($fp, $write_line);
		
		// detail lines eg  for BIRTHS  => DUNN,CHRISTOPHER DAVID,BROWN,S SHIELDS,09.88,2,1403
		// detail lines eg  for MARRIAGES  => DUNN,CHRISTOPHER DAVID,BROWN,S SHIELDS,02.90,2,1403
		// detail lines eg for DEATHS => DUNN,CHRISTOPHER DAVID,06 AP 1920,S SHIELDS,02.90,2,1403
		foreach ( $session->detail_data as $dd )
			{
				// prepare given names ie concatenate all given names to one field
				$given_names = $dd['BMD_firstname'];
				if ( $dd['BMD_secondname'] != '' )
					{
						$given_names = $given_names." ".$dd['BMD_secondname'];
					}
				if ( $dd['BMD_thirdname'] != '' )
					{
						$given_names = $given_names." ".$dd['BMD_thirdname'];
					}
				
				// now detail lines depend on BMD type and BMD year
				switch ($session->transcribe_allocation[0]['BMD_type']) 
					{
						case "B": // births
							if ($session->transcribe_allocation[0]['BMD_year'] <= 1992 )
								{
									// 1992 and before
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_registration'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
								}
							else
								{
									// after 1992
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_reg'].",".$dd['BMD_entry'].",".$dd['BMD_registration']."\r\n";
								}
							break;
						case "M": // marriages
							if ($session->transcribe_allocation[0]['BMD_year'] <= 1993 )
								{
									// 1993 and before
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_registration'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
								}
							else
								{
									// after 1993
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_partnername'].",".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_registration'].",".$dd['BMD_page'].",".$dd['BMD_entry'].",".$dd['BMD_source_code']."\r\n";
								}
							
							break;
						case "D": // deaths
							if ($session->transcribe_allocation[0]['BMD_year'] <= 1992 )
								{
									// 1992 and before
									if ( $dd['BMD_age'] == 999 )
										{
											$write_line = $dd['BMD_surname'].",".$given_names.",,".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
										}
									else
										{
											$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_age'].",".$dd['BMD_district'].",".$dd['BMD_registration'].",".$dd['BMD_volume'].",".$dd['BMD_page']."\r\n";
										}
								}
							else
								{
									// after 1992
									$write_line = $dd['BMD_surname'].",".$given_names.",".$dd['BMD_age'].",".$dd['BMD_district'].",".$dd['BMD_volume'].",".$dd['BMD_reg'].",".$dd['BMD_entry'].",".$dd['BMD_registration']."\r\n";
								}
							break;
					}
				fwrite($fp, $write_line);
				
				// any comments?
				// get the comment lines and load fields
				$session->set('detail_comments', $detail_comments_model	
					->where('BMD_line_index', $dd['BMD_index'])
					->where('BMD_identity_index', $session->BMD_identity_index)
					->where('BMD_header_index', $session->transcribe_header[0]['BMD_header_index'])
					->find());
				// any found
				if ( $session->detail_comments )
					{
						// process line comment by line comment
						foreach ( $session->detail_comments as $dc )
							{
								switch ($dc['BMD_comment_type'])
									{
										case 'C':
											// eg #COMMENT(5) reads DUNKLEY or HART for mother's name
											$write_line = "#COMMENT(".$dc['BMD_comment_span'].") ".$dc['BMD_comment_text']."\r\n";
											break;
										case 'T':
											$write_line = "#THEORY(".$dc['BMD_comment_span'].") ".$dc['BMD_comment_text']."\r\n";
											break;
										case 'N':
											$write_line = "#(".$dc['BMD_comment_span'].") ".$dc['BMD_comment_text']."\r\n";
											break;
										case 'B':
											$write_line = "+BREAK\r\n";
											break;
									}
								fwrite($fp, $write_line);
							}
					}				
			}
		// next page line, eg +PAGE,0430 or +PAGE,002 if births and after 1992
		switch ($session->transcribe_allocation[0]['BMD_type'])
			{
				case 'B':
					if ( $session->transcribe_allocation[0]['BMD_year'] <= 1992 )
						{
							// 1992 and before
							$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_next_page'],4,"0",STR_PAD_LEFT)."\r\n";
						}
					else
						{
							// after 1992
							$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_next_page'],3,"0",STR_PAD_LEFT)."\r\n";
						}
					break;
				case 'M':
					$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_next_page'],4,"0",STR_PAD_LEFT)."\r\n";
					break;
				case 'D':
					$write_line = "+PAGE,".str_pad($session->transcribe_header[0]['BMD_next_page'],4,"0",STR_PAD_LEFT)."\r\n";
					break;
				default:
					break;
			}
					
		fwrite($fp, $write_line);
		// close the file
		fclose($fp);	
	}
	
	public function store_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$identity_model = new Identity_Model();
		$allocation_model = new Allocation_Model();
		// create the BMD file
		$this->create_BMD_file($BMD_header_index);
		// show message
		$session->set('message_2', 'BMD file successfully created. Use Show raw BMD file to see it.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
		
	public function show_raw_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$identity_model = new Identity_Model();
		$allocation_model = new Allocation_Model();
		$session->set('message_error', '');
		$session->set('message_2', '');
		$session->set('message_class_2', '');
		
		// test file exists
		if ( ! file_exists(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD') )
			{ 
				$session->set('message_2', 'The BMD file for this header does not exist. Please create it first if you want to read it.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// show the raw file BMD file
		$session->set('message_1', 'Here is the raw BMD file you requested = '.$session->transcribe_header[0]['BMD_file_name'].'.BMD'. '. You cannot change it here.');
		$session->set('message_class_1', 'alert alert-primary');
		$session->set('show_view_type', 'show_raw_BMD');
		return redirect()->to( base_url('transcribe/transcribe_step1/1') );
	}
	
	public function send_BMD_file_to_syndicate_leader($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$identity_model = new Identity_Model();
		$allocation_model = new Allocation_Model();
		$syndicate_model = new Syndicate_Model();
		// test file exists
		if ( ! file_exists(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD') )
			{ 
				$session->set('message_2', 'The BMD file for this header does not exist. Please create it first if you want to send it to your syndicate leader.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// send email
		$session->transcribe_syndicate = $syndicate_model->where('BMD_syndicate_index',  $session->transcribe_header[0]['BMD_syndicate_index'])->find();
		if ( ! $session->transcribe_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate in Transcribe/send_BMD_file_to_syndicate_leader. Send an email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		$this->send_email('BMD_file');
	}
	
	public function upload_BMD_file($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$identity_model = new Identity_Model();
		$allocation_model = new Allocation_Model();
		// create the BMD upload file
		$this->create_BMD_file($BMD_header_index);
		// does BMD file already exist on FreeBMD?
		BMD_file_exists_on_FreeBMD($session->transcribe_header[0]['BMD_file_name']);
		// create the curl file
		$cfile = curl_file_create(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD', $session->transcribe_header[0]['BMD_file_name']);
		// set up the fields to pass
		switch ($session->BMD_file_exists_on_FreeBMD)
		{
			case '0': // file does not already exist on FreeBMD
				$postfields = array(
												"UploadAgent" => $session->uploadagent,
												"user" => $session->user[0]['BMD_user'],
												"password" => $session->user[0]['BMD_password'],
												"file" => $session->transcribe_header[0]['BMD_file_name'],
												"content2" => $cfile,
												"data_version" => "districts.txt:??"
												);
				break;
			case '1': // file already exists on FreeBMD
				$postfields = array(
												"UploadAgent" => $session->uploadagent,
												"user" => $session->user[0]['BMD_user'],
												"password" => $session->user[0]['BMD_password'],
												"file_update" => $session->transcribe_header[0]['BMD_file_name'],
												"content2" => $cfile,
												"data_version" => "districts.txt:??"
												);
				break;
		}
		
		// set up the curl - $session->curl_url is set in home
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
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Transcribe::upload_BMD_file, around line 381 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				$session->set('view', 1);
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// close the curl handle and file handle
		curl_close($ch);
		fclose($fp);
		// check results
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "fileupload result") !== FALSE)
				{
					$upload_status = explode("=", $buffer);
					// test status
					$upload_status[1] = rtrim($upload_status[1]);
					switch ($upload_status[1]) 
					{
						case "OK":
							// update header
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							
							// update allocation with last page uploaded
							$data =	[
												'BMD_last_uploaded' => $session->transcribe_header[0]['BMD_current_page']
											];
							$allocation_model->update($session->transcribe_header[0]['BMD_allocation_index'], $data);
							
							// action depending on whether UPLOAD or REPLACE
							switch ($session->BMD_file_exists_on_FreeBMD)
								{
									case '0': // file did not already exist on FreeBMD
										$session->set('message_2', 'BMD file successfully UPLOADED to FreeBMD.');
										// update total number ever transcribed by this user
										$header = $header_model->where('BMD_header_index', $BMD_header_index)
													->where('BMD_identity_index', $session->BMD_identity_index)
													->find();
										$data =	[
												'BMD_total_records' => $session->user[0]['BMD_total_records'] + $header[0]['BMD_records']
												];
										$identity_model->update($session->user[0]['BMD_identity_index'], $data);
										break;
									case '1': // file already existed on FreeBMD
										$session->set('message_2', 'BMD file successfully REPLACED on FreeBMD.');
										break;
								}
							$session->set('message_class_2', 'alert alert-success');
							break;
						case "failed":
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							//
							$session->set('message_2', 'BMD file upload failed. See errors message by clicking on the status of the file concerned.');
							$session->set('message_class_2', 'alert alert-danger');
							break;
						case "warnings":
							$data =	[
												'BMD_submit_date' => $session->current_date,
												'BMD_submit_status' => $upload_status[1],
												'BMD_submit_message' => file_get_contents(getcwd()."/curl_result.txt"),
											];
							$header_model->update($BMD_header_index, $data);
							// update allocation with last page uploaded
							$data =	[
												'BMD_last_uploaded' => $session->transcribe_header[0]['BMD_current_page']
											];
							$allocation_model->update($session->transcribe_header[0]['BMD_allocation_index'], $data);
							// action depending on whether UPLOAD or REPLACE
							switch ($session->BMD_file_exists_on_FreeBMD)
								{
									case '0': // file did not already exist on FreeBMD
										$session->set('message_2', 'BMD file successfully UPLOADED to FreeBMD but with warnings. See warnings by clicking on the status of the file concerned.');
										// update total number ever transcribed by this user
										$header = $header_model->where('BMD_header_index', $BMD_header_index)
													->where('BMD_identity_index', $session->BMD_identity_index)
													->find();
										$data =	[
												'BMD_total_records' => $session->user[0]['BMD_total_records'] + $header[0]['BMD_records']
												];
										$identity_model->update($session->user[0]['BMD_identity_index'], $data);
										break;
									case '1': // file already existed on FreeBMD
										$session->set('message_2', 'BMD file successfully REPLACED on FreeBMD but with warnings. See warnings by clicking on the status of the file concerned');
										break;
								}
							$session->set('message_class_2', 'alert alert-warning');
							break;
					}
				}
		}		
				
		// all done
		// close the file handle
		fclose($fp);
		
		// redirect
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function submit_details($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// show upload details for this header																				
		echo view('templates/header');
		echo view('linBMD2/transcribe_submit_details');
		echo view('templates/footer');
	}
	
	public function close_header_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// can I close this file = if not uploaded successfully
		if ( $session->transcribe_header[0]['BMD_submit_date'] == '' OR $session->transcribe_header[0]['BMD_submit_status'] == 'failed' )
			{
				$session->set('message_2', 'This file has not been uploaded or it was not uploaded successfully. Normally you should not close it.');
				$session->set('message_class_2', 'alert alert-danger');
			}
		else
			{
				$session->set('message_2', 'Please confirm close of this BMD file.');
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
		$header_model = new Header_Model();
		// get inputs
		$session->set('close_header', $this->request->getPost('close_header'));
		// test for close
		if ( $session->close_header == 'N' )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'You did not confirm close. This file is still open.');
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		else
			{
				$data =	[
									'BMD_end_date' => $session->current_date,
									'BMD_header_status' => '1',
									'BMD_last_action' => $session->BMD_cycle_text[0]['BMD_cycle_name'],
								];
				$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'BMD file has been closed successfully.');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
	}
	
	public function verify_BMD_file_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		// get header
		$header = $header_model->where ('BMD_header_index', $BMD_header_index)
								->where('BMD_identity_index', $session->BMD_identity_index)
								->find();
		if ( ! $header )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'A problem occurred in Transribe::verify_BMD_file_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// load bmd file to array
		$session->set('verify_BMD_file', file(getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$header[0]['BMD_file_name'].'.BMD'));
		// show file
		$session->set('show_view_type', 'verify_BMD');
		$session->set('message_2', 'Verify BMD that will be uploaded to FreeBMD for this scan. To change data go to transcribe from scan option.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );	
	}
	
	public function verify_BMD_trans_step1($BMD_header_index)
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$detail_data_model = new Detail_Data_Model();
		$allocation_model = new Allocation_Model();
		// get header
		$session->transcribe_header = $header_model->where ('BMD_header_index', $BMD_header_index)
													->where('BMD_identity_index', $session->BMD_identity_index)
													->find();
		if ( ! $session->transcribe_header )
			{
				$session->set('show_view_type', 'transcribe');
				$session->set('message_2', 'A problem occurred in Transribe::verify_BMD_trans_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get allocation
		$session->transcribe_allocation = $allocation_model->where('BMD_allocation_index',  $session->transcribe_header[0]['BMD_allocation_index'])
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find();
		if ( ! $session->transcribe_allocation )
			{
				$session->set('message_2', 'Invalid allocation, please select again in transcribe/verify_BMD_trans_step1. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// get detail data
		$session->transcribe_detail_data = $detail_data_model	->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
																->where('BMD_identity_index', $session->BMD_identity_index)
																->orderby('BMD_line_sequence', 'ASC')
																->findAll();
		if ( ! $session->transcribe_detail_data )
			{
				$session->set('message_2', 'No detail found to verify!');
				$session->set('message_class_2', 'alert alert-warning');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// show file
		$session->set('show_view_type', 'verify_trans');
		$session->set('message_2', 'Verify the transcription data before creating the BMD file that will be uploaded to FreeBMD for this scan. To change data go to transcribe from scan option.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );	
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
		switch ($session->transcribe_allocation[0]['BMD_type']) 
			{
				case 'B':
					$registration = explode('.', $session->registration);
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = $session->month_to_quarter[$registration[0]];
					break;
				case 'M':
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
				case 'D':
					$year = $session->transcribe_allocation[0]['BMD_year'];
					$quarter = str_pad($session->transcribe_allocation[0]['BMD_quarter'], 2, '0', STR_PAD_LEFT);
					break;
			}
		// find volume range
		foreach ( $results as $result )
			{
				$volumes =  $volumes_model
							->where('district_index', $result['district_index'])
							->where('BMD_type', $session->transcribe_allocation[0]['BMD_type'])
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
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching district
		$results = $districts_model	->like('District_name', $search_term, 'after')
														->findAll();
		// prepare return array
		$search_result = array();
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
		// get search term
		$request = \Config\Services::request();
		$search_term = $request->getPostGet('term');
		// get matching volumes join to districts master
		$results = $volumes_model	
			->like('volume', $search_term, 'after')
			->join('districts_master', 'volumes.district_index = districts_master.district_index')
			->where('BMD_type', $session->transcribe_allocation[0]['BMD_type'])
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
		// get matching firstname
		$results = $firstname_model		->like('Firstname', $search_term, 'after')
															->orderby('Firstname_popularity', 'DESC')
															->findAll();
		// prepare return array
		$search_result = array();
		foreach($results as $result)
			{
				$search_result[] = $result['Firstname'];
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
					$session->set('message_1', 'Set the horizontal, vertical image size, and scroll_step to suit your requirements for this image.');
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
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function image_parameters_step2($BMD_header_index)
	{
		// initialise
		$session = session();
		$header_model = new Header_Model();
		// get inputs
		$session->set('image_y', $this->request->getPost('image_height'));
		$session->set('scroll_step', $this->request->getPost('image_scroll_step'));
		$session->set('image_r', $this->request->getPost('image_rotate'));
		// do tests
		// height
		if ( $session->image_y == '' OR $session->image_y == '0' OR is_numeric($session->image_y) === false OR  $session->image_y < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image HEIGHT cannot be blank, zero, non_numeric or less than zero.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// scroll step
		if ( $session->scroll_step == '' OR $session->scroll_step == '0' OR is_numeric($session->scroll_step) === false OR  $session->scroll_step < 0 )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image SCROLL STEP cannot be blank, non_numeric or negative');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
		// rotate
		if ( $session->image_r == '' OR is_numeric($session->image_r) === false )
			{
				$session->set('show_view_type', 'image_parameters');
				$session->set('message_2', 'Image ROTATE cannot be blank, non_numeric. It can be negative for rotate left.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// all good
		// update header
		$data =	[
							'BMD_image_y' => $session->image_y,
							'BMD_image_x' => '',
							'BMD_image_scroll_step' => $session->scroll_step,
							'BMD_image_rotate' => $session->image_r,
						];
		$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
		// reload header 
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
													->where('BMD_identity_index', $session->BMD_identity_index)
													->find();
		// get image parameters
		$session->set('panzoom_x', $session->transcribe_header[0]['BMD_panzoom_x']);
		$session->set('panzoom_y', $session->transcribe_header[0]['BMD_panzoom_y']);
		$session->set('panzoom_z', $session->transcribe_header[0]['BMD_panzoom_z']);
		$session->set('sharpen', $session->transcribe_header[0]['BMD_sharpen']);
		$session->set('image_x', $session->transcribe_header[0]['BMD_image_x']);
		$session->set('image_y', $session->transcribe_header[0]['BMD_image_y']);
		$session->set('scroll_step', $session->transcribe_header[0]['BMD_image_scroll_step']);
		$session->set('image_r', $session->transcribe_header[0]['BMD_image_rotate']);
		// reset image
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function enter_parameters_step1($start_message)
	{
		// initialise
		$session = session();
		//set defaults
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Set the field widths, font family, font size for the data entry matrix.');
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
		$session->set('show_view_type', 'enter_parameters');
		$session->set('message_2', 'Current data entry parameters are shown.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function enter_parameters_step2($BMD_header_index)
	{
		// initialise
		$session = session();
		$header_model = new Header_Model();
		// get inputs
		$session->set('enter_font_family', $this->request->getPost('enter_font_family'));
		$session->set('enter_font_size', $this->request->getPost('enter_font_size'));
		$session->set('enter_font_style', $this->request->getPost('enter_font_style'));
		// do tests
		// font size
		if ( $session->enter_font_size == '' OR $session->enter_font_size == '0' OR is_numeric($session->enter_font_size) === false OR  $session->enter_font_size< 0 )
			{
				$session->set('show_view_type', 'enter_parameters');
				$session->set('message_2', 'FONT SIZE cannot be blank, non_numeric or negative');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('transcribe/transcribe_step1/2') );
			}
			
		// all good
		// update header
		$data =	[
							'BMD_font_family' => $session->enter_font_family,
							'BMD_font_size' => $session->enter_font_size,
							'BMD_font_style' => $session->enter_font_style,
						];
		$header_model->update($session->transcribe_header[0]['BMD_header_index'], $data);
		// reload header 
		$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
													->where('BMD_identity_index', $session->BMD_identity_index)
													->find();
		// get image parameters
		$session->set('enter_font_family', $session->transcribe_header[0]['BMD_font_family']);
		$session->set('enter_font_size', $session->transcribe_header[0]['BMD_font_size']);
		$session->set('enter_font_style', $session->transcribe_header[0]['BMD_font_style']);
		
		// reset data entry
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function delete_line_step1($line_index)
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
	
	public function delete_line_step2()
	{
		// initialse
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		$header_model = new Header_Model();
		// get input
		$session->set('delete_ok', $this->request->getPost('confirm'));
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
				$session->transcribe_header = $header_model->where('BMD_header_index',  $session->transcribe_header[0]['BMD_header_index'])
															->where('BMD_identity_index', $session->BMD_identity_index)
															->find();
			}
		// return
		return redirect()->to( base_url($session->return_route_step1) );
	}
	
	public function send_email($email_type)
	{
		// initialise
		$session = session();
		//require '../vendor/autoload.php';
		$mail = new PHPMailer();
		$result = '';
		$result_dump = '';
		$mail->isSMTP();
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mail->Host = 'mail.mailo.com';
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Username = 'linBMD2@mailo.com';
		$mail->Password = 'LdpKR0vyLq4gVn_PU2_0';
		$mail->Port = 587;
		$mail->isHTML(true);
		//$mail->addBCC('linBMD2@mailo.com');
		//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		
		// set up message specific parameters
		switch ($email_type) 
			{
				case 'allocation':
					$mail->addBCC('linBMD2@mailo.com');
					$leader = explode(' ', $session->transcribe_syndicate[0]['BMD_syndicate_leader']);
					$mail->setFrom($session->user[0]['BMD_email']);
					$mail->addAddress($session->transcribe_syndicate[0]['BMD_syndicate_email']);
					$mail->addReplyTo($session->user[0]['BMD_email']);
					$mail->Subject = 'Message from linBMD2 - Allocation '.$session->transcribe_allocation[0]['BMD_allocation_name'].' completed';
					$mail->Body = 	'<html>Hello '
												.$leader[0]
												.','
												.'<br><br>I completed the allocation '
												.'<b>'
												.$session->transcribe_allocation[0]['BMD_allocation_name']
												.'</b>'
												.' on '
												.$session->transcribe_allocation[0]['BMD_end_date']
												.'.'
												.'<br><br>Please provide me with another allocation.'
												.'<br><br>Thank you.'
												.'<br><br>Best wishes,'
												.'<br><br>'
												.$session->user[0]['BMD_realname']
												.'<br><br>'
												.$session->user[0]['BMD_user'];
					$mail->AltBody = 'Allocation '.$session->transcribe_allocation[0]['BMD_allocation_name'].'completed';
					// set return message
					$session->set('message_2', 'An email was sent to the syndicate owner informing him/her that this allocation is complete and asking for another allocation.');
					break;
				case 'identity':
					$mail->setFrom('linBMD2@mailo.com', 'linBMD2 admin');
					$mail->addAddress($session->email);
					$mail->Subject = 'linBMD2 Email recovery.';
					$mail->Body 	= 	'<html>Hello '
												.$session->user[0]['BMD_realname']
												.'<br><br>Here is your linBMD2 password => <strong>'
												.$session->user[0]['BMD_password'].
												'</strong>.<br><br>Best Regards<br>linBMD2 Admin</html>';
					$mail->AltBody = 'Here is your linBMD2 password => '.$session->user[0]['BMD_password'];
					// set return message
					$session->set('message_2', 'An email has been sent to your email address with your password.');
					break;
				case 'BMD_file':
					$mail->addBCC('linBMD2@mailo.com');
					$leader = explode(' ', $session->transcribe_syndicate[0]['BMD_syndicate_leader']);
					$mail->setFrom($session->user[0]['BMD_email']);
					$mail->addAddress($session->transcribe_syndicate[0]['BMD_syndicate_email']);
					$mail->addReplyTo($session->user[0]['BMD_email']);
					$mail->Subject = 'Message from linBMD2 - BMD File transcription '.$session->transcribe_header[0]['BMD_file_name'].' completed';
					$mail->Body = 	'<html>'
												.'Hello '
												.$leader[0]
												.','
												.'<br><br>Please find attached the completed transcription file: '
												.'<b>'
												.$session->transcribe_header[0]['BMD_file_name']
												.'</b>'
												.' of the scanned image file:  '
												.$session->transcribe_header[0]['BMD_scan_name']
												.'.'
												.'<br><br>Please provide me with another scan.'
												.'<br><br>Thank you.'
												.'<br><br>Best regards,'
												.'<br><br>'
												.$session->user[0]['BMD_realname']
												.'<br><br>'
												.$session->user[0]['BMD_user']
												.'</html>';
					$mail->AltBody = 'Transcription '.$session->transcribe_header[0]['BMD_file_name'].' completed';
					$mail->AddAttachment( getcwd().'/Users/'.$session->user[0]['BMD_user'].'/BMD_Files/'.$session->transcribe_header[0]['BMD_file_name'].'.BMD', $session->transcribe_header[0]['BMD_file_name'].'.BMD' );
					// set return message
					$session->set('message_2', 'An email was sent to the syndicate owner informing him/her that this transcription is complete and asking for another scan to transcribe.');
					break;
			}
		// send the mail.
		if ( ! $mail->send() )
			{
				$result = 'Internal error ending email, contact '.$session->linbmd2_email;
				$result_dump =  $mail->ErrorInfo;
				$session->set('message_2', $result.' '.$result_dump.' => '. 'for message type = '.$email_type);
				$session->set('message_class_2', 'alert alert-warning');
				// show view
				echo view('templates/header');
				echo view('linBMD2/error');
				echo view('templates/footer');
			}
		else
			{	   
				// set message class
				$session->set('message_class_2', 'alert alert-success');
				// go back to designated route
				switch ($email_type) 
					{
						case 'allocation':
							return redirect()->to( base_url('allocation/manage_allocations/2') );
							break;
						case 'identity':
							return redirect()->to( base_url('identity/signin_step1/1') );
							break;
						case 'BMD_file':
							$this->transcribe_step1(2);
							break;
					}
			}		
	}
}
