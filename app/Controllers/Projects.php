<?php namespace App\Controllers;

use App\Models\Projects_Model;
use App\Libraries\DatabaseConnector;

class Projects extends BaseController
{
	
	public function load_project($project)
	{		
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();
		
		// get project details
		$session->current_project = $projects_model 
			->where('project_index', $project) 
			->find();
		
		// set the project environment - see Identity controller for using the environment parameter
		$session->environment_project = $session->current_project[0]['environment'];
		
		// set the project DB depending on project selected
		switch ( $session->current_project[0]['project_name']) 
			{
				case 'FreeBMD':
					// The FreeBMD database name changes on each Freebmd update.
					// In order to set the FreeBMD DB up, I have to get the latest bmd_12345678 name from a cgi script using curl.
					// Once retrieved I need to massage the contents to isolate the DB name
					// Once massaged, I can inject the current DB into the DB definition 
					
					// set the url
					$curl_url = 'https://www.freebmd.org.uk/cgi/dbinfo.pl';
					$ch = curl_init($curl_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					
					// get the data - thanks to Lemon for this code snippet
					$curl_result = curl_exec($ch);
					if ( $curl_result == '' )
						{
							$session->currentdb = 'bmd_1694426109';
						}
					else
						{
							// decode the data to an array
							$session->currentdb = json_decode($curl_result, true);
						}
							
					// setup the DB definition
					// 'hostname' => 'ginseng.internal.freeukgen.org.uk',
					// project DB
					$session->project_DB = 	[
												'DSN'      => '',
												'hostname' => $session->current_project[0]['DB_hostname'],
												'username' => $session->current_project[0]['DB_username'],
												'password' => $session->current_project[0]['DB_password'],
												'database' => $session->currentdb['current_db'],
												'DBDriver' => $session->current_project[0]['DB_driver'],
												'DBPrefix' => '',
												'pConnect' => false,
												'DBDebug'  => (ENVIRONMENT !== 'production'),
												'cacheOn'  => false,
												'cacheDir' => '',
												'charset'  => 'utf8',
												'DBCollat' => 'utf8_general_ci',
												'swapPre'  => '',
												'encrypt'  => false,
												'compress' => false,
												'strictOn' => false,
												'failover' => [],
												'port'     => $session->current_project[0]['DB_hostport'],
											];
					// syndicate DB
					$session->syndicate_DB = 	[
												'DSN'      => '',
												'hostname' => $session->current_project[0]['DB_hostname'],
												'username' => $session->current_project[0]['DB_username'],
												'password' => $session->current_project[0]['DB_password'],
												'database' => 'syndicate',
												'DBDriver' => $session->current_project[0]['DB_driver'],
												'DBPrefix' => '',
												'pConnect' => false,
												'DBDebug'  => (ENVIRONMENT !== 'production'),
												'cacheOn'  => false,
												'cacheDir' => '',
												'charset'  => 'utf8',
												'DBCollat' => 'utf8_general_ci',
												'swapPre'  => '',
												'encrypt'  => false,
												'compress' => false,
												'strictOn' => false,
												'failover' => [],
												'port'     => $session->current_project[0]['DB_hostport'],
											];
					break;
				case 'FreeREG':
					// FreeREG uses a MongoDB database
					// see here for set up details https://www.mongodb.com/compatibility/mongodb-and-codeigniter
					// project DB
					$session->project_DB = 	[
												'hostname' => $session->current_project[0]['DB_hostname'],
												'database' => $session->current_project[0]['DB_database'],
												'port'     => $session->current_project[0]['DB_hostport'],
												'DBDriver' => $session->current_project[0]['DB_driver'],
											];
					
					break;
				case 'FreeCEN':
					break;
			}

		// go to signin
		return redirect()->to( base_url('identity/signin_step1/0') );
	}
	
	public function manage_projects_step1($start_message)
	{		
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();
	
		// set messages
		switch ($start_message) 
			{
				case 0:
					$session->set('message_1', 'Manage FreeComETT Projects.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');				
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Manage FreeComETT Projects');
					$session->set('message_class_1', 'alert alert-primary');
					break;
			}
			
		// get all projects
		$session->projects =	$projects_model
								->findAll();

		// any found								
		if (  ! $session->projects )
			{
				$session->set('message_2',  'No FreeComETT Projects found. Please report to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step1/2') );
			}			
			
		// show parameters
		echo view('templates/header');
		echo view('linBMD2/manage_projects');
		echo view('templates/footer');
	}
	
