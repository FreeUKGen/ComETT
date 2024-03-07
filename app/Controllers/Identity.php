<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Submitters_Model;				
use App\Models\Parameter_Model;
use App\Models\Detail_Data_Model;
use App\Models\Detail_Comments_Model;
use App\Models\Transcription_Detail_Def_Model;
use App\Models\Transcription_Model;
use App\Models\Allocation_Model;
use App\Models\Syndicate_Model;
use App\Models\Messaging_Model;
use App\Models\Roles_Model;
use App\Models\Projects_Model;
use App\Models\Signins_Model;

class Identity extends BaseController
{
	function __construct() 
	{
        helper('common');
    }
	
	public function signin_step1($start_message)
		{
			// initialise
			$session = session();
			$messaging_model = new Messaging_Model();
			$parameter_model = new Parameter_Model();
			
			// initialise message
			$session->set('message_1', 'Welcome, please sign in.');
			$session->set('message_class_1', 'alert alert-primary');
			
			// is javascript enabled?
			if ( $session->javascript == 'disabled' )
				{
					return redirect()->to( base_url('home/no_javascript') );
				}
			
			if ( $start_message == 0 )
				{					
					// initialise
					if ( $session->session_expired == 1 )
						{
							$session->set('message_2', 'Your session has expired - Time out. Please sign in again to continue.');
							$session->set('message_class_2', 'alert alert-danger');
							$session->set('session_expired', 0);
						}
					else
						{
							$session->set('message_2', '');
							$session->set('message_class_2', '');
							
							// get today date
							$today = date("Y-m-d");
							// get message to show
							$session->current_message =	$messaging_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('from_date <=', $today)
								->where('to_date >=', $today)
								->find();
							// set show message if any found
							if ( $session->current_message )
								{
									$session->show_message = 'show';
								}
							else
								{
									$session->show_message = '';
								}
								
							// get version from parameters
							$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
							$session->set('version', $parameter[0]['Parameter_value']);
							
							// load linbmd2 email
							$parameter = $parameter_model->where('Parameter_key', 'linbmd2_email')->findAll();
							$session->set('linbmd2_email', $parameter[0]['Parameter_value']);
						}
				}
			
			// show view
			echo view('templates/header');
			echo view('linBMD2/signin');
		}
	
