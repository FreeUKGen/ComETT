<?php namespace App\Models;

use CodeIgniter\Model;

class Detail_Data_Model extends Model
{
    protected $table = 'detail_data';
    protected $primaryKey = 'BMD_index';
	protected $allowedFields =	[	
									'project_index',
									'BMD_identity_index',
									'BMD_index',
									'BMD_header_index',
									'BMD_line_sequence',
									'BMD_surname',
									'BMD_firstname',
									'BMD_secondname',
									'BMD_thirdname',
									'BMD_partnername',
									'BMD_district',
									'BMD_volume',
									'BMD_registration',
									'BMD_page',
									'BMD_status',
									'BMD_age',
									'BMD_line_panzoom_x',
									'BMD_line_panzoom_y',
									'BMD_line_panzoom_z',
									'BMD_line_sharpen',
									'BMD_line_image_rotate',
									'BMD_reg',
									'BMD_entry',
									'BMD_source_code',
									'BMD_dor',
									'BMD_district_number',
									'BMD_month',
									'BMD_dob',
									'reopen_flag',
									'line_verified',
									'detail_x',
									'detail_y',
								];
    protected $returnType = 'array';
}