	public function manage_projects_step2($project_index)
	{		
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();

		// get parameter value
		$session->project_values =	$projects_model
									->where('project_index', $project_index)
									->find();
		// found?
		if ( ! $session->project_values )
			{
				$session->set('message_2', 'Sorry I cannot find the praject you selected. Please report to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('pproject/manage_projects_step1/2') );
			}
	
		// set fields
		$session->new_project_name = $session->project_values[0]['project_name'];
		$session->new_project_desc = $session->project_values[0]['project_desc'];
		$session->new_project_pathtoicon = $session->project_values[0]['project_pathtoicon'];
		$session->new_project_iconname = $session->project_values[0]['project_iconname'];
		$session->new_project_autoimageservertype = $session->project_values[0]['project_autoimageservertype'];
		$session->new_project_autoimageurl = $session->project_values[0]['project_autoimageurl'];
		$session->new_project_autouploadurllive = $session->project_values[0]['project_autouploadurllive'];
		$session->new_project_autouploadurltest = $session->project_values[0]['project_autouploadurltest'];
		$session->new_back_button_text = $session->project_values[0]['back_button_text'];
		$session->new_submit_button_text = $session->project_values[0]['submit_button_text'];
		$session->new_hmac_key = $session->project_values[0]['hmac_key'];
		$session->new_environment = $session->project_values[0]['environment'];
		$session->new_project_status = $session->project_values[0]['project_status'];

		// show project
		echo view('templates/header');
		echo view('linBMD2/change_project_step1');
		echo view('templates/footer');
	}
	
	public function manage_projects_step3()
	{		
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();

		// get inputs
		$session->new_project_name = $this->request->getPost('project_name');
		$session->new_project_desc = $this->request->getPost('project_desc');
		$session->new_project_pathtoicon = $this->request->getPost('project_pathtoicon');
		$session->new_project_iconname = $this->request->getPost('project_iconname');
		$session->new_project_autoimageservertype = $this->request->getPost('project_autoimageservertype');
		$session->new_project_autoimageurl = $this->request->getPost('project_autoimageurl');
		$session->new_project_autouploadurllive = $this->request->getPost('project_autouploadurllive');
		$session->new_project_autouploadurltest = $this->request->getPost('project_autouploadurltest');
		$session->new_back_button_text = $this->request->getPost('back_button_text');
		$session->new_submit_button_text = $this->request->getPost('submit_button_text');
		$session->new_environment = $this->request->getPost('environment');
		$session->new_project_status = $this->request->getPost('project_status');
				
		// test inputs - environment
		$test_array = array("TEST", "LIVE");
		if ( ! in_array($session->new_environment, $test_array) )
			{
				$session->set('message_2', 'Project Environment must be TEST or LIVE. You entered '.$session->new_environment);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// test inputs - status
		$test_array = array("Open", "Closed");
		if ( ! in_array($session->new_project_status, $test_array) )
			{
				$session->set('message_2', 'Project Status must be Open or Closed. You entered '.$session->new_project_status);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
		
		// project name blank?
		if ( $session->new_project_name == '' )
			{
				$session->set('message_2', 'Project name cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}		
		
		// icon name blank?
		if ( $session->new_project_pathtoicon == '' )
			{
				$session->set('message_2', 'Path to Icon cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// icon path blank?
		if ( $session->new_project_iconname == '' )
			{
				$session->set('message_2', 'Icon name cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// image server type blank?
		if ( $session->new_project_autoimageservertype == '' )
			{
				$session->set('message_2', 'Image Server Type cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// image url blank?
		if ( $session->new_project_autoimageurl == '' )
			{
				$session->set('message_2', 'Image URL cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
		
		// upload to live servers?
		if ( $session->new_project_autouploadurllive == '' )
			{
				$session->set('message_2', 'Upload to LIVE server URL cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// upload to test servers?
		if ( $session->new_project_autouploadurltest == '' )
			{
				$session->set('message_2', 'Upload to TEST server URL cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// back button text?
		if ( $session->new_back_button_text == '' )
			{
				$session->set('message_2', 'Back Button text cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
		
		// submit button text?
		if ( $session->new_submit_button_text == '' )
			{
				$session->set('message_2', 'Submit Button text cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// description text?
		if ( $session->new_project_desc == '' )
			{
				$session->set('message_2', 'Project Description cannot be blank. Are you sure you know what you are doing? If you are wise, back out NOW!');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('projects/manage_projects_step2/'.$session->project_values[0]['project_index']));
			}
			
		// test passed
		
		// load update
		$projects_model	->where('project_index', $session->project_values[0]['project_index'])
						->set(['environment' => $session->new_environment])
						->set(['project_status' => $session->new_project_status])
						->set(['project_name' => $session->new_project_name])
						->set(['project_pathtoicon' => $session->new_project_pathtoicon])
						->set(['project_iconname' => $session->new_project_iconname])
						->set(['project_autoimageservertype' => $session->new_project_autoimageservertype])
						->set(['project_autoimageurl' => $session->new_project_autoimageurl])
						->set(['project_autouploadurllive' => $session->new_project_autouploadurllive])
						->set(['project_autouploadurltest' => $session->new_project_autouploadurltest])
						->set(['back_button_text' => $session->new_back_button_text])
						->set(['submit_button_text' => $session->new_submit_button_text])
						->set(['project_desc' => $session->new_project_desc])
						->update();
						
		// update current project
		$session->current_project =	$projects_model
									->where('project_index', $session->project_values[0]['project_index']) 
									->find();
									
		// go round again
		return redirect()->to( base_url('projects/manage_projects_step1/0') );
	}
}
