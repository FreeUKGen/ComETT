<?php namespace App\Controllers;

use App\Models\Projects_Model;


class Projects extends BaseController
{
	
	public function load_project($project)
	{
		// initialise method
		$session = session();
		$projects_model = new Projects_Model();
		
		// get project details
		$session->current_project = $projects_model ->where('project_index', $project) ->find();

		// go to signin
		return redirect()->to( base_url('identity/signin_step1/0') );
	}
}
