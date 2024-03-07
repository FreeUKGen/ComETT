<?php namespace App\Models;

use CodeIgniter\Model;

class Projects_Model extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'project_index';
    protected $allowedFields = 	[	'project_index',
									'project_name',
									'project_desc',
									'project_pathtoicon',
									'project_iconname',
									'project_autoimageurl', 								
									'project_autouploadurllive', 
									'project_autouploadurltest',
									'back_button_text',
									'submit_button_text',
									'hmac_key',
									'environment',
									'project_status',
									'syndicate_refresh',
									'signons_to_project',
								];
    protected $returnType = 'array';
}
