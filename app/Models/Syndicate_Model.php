<?php namespace App\Models;

use CodeIgniter\Model;

class Syndicate_Model extends Model
{
    protected $table = 'syndicate';
    protected $primaryKey = 'BMD_syndicate_index';
    protected $allowedFields = ['BMD_syndicate_index', 'BMD_syndicate_name', 'BMD_syndicate_leader', 'BMD_syndicate_email', 'BMD_syndicate_credit'];
    protected $returnType = 'array';
}
