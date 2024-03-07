<?php namespace App\Models;

use CodeIgniter\Model;

class Transcription_CSV_File_Model extends Model
{
    protected $table = 'transcription_csv_file';
    protected $primaryKey = 'transcription_index';
    protected $allowedFields = 	[
									'transcription_index',
									'csv_string',
								];
    protected $returnType = 'array';
}
