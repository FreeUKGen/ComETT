<?php namespace App\Models;

use CodeIgniter\Model;

class Header_Model extends Model
{
    protected $table = 'header';
    protected $primaryKey = 'BMD_header_index';
    protected $allowedFields = ['BMD_header_index', 'BMD_file_name', 'BMD_scan_name', 'BMD_start_date', 'BMD_end_date',
    												'BMD_submit_date', 'BMD_submit_status',
													'BMD_submit_message', 'BMD_current_page', 'BMD_current_page_suffix',
													'BMD_next_page', 'BMD_records','BMD_header_status',
													'BMD_allocation_index', 
													'BMD_identity_index', 'BMD_syndicate_index', 'BMD_last_action', 
													'BMD_panzoom_x', 'BMD_panzoom_y', 'BMD_panzoom_z', 'BMD_sharpen', 
													'BMD_image_x', 'BMD_image_y', 'BMD_image_scroll_step', 'BMD_image_rotate',
													'BMD_font_family', 'BMD_font_style', 'BMD_font_size'];
    protected $returnType = 'array';
}