	public function signin_step2()
	{
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		$syndicate_model = new Syndicate_Model();
		$submitters_model = new Submitters_Model();
		$parameter_model = new Parameter_Model();
		$session->signon_success = 0;
		
		// what OS is this?
		$agent = $this->request->getUserAgent();
		$currentPlatform = $agent->getPlatform();
		
		// build / update FreeComETT syndicate DB ; manage_syndicate_DB() in common_helper
		// frequency depends on parameter in Projects table, and can be different per project
		// calculate number of signons / syndicate refresh frequency ; get remainder
		$update = $session->current_project[0]['signons_to_project'] % $session->current_project[0]['syndicate_refresh'];
		if ( $update == 0 )
			{
				manage_syndicate_DB();
			}
			
		// get input and set session fields
		$session->set('identity_userid', $this->request->getPost('identity'));
		$session->set('identity_password', $this->request->getPost('password'));
		$session->actual_x = $this->request->getPost('actual_x');
		$session->actual_y = $this->request->getPost('actual_y');
		
		// validate depending on project
		switch ($session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':
					// find identity entered by user
					$db = \Config\Database::connect($session->project_DB);
					$sql = 	"
								SELECT * 
								FROM Submitters 
								WHERE UserID = '".$session->identity_userid."'
							";
					$query = $db->query($sql);
					$session->submitter = $query->getResultArray();	
				
					// was it found?
					if ( ! $session->submitter )
						{
							$session->set('message_2', 'The identity you entered is not defined for '.$session->current_project[0]['project_name'].'. Please ensure that you have entered your Identity correctly.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}

					// hash the entered password
					$UserPassword_hash = hash_hmac('md5', $this->request->getPost('password'), $session->current_project[0]['hmac_key'], true);
					$UserPassword_base64 = rtrim(base64_encode($UserPassword_hash), '=');
					
					// are hashes same?
					if ( $session->submitter[0]['Password'] != $UserPassword_base64 )
						{
							$session->set('message_2', 'The password you entered is not valid for your identity '.$this->request->getPost('identity').'. Please ensure that you have entered your Identity and password correctly.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}

					// is user active? except for me.
					if ( $session->submitter[0]['NotActive'] == 1 AND $this->request->getPost('identity') != 'dreamstogo' )
						{
							$get_date = getdate($session->submitter[0]['NotActiveDate']);
							$not_active_date = $get_date['mday'].'-'.$get_date['month'].'-'.$get_date['year'];
							$session->set('message_2', 'Your '.$session->current_project[0]['project_name'].' account has been suspended on '.$not_active_date.' for this reason, '.$session->submitter[0]['NotActiveReason'].'. Please contact your coordinator.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
					
					// get the syndicate(s) this user is attached to
					$session->syndicateID = '';
					$db1 = \Config\Database::connect($session->syndicate_DB);
					$sql =	"	
								SELECT * 
								FROM SyndicateMembers 
								JOIN SyndicateTable
								ON SyndicateTable.SyndicateID = SyndicateMembers.SyndicateID
								WHERE SyndicateMembers.UserID = '".$session->identity_userid."'
								ORDER BY SyndicateTable.SyndicateName
							";
					$query = $db1->query($sql);
					$session->project_user_syndicates = $query->getResultArray();
				
					// do syndicate checks				
					// any found?
					if ( ! $session->project_user_syndicates )
						{
							$session->set('message_2', 'You do not appear to be a member of any syndicates. Please contact your coordinator.');
							$session->set('message_class_2', '');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
						
					// multiple syndicates?
					if ( count($session->project_user_syndicates) > 1 )
						{
							// this method will set the $session->current_syndicate if there are mutiple syndicates
							// and return to signon_step_3
							return redirect()->to( base_url('identity/signin_select_syndicate') );
						}
					else
						{
							// set current syndicate if only one syndicate
							$session->current_syndicate = $syndicate_model
								->where('project_index', $session->current_project[0]['project_index'])
								->where('BMD_syndicate_index', $session->project_user_syndicates[0]['SyndicateID'])
								->find();
						}		
					break;
					
				case 'FreeREG':
					// $session->project_DB is defined on Projects.php and comes from the projects table
					// create mongodb client
					$client = new \MongoDB\Client($session->project_DB['DBDriver'].$session->project_DB['hostname'].':'.$session->project_DB['port']);
					// create database
					$database = $client->selectDatabase($session->project_DB['database']);
					// define userid_details collection (need curly brackets because of _ in collection name)
					$collection_userid = $database->{'userid_details'};
					// define syndicate collection
					$collection_syndicates = $database->{'syndicates'};
					
					// get the userid_details record for this transcriber
					$session->submitter = $collection_userid->find
						(
							[
								'userid' => $session->identity_userid
							]
						)->toArray();

					// was it found?
					if ( ! $session->submitter )
						{
							$session->set('message_2', 'The identity you entered is not defined for '.$session->current_project[0]['project_name'].'. Please ensure that you have entered your Identity correctly.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}

					// hash the entered password
					$UserPassword_hash = hash_hmac('md5', $this->request->getPost('password'), $session->current_project[0]['hmac_key'], true);
					$UserPassword_base64 = rtrim(base64_encode($UserPassword_hash), '=');
				
					// are hashes same?
					if ( $session->submitter[0]['password'] != $UserPassword_base64 )
						{
							$session->set('message_2', 'The password you entered is not valid for your identity '.$this->request->getPost('identity').'. Please ensure that you have entered your Identity and password correctly.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
						
					// is user active? except for me.
					if ( $session->submitter[0]['active'] == false AND $this->request->getPost('identity') != 'freeregdev' )
						{
							$session->set('message_2', 'Your '.$session->current_project[0]['project_name'].' account is not active
							. Please contact your coordinator.');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
												
					// get the syndicate(s) this user is attached to
					$session->project_user_syndicates = $collection_syndicates->find
						(
							[
								'syndicate_code' => $session->submitter[0]['syndicate']
							]
						)->toArray();
						
					// do syndicate checks				
					// any found?
					if ( ! $session->project_user_syndicates )
						{
							$session->set('message_2', 'You do not appear to be a member of any syndicates. Please contact your coordinator.');
							$session->set('message_class_2', '');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
						
					// multiple syndicates?
					// transcribers can only be in one syndicate in FreeREG
					
					// set current syndicate if only one syndicate
					$id = $session->project_user_syndicates[0]['_id']->__toString();
					$session->current_syndicate = $syndicate_model
						->where('project_index', $session->current_project[0]['project_index'])
						->where('BMD_syndicate_index', $id)
						->find();
					break;
					
				case 'FreeCEN':
					break;
			}	
				
		return redirect()->to( base_url('identity/signin_step3') );
	}

	public function signin_step3()
	{
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		$syndicate_model = new Syndicate_Model();
		$submitters_model = new Submitters_Model();
		$parameter_model = new Parameter_Model();
		$projects_model = new Projects_Model();
		$signins_model = new Signins_Model();
		
		// set flags
		$session->signon_success = 1;
		$new_user = 0;
		$session->masquerade = 0;
		
		// update number of signons this syndicate in order to know when to next update syndicates table
		$signons = $session->current_project[0]['signons_to_project'] + 1;
		$projects_model
			->where('project_index', $session->current_project[0]['project_index'])
			->set(['signons_to_project' => $signons])
			->update();

		// get user identity in FreeComETT by using the UserID
		$session->current_identity = $identity_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_user', $session->identity_userid)
			->find();
										
		// found?
		if ( ! $session->current_identity )
			{					
				// No?, so add it
				// set user defaults for this session, this syndicate
				// is this user a syndicate leader? default is no = ordinary transcriber
				$user_role = 4;
				switch ($session->current_project[0]['project_name']) 
					{
						case 'FreeBMD':	
							// is the person signing on a coordinator
							if ( $session->project_user_syndicates[0]['CoOrdinator'] == 'Y' )
								{
									$user_role = '2';
								}				
							break;
						
						case 'FreeREG':
							// is the person signing on a coordinator
							if ( $session->identity_userid == $session->project_user_syndicates[0]['syndicate_coordinator'] )
								{
									$user_role = '2';
								}
							break;
							
						case 'FreeCEN':
							break;
					}
				
				// user environment and verify flag
				$user_env = 'LIVE';
				$user_ver = 'onthefly';
				if ( $session->current_syndicate )
					{
						$user_env = $session->current_syndicate[0]['new_user_environment'];
						$user_ver = $session->current_syndicate[0]['verify_mode'];
					}
				
				// add record - most fields are provided by DB default definitions.
				$identity_model
					->set(['project_index' => $session->current_project[0]['project_index']])
					->set(['BMD_user' => $session->identity_userid])
					->set(['environment' => $user_env])
					->set(['verify_mode' => $user_ver])
					->set(['role_index' => $user_role])
					->insert();
		
				// create user folder and subfolders if they don't exist
				if ( ! is_dir(getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->identity_userid) )
					{ 
						if ( ! is_dir(getcwd().'/Users/'.$session->current_project[0]['project_name']) )
							{
								mkdir(getcwd().'/Users/'.$session->current_project[0]['project_name']);
							}
						mkdir(getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->identity_userid);
						mkdir(getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->identity_userid.'/Backups');
						mkdir(getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->identity_userid.'/CSV_Files');
						mkdir(getcwd().'/Users/'.$session->current_project[0]['project_name'].'/'.$session->identity_userid.'/Scans');
					}				
				
				// set_new user_flag
				$new_user = 1;
			}
		
		// signon is validated - WOW! At last!
		
		// get the identity
		$session->current_identity = $identity_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_user', $session->identity_userid)
			->find();
			
		// set identity session parms depending on project
		switch ($session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':	
					$session->identity_emailid = $session->submitter[0]['EmailID'];
					$session->realname = $session->submitter[0]['GivenName'].' '.$session->submitter[0]['Surname'];
					$session->total_records = $session->submitter[0]['TotalEntries'];
					break;
					
				case 'FreeREG':	
					$session->identity_emailid = $session->submitter[0]['email_address'];
					$session->realname = $session->submitter[0]['person_forename'].' '.$session->submitter[0]['person_surname'];
					$session->total_records = $session->submitter[0]['number_of_records'];
					break;
				
				case 'FreeREG':
					break;
			}
	
		// set others			
		$session->BMD_identity_index = $session->current_identity[0]['BMD_identity_index'];
		$session->data_entry_font = $session->current_identity[0]['default_dataentryfont'];
		$session->environment_user = $session->current_identity[0]['environment'];
		$session->role = $session->current_identity[0]['role_index'];
		$session->syndicate_name = $session->current_syndicate[0]['BMD_syndicate_name'];
		$session->syndicate_id = $session->current_syndicate[0]['BMD_syndicate_index'];
		
		// add record to signins this user
		$signins_model
			->set(['identity_index' => $session->BMD_identity_index])
			->set(['identity_role' => $session->role])
			->set(['syndicate_index' => $session->current_syndicate[0]['BMD_syndicate_index']])
			->set(['signin_x' => $session->actual_x])
			->set(['signin_y' => $session->actual_y])
			->insert();
		
		// is there an update in progress?
		$update_in_progress = $parameter_model->where('Parameter_key', 'updateinprogress')->find();
		// if update in progress and user role is not DBADMIN stop
		if ( $update_in_progress[0]['Parameter_value'] == 'YES' AND $session->current_identity[0]['role_index'] != 1 )
			{
				// update in progress message
				return redirect()->to( base_url('home/update_in_progress') );
			}	
										
		// Can I reach the image server and get an image?			
				
		// setup curl by trying to DL an image - any image will do
		switch ($session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':	
					$curl_url =	$session->current_project[0]['project_autoimageservertype']
								.$session->current_project[0]['project_autoimageurl']
								.'GUS/1870/Marriages/December/ANC-05/'
								.'1870M4-M-0185.jpg';
					$ch = curl_init($curl_url);
					curl_setopt($ch, CURLOPT_USERPWD, "$session->identity_userid:$session->identity_password");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
											
					// do the curl
					$curl_result = curl_exec($ch);
					curl_close($ch);
				
					// anything found
					if ( $curl_result == '' )
						{
							// problem so send error message
							$session->set('message_2', 'A technical problem occurred. Is your browser blocking access to images? Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to reach Image Server in Identity::signin_step2');
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/signin_step1/1') );
						}
				
					// load returned data to array
					$lines = preg_split("/\r\n|\n|\r/", $curl_result);
					
					// now test to see if a valid page was found
					foreach($lines as $line)
						{
							if ( strpos($line, "404 Not Found") !== false )
								{
									$session->set('message_2', 'A technical problem occurred. Please send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Malformed URL in Identity::signin_step2');
									$session->set('message_class_2', 'alert alert-danger');
									return redirect()->to( base_url('identity/signin_step1/1') );
								}
						}
					break;
					
				case 'FreeREG':
					break;
					
				case 'FreeCEN':	
					break;
			}
		
			
		// OK I can access image server
		
		// load global variables - function is in common helper
		load_variables();
		
		// A word about environment
		// The environment tells FreeComETT whether to use TEST or LIVE servers.
		// It can be set 
		// - at application level in the parameters table
		// - at project level in the projects table
		// - at user level in the identity table. A new user is always added as TEST until his coordinator moves him to LIVE.
		// Thus this is a hierachy
		// Global first, then project, then user
		
		// Get Global environment
		$parameter = $parameter_model->where('Parameter_key', 'environment')->find();
		$session->set('environment_global', $parameter[0]['Parameter_value']);
		
		// project environment is set in Projects controller = $session->environment_project
		// user environment is set in this method = $session->environment_user
		
		// set the $session->environment variable - default is LIVE
		$session->environment = 'LIVE';
		if ( $session->environment_global == 'TEST' )
			{
				$session->environment = $session->environment_global;
			}
		elseif ( $session->environment_project == 'TEST' )
			{
				$session->environment = $session->environment_project;
			}
		elseif ( $session->environment_user == 'TEST' )
			{
				$session->environment = $session->environment_user;
			}
		
		// set curl_url for upload
		switch ($session->environment) 
			{
				case 'LIVE':
					$session->set('curl_url', $session->current_project[0]['project_autouploadurllive']);
					break;
				case 'TEST':
					$session->set('curl_url', $session->current_project[0]['project_autouploadurltest']);
					break;
				default:
					$session->set('curl_url', $session->current_project[0]['project_autouploadurltest']);
					break;
			}
		
		// set open or closed transcription flag - in this case open
		$session->status = '0';
		// set open or closed allocation flag - in this case open
		$session->allocation_status = 'Open';

		// redirect
		if ( $new_user == 1 )
			{
				// show help if new user
				$session->set('message_2', 'Hello '.$session->realname.', welcome to FreeComETT! This is your first time here, so please start by reading the help. It will help you! If in doubt, choose the first option.');
				$session->set('message_class_2', 'alert alert-info');
								
				// send email to coordinator if new user
				return redirect()->to(base_url('email/send_email/new_user') );
				
				// redirection to help happens in the email function
			}
		else
			{				
				// show transcriptions
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				return redirect()->to( base_url('transcribe/transcribe_step1/0') );
			}
	}
	
	public function admin_user_step1($start_message)
	{		
		// initialise method
		$session = session();
		$roles_model = new Roles_Model();
		
		// set values
		switch ($start_message) 
			{
				case 0:
					// initialise values
					$session->set('admin_user', '');
					// message defaults
					$session->set('message_1', 'Change FreeComETT user role for FreeComETT user.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					// load rights
					$session->available_roles =	$roles_model
													->where('role_precedence >=', $session->current_identity[0]['role_index'])
													->orderby('role_precedence')
													->findAll();
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Change FreeComETT user role for FreeComETT user.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}	
	
		echo view('templates/header');
		echo view('linBMD2/admin_user');
		echo view('templates/footer');
	}
	
	public function admin_user_step2()
	{		
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('role_index', $this->request->getPost('role'));
		
		// find identity entered by user
		$identity = $model
					->where('project_index', $session->current_project[0]['project_index'])
					->where('BMD_user', $session->identity)
					->find();
		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'This Identity you entered is not registered in the current project.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/admin_user_step1/1') );
			}
			
		$data = [
					'role_index' => $session->role_index,
				];
		$model->update($identity[0]['BMD_identity_index'], $data);
				
		// go back
		$session->set('message_2', 'The user role has been changed.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/admin_user_step1/1') );
	}
	
	public function change_details_step2($start_message)
	{
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		
		// get identity record
		$session->current_identity = $identity_model
			->where('BMD_user', $session->submitter[0]['UserID'])
			->find();
			
		// set verify mode text
		switch ($session->current_identity[0]['verify_mode']) 
			{
				case 'after':
					$session->verify_mode_text = 'Verify after Transcription is complete using the Verify Module';
					break;
				case 'onthefly':
					$session->verify_mode_text = 'Verify line-by-line in the Transcription Module';
					break;
				default:
					$session->verify_mode_text = 'No Verify Mode specified';
					break;
			}
		
		if ( $start_message == 0 )
			{				
				$session->set('message_1', 'Change your Identity details for '.$session->current_project[0]['project_name'].'.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
			}
		
		// set inputs
		$session->set('identity', $session->submitter[0]['UserID']);			

		// show view
		$session->details_step = 3;
		echo view('templates/header');
		echo view('linBMD2/change_identity_step2');
	}	
	
	public function change_details_step3()
	{
		// initialise method
		$session = session();
		$identity_model = new Identity_Model();
		
		// get user data
		$session->set('verify_mode', $this->request->getPost('verify_mode'));
			
		// All good so update to database
		$data = [
					'verify_mode' => $session->verify_mode,
				];
		$identity_model->update($session->current_identity[0]['BMD_identity_index'], $data);
		
		// reload current identity and user
		$session->current_identity = 	$identity_model
										->where('project_index', $session->current_project[0]['project_index'])
										->where('BMD_identity_index', $session->BMD_identity_index)
										->findAll();
		
		// go back to transcribe home
		$session->set('message_2', 'Your Identity has been changed on FreeComETT for this project '.$session->current_project[0]['project_name'].'.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('transcribe/transcribe_step1/2') );
	}
	
	public function signin_select_syndicate()
	{
		// initialise method
		$session = session();
		
		// get syndicate
		$session->set('message_1', 'Please select the syndicate you want to work with the this FreeComETT session.');
		$session->set('message_class_1', 'alert alert-primary');
		$session->set('message_2', '');
		$session->set('message_class_2', '');

		echo view('templates/header');
		echo view('linBMD2/signin_select_syndicate');
	}
	
	public function signin_get_syndicate()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		
		// set current syndicate
		$session->current_syndicate = $syndicate_model
			->where('project_index', $session->current_project[0]['project_index'])
			->where('BMD_syndicate_index', $this->request->getPost('syndicate'))
			->find();
		
		// continue processing
		return redirect()->to( base_url('identity/signin_step3') );
	}
}
