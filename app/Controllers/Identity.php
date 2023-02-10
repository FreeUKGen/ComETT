<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Parameter_Model;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Identity extends BaseController
{
	public function signin_step1($start_message)
		{
			// initialise
			$session = session();
			
			// did Home controller run? If not run it 
			if (!$session->login_time_stamp)
				{
					return redirect()->to( base_url('home/index') );
				}
				
			// initialise message
			$session->set('message_1', 'Welcome, please sign in.');
			$session->set('message_class_1', 'alert alert-primary');
			
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
		$model = new Identity_Model();
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $this->request->getPost('identity'))->find();

		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'The identity you entered is not defined in FreeComETT. Use Create new FreeComETT identity. => '.$this->request->getPost('identity'));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/signin_step1/1') );
			}

		// test correct password
		if ( $this->request->getPost('password') != $identity[0]['BMD_password'] )
			{
				$session->set('message_2', 'Password is not correct for this identity.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/signin_step1/1') );
			}
				
		// all good - store identity index
		$session->set('BMD_identity_index', $identity[0]['BMD_identity_index']);
		$session->set('realname', $identity[0]['BMD_realname']);
		
		// create user folder and subfolders if they don't exist
		if ( ! is_dir(getcwd().'/Users/'.$identity[0]['BMD_user']) )
			{ 
				mkdir(getcwd().'/Users/'.$identity[0]['BMD_user']);
				mkdir(getcwd().'/Users/'.$identity[0]['BMD_user'].'/Backups');
				mkdir(getcwd().'/Users/'.$identity[0]['BMD_user'].'/BMD_Files');
				mkdir(getcwd().'/Users/'.$identity[0]['BMD_user'].'/Scans');
			}
		
		// redirect to transcribe
		return redirect()->to( base_url('transcribe/transcribe_step1/0') );
	}

	public function create_identity_step1($start_message)
	{
		// initialise
		$session = session();

		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Create your Identity in FreeComETT by entering the following information,');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('password', '');
				$session->set('realname', '');
				$session->set('email', '');
				$session->set('environment', '');
				$session->set('project_index', '');
				$session->set('default_dataentryfont', '');	
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/create_identity');
		echo view('templates/footer');
	}
	
	public function create_identity_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		$parameter_model = new Parameter_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('password', $this->request->getPost('password'));
		$session->set('realname', $this->request->getPost('realname'));
		$session->set('email', $this->request->getPost('email'));
		
		// set defaults
		$session->set('environment', 'TEST');
		$session->set('default_dataentryfont', '/Fonts/MODERN_TYPEWRITER');
		
		// find identity entered by user
		$identity = $model	->where('BMD_user', $session->identity)
							->where('project_index', $session->current_project[0]['project_index'])
							->find();

		// does it already exist?
		if ( $identity )
			{
				$session->set('message_2', 'The Identity => '.$session->identity.', already exists on this project => '.$session->current_project[0]['project_name']);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}

		// does this identity/password exist on project
		// test identity / password on project by trying to upload a file
		// set curl handle and results file handle
		
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
		
		// load curl parameters
		// load programme name
		$parameter = $parameter_model->where('Parameter_key', 'programname')->findAll();
		$session->set('programname', $parameter[0]['Parameter_value']);
		// load version
		$parameter = $parameter_model->where('Parameter_key', 'version')->findAll();
		$session->set('version', $parameter[0]['Parameter_value']);
		// load uploadagent
		$parameter = $parameter_model->where('Parameter_key', 'uploadagent')->findAll();
		$session->set('uploadagent', $parameter[0]['Parameter_value']);
		// set curl
		// set up the curl
		$ch = curl_init($session->curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		// set up the fields to pass
		$postfields = array(
										"UploadAgent" => $session->uploadagent,
										"user" => $session->identity,
										"password" => $session->password,
										"file" => "TEST",
										"content2" => @getcwd()."/curl_result.txt",
										"data_version" => "districts.txt:??"
										);
		// set the curl options
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.'1.0.0');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Identity::create_identity_step2, around line 133 => '.$curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);
		// search the curl result for error
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "passwordfailed") !== FALSE)
				{
					fclose($fp);
					$session->set('message_2', 'The identity => '.$session->identity.' and password are not valid for the project => '.$session->current_project[0]['project_name'].'. Do you have a '.$session->current_project[0]['project_name'].' account?');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('identity/create_identity_step1/2') );
				}
		}

		// test for real name
		if ( $this->request->getPost('realname') == '' )
			{
				$session->set('message_2', 'You must enter your real name.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
			
		// test for email
		if ( $this->request->getPost('email') == '' )
			{
				$session->set('message_2', 'You must enter your email.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/create_identity_step1/1') );
			}
			
		// All good so write to database
		$data = [
						'BMD_user' => $session->identity,
						'BMD_password' => $session->password,
						'BMD_realname' => $session->realname,
						'BMD_email' => $session->email,
						'BMD_total_records' => 0,
						'environment' => $session->environment,
						'project_index' => $session->current_project[0]['project_index'],
						'default_dataentryfont' => $session->default_dataentryfont,
					];
		$model->insert($data);
		
		// go back to sign in
		$session->set('message_2', 'Your Identity=> '.$session->identity.' has been created in this project => '.$session->current_project[0]['project_name'].' on FreeComETT. Now, please sign in.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/signin_step1/2') );
	}
	
	public function change_password_step1($start_message)
	{
		// initialise
		$session = session();

		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Change your password on this system. The new password must match your identity and password on FreeBMD.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('newpassword', '');
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/change_password');
		echo view('templates/footer');
	}
	
	public function change_password_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('newpassword', $this->request->getPost('newpassword'));
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $session->identity)->find();
		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'This Identity is not registered on this system. => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/change_password_step1/1') );
			}

		// test identity / password on FreeBMD by trying to upload a file
		// set curl handle and results file handle - need to get defaults from parameters table because the common_helper has not yet been run.
		// load environment
		$parameter = $parameter_model->where('Parameter_key', 'environment')->findAll();
		$session->set('environment', $parameter[0]['Parameter_value']);
		// load autoupload url for curl
		$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_live')->findAll();
		$session->set('autouploadurl_live', $parameter[0]['Parameter_value']);
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
		// set curl
		// set up the curl depending on environment, $session->curl_url is set in Home
		$ch = curl_init($session->curl_url);
		$fp = fopen(getcwd()."/curl_result.txt", "w");				
		// set up the fields to pass
		$postfields = array(
										"UploadAgent" => $session->uploadagent,
										"user" => $session->identity,
										"password" => $session->password,
										"file" => "TEST",
										"content2" => @getcwd()."/curl_result.txt",
										"data_version" => "districts.txt:??"
										);
		// set the curl options
		curl_setopt($ch, CURLOPT_USERAGENT, $session->programname.':'.'1.0.0');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
		curl_setopt($ch, CURLOPT_FILE, $fp);
		// execute curl
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Failed to fetch references in Identity::change_password_step2, around line 279 => '.$curl_url);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/change_password_step1/1') );
			}
		// close the curl and file handles
		curl_close($ch);
		fclose($fp);
		// search the curl result for error
		$fp = fopen(getcwd()."/curl_result.txt", "r");
		while (!feof($fp))
		{
			$buffer = fgets($fp);
			if (strpos($buffer, "passwordfailed") !== FALSE)
				{
					fclose($fp);
					$session->set('message_2', 'This identity and password is not valid for the FreeBMD site. Do you have a FreeBMD account? => '.$session->identity);
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('identity/change_password_step1/1') );
				}
		}
			
		// All good so update to database
		$data = [
						'BMD_password' => $session->newpassword
					];
		$model->update($identity[0]['BMD_identity_index'], $data);
		
		// go back to signon
		$session->set('message_2', 'Your password has been changed on this system.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/signin_step1/2') );
	}
	
	public function retrieve_password_step1($start_message)
	{
		// initialise
		$session = session();
		
		// if retrieve password step 1 = 0 it was called from signin view
		if ( $start_message == 0 )
			{
				$session->set('message_1', 'Retrieve your password by entering the following information. You will receive an email with your password.');
				$session->set('message_class_1', 'alert alert-primary');
				$session->set('message_2', '');
				$session->set('message_class_2', '');
				
				$session->set('identity', '');
				$session->set('email', '');
			}
		
		// show view
		echo view('templates/header');
		echo view('linBMD2/retrieve_password');
		echo view('templates/footer');
	}
	
	public function retrieve_password_step2()
	{
		// initialise method
		$session = session();
		$model = new Identity_Model();
		
		// get user data
		$session->set('identity', $this->request->getPost('identity'));
		$session->set('email', $this->request->getPost('email'));

		// find identity entered by user
		$session->set('user', $model->where('BMD_user', $session->identity)->find());
		// was it found? 
		if ( count($session->user) == 0 )
			{
				$session->set('message_2', 'This Identity is not registered on this system. => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				// add 1 to redirect so that messages are not reset
				return redirect()->to( base_url('identity/retrieve_password_step1/1') );
			}
			
		// test email entered is same as that on the identity
		if ( $session->email != $session->user[0]['BMD_email'] )
			{
				$session->set('message_2', 'The email you entered is not valid for your account. => '.$session->email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/retrieve_password_step1/1') );
			}
			
		// All good so send email to user
		return redirect()->to( base_url('transcribe/send_email/identity') );
	}
	
	public function admin_user_step1($start_message)
	{		
		// initialise method
		$session = session();
		
		// set values
		switch ($start_message) 
			{
				case 0:
					// initialise values
					$session->set('admin-user', '');
					// message defaults
					$session->set('message_1', 'Give or remove webBMD admin rights to a webBMD user.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Give or remove webBMD admin rights to a webBMD user.');
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
		$session->set('admin_action', $this->request->getPost('admin_action'));
		
		// find identity entered by user
		$identity = $model->where('BMD_user', $session->identity)->find();
		// was it found?
		if ( ! $identity )
			{
				$session->set('message_2', 'This Identity is not registered on this system. => '.$session->identity);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/admin_user_step1/1') );
			}
		
		// is action set?
		if ( $session->admin_action == '' )
			{
				$session->set('message_2', 'Please select an acton, either give or remove admin rights');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('identity/admin_user_step1/1') );
			}
			
		// tests for admin_action
		switch ($session->admin_action)
			{
				case "give":
					// Is this identity already an admin?
					if ( $identity[0]['BMD_admin'] == "Y" )
						{
							$session->set('message_2', 'This Identity is already an admin. => '.$session->identity);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/admin_user_step1/1') );
						}
					break;
				case "remove":
					// Is identity already an admin?
					if ( $identity[0]['BMD_admin'] != "Y"  )
						{
							$session->set('message_2', 'This Identity is not an admin, so I cannot remove admin rights => '.$session->identity);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/admin_user_step1/1') );
						}
					// Trying to remove admin rights from self?
					if ( $identity[0]['BMD_user'] == $session->user[0]['BMD_user'] )
						{
							$session->set('message_2', 'You cannot remove admin rights from yourself. => '.$session->identity);
							$session->set('message_class_2', 'alert alert-danger');
							return redirect()->to( base_url('identity/admin_user_step1/1') );
						}
					break;
				default:
					break;
			}
			
		// set update fields
		switch ($session->admin_action)
			{
				case "give":
					$action = "Y";
					$session->set('message_2', 'Admin rights given to => '.$session->identity);
					break;
				case "remove";
					$action = '';
					$session->set('message_2', 'Admin rights removed from => '.$session->identity);
					break;
				default:
					break;
			}
			
		$data = [
					'BMD_admin' => $action,
				];
		$model->update($identity[0]['BMD_identity_index'], $data);
				
		// go back
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('identity/admin_user_step1/1') );
	}
	
}
