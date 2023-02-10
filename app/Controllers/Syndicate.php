<?php namespace App\Controllers;

use App\Models\Syndicate_Model;
use App\Models\Transcription_Cycle_Model;

class Syndicate extends BaseController
{
	public function index()
	{
		// initialise method
		$session = session();
		$header_model = new Header_Model();
		$session->set('message', 'Please select the Current Action or the Next Action for the BMD file you wish to work with OR create a new one. Your current BMD file is highlighted.');
		$session->set('message_value', ' ');
		$session->set('message_class', 'alert alert-primary');
	}
	
	public function manage_syndicates($start_message)
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
		$syndicate_model = new Syndicate_Model();
		// set messages
		switch ($start_message) 
			{
				case 0:
					$session->set('message_1', 'Manage Syndicates.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Manage Syndicates.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
			}
		
		// get all syndicates in syndicate name sequence
		$session->syndicates = $syndicate_model->orderby('BMD_syndicate_name')
																	->findAll();
		if (  ! $session->syndicates )
			{
				$session->set('message_2',  'No syndicatess found. Try to refresh them.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('syndicate/refresh_syndicates') );
			}
		// show syndicates
		echo view('templates/header');
		echo view('linBMD2/manage_syndicates');
		echo view('templates/footer');
	}
	
	public function next_action()
	{
		// initialise method
		$session = session();
		$syndicate_model = new Syndicate_Model();
		$transcription_cycle_model = new Transcription_Cycle_Model();
		// get inputs
		$BMD_syndicate_index = $this->request->getPost('BMD_syndicate_index');
		$session->set('BMD_cycle_code', $this->request->getPost('BMD_next_action'));
		$session->set('BMD_cycle_text', $transcription_cycle_model	->where('BMD_cycle_code', $session->BMD_cycle_code)
																												->where('BMD_cycle_type', 'SYNDC')
																												->find());
		// get syndicate
		$session->transcribe_syndicate = $syndicate_model->where('BMD_syndicate_index',  $BMD_syndicate_index)->find();
		if ( ! $session->transcribe_syndicate )
			{
				$session->set('message_2', 'Invalid syndicate, please select again.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('syndicate/manage_syndicates/2') );
			}
		// perform action selected
		switch ($session->BMD_cycle_code) 
			{
				case 'NONES': // nothing was selected
					$session->set('message_2', 'Please select an action to perform from the dropdown.');
					$session->set('message_class_2', 'alert alert-danger');
					return redirect()->to( base_url('syndicate/manage_syndicates/2') );
					break;
				case 'UPDCR': // toogle header credit line
					switch ($session->transcribe_syndicate[0]['BMD_syndicate_credit'])
						{
							case 'Y':
								$data =	[
												'BMD_syndicate_credit' => 'N',
											];
								$session->set('message_2', 'Syndicate was updated to NOT INCLUDE Credit Line in BMD File Header when uploading to FreeBMD');
								break;
							case 'N':
								$data =	[
												'BMD_syndicate_credit' => 'Y',
											];
								$session->set('message_2', 'Syndicate was updated to INCLUDE Credit Line in BMD File Header when uploading to FreeBMD');
								break;
						}
					$syndicate_model->update($BMD_syndicate_index, $data);
					$session->set('message_class_2', 'alert alert-success');
					return redirect()->to( base_url('syndicate/manage_syndicates/2') );
					break;
			}
		// no action found
		$session->set('message_2', 'No action performed. Selected action not recognised.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('syndicate/manage_syndicates/2') );			
	}
	
	public function refresh_syndicates()
	{
		// initialise method
		$session = session();
		
		// function unavailable
		$session->set('message_2', 'Sorry this function is not available at this time.');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('header/create_BMD_step1/2') );	
	}
}
