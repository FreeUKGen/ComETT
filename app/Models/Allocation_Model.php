<?php namespace App\Models;

use CodeIgniter\Model;

class Allocation_Model extends Model
{
    protected $table = 'allocation';
    protected $primaryKey = 'BMD_allocation_index';
    protected $allowedFields = ['BMD_identity_index', 'BMD_allocation_index', 'BMD_syndicate_index', 'BMD_allocation_name', 'BMD_reference', 'BMD_start_date', 'BMD_end_date', 'BMD_start_page', 'BMD_end_page',
								'BMD_year', 'BMD_quarter', 'BMD_letter', 'BMD_type', 'BMD_status', 'BMD_sequence', 'BMD_scan_type', 'BMD_last_action',
								'BMD_last_uploaded', 'BMD_syndicate_scan'];
    protected $returnType = 'array';
}
