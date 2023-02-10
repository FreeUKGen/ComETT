<?php namespace App\Controllers;

use App\Models\Identity_Model;
use App\Models\Parameter_Model;
use App\Models\Projects_Model;

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
		$identity_model = new Identity_Model();
		$parameter_model = new Parameter_Model();
		$projects_model = new Projects_Model();
		
		// load time stamp to session
		$session->set('login_time_stamp', time());
		
		// load current data entry font
		$parameter = $parameter_model->where('Parameter_key', 'currentdataentryfont')->findAll();
		$session->set('data_entry_font', $parameter[0]['Parameter_value']);
		
		// load curl url
		switch ($session->environment) 
			{
				case 'LIVE':
					$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_live')->findAll();
					$session->set('autouploadurl_live', $parameter[0]['Parameter_value']);
					$session->set('curl_url', $session->autouploadurl_live);
					break;
				case 'TEST':
					$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_test')->findAll();
					$session->set('autouploadurl_test', $parameter[0]['Parameter_value']);
					$session->set('curl_url', $session->autouploadurl_test);
					break;
				default:
					$parameter = $parameter_model->where('Parameter_key', 'autouploadurl_test')->findAll();
					$session->set('autouploadurl_test', $parameter[0]['Parameter_value']);
					$session->set('curl_url', $session->autouploadurl_test);
					break;
			}
			
		// set heading
		$session->set('title', 'webBMD - A FreeBMD transcription application.');
		$session->set('realname', '');
		
		// load projects
		$session->set('projects', $projects_model->findAll());
		
		// were any found? if not, this is first use of the system
		if ( ! $session->projects )
			{
				var_dump('first_use');
			}
		
		// show view to select project
		echo view('linBMD2/project_select');

	}
	
	public function signout()
	{
		// declare session
		$session = session();
		
		// destroy the session
		$session->destroy();
		
		// backup the database 
		database_backup();
		
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
	
}
