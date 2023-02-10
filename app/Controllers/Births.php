<?php namespace App\Controllers;

use App\Models\Header_Model;
use App\Models\Syndicate_Model;
use App\Models\Allocation_Model;
use App\Models\Detail_Data_Model;
use App\Models\Surname_Model;
use App\Models\Firstname_Model;
use App\Models\Districts_Model;
use App\Models\Volumes_Model;

class Births extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('update_names');
        helper('backup');
        helper('transcribe');
    }
	
	public function transcribe_births_step1($start_message)
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
			
		// initialise step1 = start message, controller, controller title
		transcribe_initialise_step1($start_message, 'births', 'Births');
		// show views
		transcribe_show_step1('births');
	}
	
	public function transcribe_births_step2()
	{
		// initialise method
		$session = session();
		
		// what data am I getting and validating?
		switch ($session->show_view_type) 
			{
				// standard data entry
				case 'transcribe':
					transcribe_get_transcribe_inputs('births');
					transcribe_validate_transcribe_inputs('births');
					break;
				// confirm district
				case 'confirm_district':
					transcribe_get_confirm_district_inputs('births');
					transcribe_validate_confirm_district_inputs('births');
					if ( $session->message_error == 'error' )	
						{
							return redirect()->to( base_url('births/transcribe_births_step1/1') );
						}
					transcribe_validate_transcribe_inputs('births');
					break;
				// confirm page
				case 'confirm_page':
					transcribe_get_confirm_page_inputs('births');
					transcribe_validate_confirm_page_inputs('births');
					if ( $session->message_error == 'error' )	
						{
							return redirect()->to( base_url('births/transcribe_births_step1/1') );
						}
					transcribe_validate_transcribe_inputs('births');
					break;
				// confirm volume
				case 'confirm_volume':
					transcribe_get_confirm_volume_inputs('births');
					transcribe_validate_confirm_volume_inputs('births');
					if ( $session->message_error == 'error' )	
						{
							return redirect()->to( base_url('births/transcribe_births_step1/1') );
						}
					transcribe_validate_transcribe_inputs('births');
					break;
				// confirm registration
				case 'confirm_registration':
					transcribe_get_confirm_registration_inputs('births');
					transcribe_validate_confirm_registration_inputs('births');
					if ( $session->message_error == 'error' )
					{
						return redirect()->to( base_url('births/transcribe_births_step1/1') );
					}
					transcribe_validate_transcribe_inputs('births');
					break;
				// confirm firstname
				case 'confirm_firstname':
					transcribe_get_confirm_firstname_inputs('births');
					transcribe_validate_confirm_firstname_inputs('births');
					if ( $session->message_error == 'error' )
					{
						return redirect()->to( base_url('births/transcribe_births_step1/1') );
					}
					transcribe_validate_transcribe_inputs('births');
					break;
			}
			
		// is there an error?
		if ( $session->message_error == 'error' )	
			{
				return redirect()->to( base_url('births/transcribe_births_step1/1') );
			}
			
		// all good - write / update data
		transcribe_update('births');
		
		// go round again
		switch ($session->BMD_cycle_code) 
			{
				case 'VERIT': // verify transcription file
					return redirect()->to( base_url('transcribe/verify_BMD_trans_step1/'.$session->transcribe_header[0]['BMD_header_index']) );
					break;
				default:
					return redirect()->to( base_url('births/transcribe_births_step1/0') );
					break;
			}
	}
		
	public function select_line($line_index)
	{
		// select the line and load session fields
		select_trans_line($line_index);
		// go back to editor					
		return redirect()->to( base_url('births/transcribe_births_step1/1') );
	}	
	
	public function delete_line_step1($line_index)
	{
		delete_line_confirm($line_index);
	}
	
	public function delete_line_step2()
	{
		delete_line_delete();
		// return
		return redirect()->to( base_url('births/transcribe_births_step1/0') );
	}
	
	public function comment_step2()
	{
		// initialse
		$session = session();
		// add/edit comments
		comment_update();
		if ( $session->message_2 != '' )
			{
				return redirect()->to( base_url('births/select_comment/'.$session->line_index) );
			}
		else
			{
				return redirect()->to( base_url('births/transcribe_births_step1/2') );
			}
	}
	
	public function select_comment($detail_line_index)
	{
		// initialise
		$session = session();
		$session->set('controller', 'births');
		// process
		comment_select($detail_line_index);
		// show comment page															
		echo view('templates/header');
		echo view('linBMD2/transcribe_comments_enter');
		echo view('linBMD2/transcribe_comments_show');
		echo view('templates/footer');	
	}
	
	public function remove_comment($comment_line_index)
	{
		$session = session();
		$session->set('controller', 'births');
		comment_remove($comment_line_index);
		return redirect()->to( base_url('births/select_comment/'.$session->transcribe_detail[0]['BMD_index']) );
	}
}
