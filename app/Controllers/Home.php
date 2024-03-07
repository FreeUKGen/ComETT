<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Parameter_Model;
use App\Models\Projects_Model;
use App\Models\Help_Model;

class Home extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('backup');
    }
	
	public function index()
	{		
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();
		$parameter_model = new Parameter_Model();
		
		// destroy the session variables no longer required
		$session->environment = '';
		$session->realname = '';
		$session->signon_success = 0;
		
		// load time stamp to session
		$session->set('login_time_stamp', time());
			
		// set heading
		$session->set('title', 'FreeComETT - A FreeUKGen transcription application.');
		$session->set('realname', '');
		
		// load projects
		$session->set('projects', $projects_model->findAll());
		
		// were any found? if not, this is first use of the system
		if ( ! $session->projects )
			{
				var_dump('first_use');
			}
			
		// I need to detect if javascript is enabled in the browser. 
		// set a php session variable to disabled
		// add some script to the project select page changing the php session variable to enabled
		// check the variable in identity - if disabled, send user a message.
		$session->javascript = 'disabled';
			
		// show view to select project
		echo view('linBMD2/project_select');
	}
	
	public function signout()
	{
		// declare session
		$session = session();
		
		// destroy the session
		$session->destroy();
		
		// clean session files
		// get the session save path
		$config = config('App');
		$sessionSavePath = $config->sessionSavePath;
		// find session files
		foreach( glob($sessionSavePath.'/ci_session*') as $file )
			{
				// check if it is a file
				if( is_file($file) )
					{
						// delete file - not sure I want to do this since the app is no multi user.
						// unlink($file);
					}
			}
		
		// return
		return redirect()->to( base_url('home') );
	}
	
	public function close()
	{
		// declare session
		$session = session();
		
		// destroy the session
		$session->destroy();
		
		// tell user to exit using ALT+F4
		echo view('linBMD2/close');
	}
	
	public function session_exists()
	{
		// declare session
		$session = session();
		
		$session_status = '';
		
		// If realname is not set, it must mean that the session has expired or was never intialised.
		if ( ! $session->has('realname') )
			{
				$session_status = 'session_expired';
				return  json_encode($session_status);
			}
		else
			{
				$session_status = 'session_active';
				return  json_encode($session_status);
			}
	}
	
	public function update_in_progress()
	{
		// declare session
		$session = session();
		
		// show update in progress message
		echo view('linBMD2/update_in_progress');
	}
	
	public function test_javascript()
	{
		// declare session
		$session = session();
		
		// set javascript session variable
		$session->javascript = 'enabled';
		
		return;
	}
	
	public function no_javascript()
	{
		// declare session
		$session = session();
		
		// show no javascript message 
		echo view('linBMD2/no_javascript');
		
		return;
	}	
}


