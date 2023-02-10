<?php namespace App\Models;

use CodeIgniter\Model;

class Districts_Model extends Model
{
    protected $table = 'districts_master';
    protected $primaryKey = 'District_name';
    protected $allowedFields = ['District_name', 'Added_by_user'];
    protected $returnType = 'array';
}
