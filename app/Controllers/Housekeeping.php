<?php namespace App\Controllers;

use App\Models\Districts_Model;
use App\Models\Parameter_Model;
use App\Models\Volumes_Model;
use App\Models\Volume_Ranges_Model;
use App\Models\Firstname_Model;
use App\Models\Surname_Model;
use App\Models\Detail_Data_Model;
use App\Models\Header_Model;
use App\Models\Header_Table_Details_Model;
use App\Models\Allocation_Model;
use App\Models\Table_Details_Model;

class Housekeeping extends BaseController
{
	function __construct() 
	{
        helper('common');
        helper('backup');
        helper('remote');
    }
	
	public function index($start_message)
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
		
		// intialise		
		switch ($start_message) 
			{
				case 0:
					// message defaults
					$session->set('message_1', 'Choose the Housekeeping action you wish to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Choose Housekeeping action you want to perform.' );
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}
		
		// show views
		echo view('templates/header');
		echo view('linBMD2/housekeeping_menu');
		echo view('templates/footer');
	}
	
	public function districts_staleness()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$parameter_model = new Parameter_Model();
		
		
		// this function is work in progress
		$session->set('message_2', 'Districts stale - This function is work-in-progress and is not available at this time');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('housekeeping/index/2') );
		
		
		// get districts file from FreeBMD
		$fp = fopen(getcwd()."/Districts.latest", "wb");
		$ch = curl_init($session->curl_url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FILE, $fp); 
		curl_setopt($ch, CURLOPT_HEADER, 0);  
		if ( curl_exec($ch) === false )
			{
				// problem so send error message
				$session->set('message_2', 'A technical problem occurred. Send an email to '.$session->linbmd2_email.' describing what you were doing when the error occurred => Housekeeping::districts_refresh, around line 42 => '.$curl_url.' => '.curl_error($ch));
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		curl_close($ch);		
		fclose($fp);
		// get file size
		$districts_latest_filesize = filesize(getcwd()."/Districts.latest");
		// test file size against last updated file size
		$parameter = $parameter_model->where('Parameter_key', 'sizeoflastrefresheddistrictfile')->findAll();
		$districts_last_filesize = $parameter[0]['Parameter_value'];
		// test staleness
		if ( $districts_latest_filesize == $districts_last_filesize )
			{
				$session->set('message_2', 'Your local Districts database is up-to-date. No need to refresh Districts!');
				$session->set('message_class_2', 'alert alert-success');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		else
			{
				$session->set('message_2', 'Your local Districts database is stale. You should refresh districts.');
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
	}
	
	public function districts_refresh()
	{
		// initialise
		$session = session();
		$districts_model = new Districts_Model();
		$parameter_model = new Parameter_Model();
		$volumes_model = new Volumes_Model();
		$volume_ranges_model = new Volume_Ranges_Model();
		
		
		// this function is work in progress
		$session->set('message_2', 'Districts Refresh - This function is work-in-progress and is not available at this time');
		$session->set('message_class_2', 'alert alert-warning');
		return redirect()->to( base_url('housekeeping/index/2') );
		
		
		
		// backup database
		database_backup();
		// read district and update districts master and create volumes
		// the Districts.latest file must be created before hand from the official xls containing the the list of districts as per FreeBMD.
		// see option to create the Districts.latest in the housekeepting menu under admin priviledges
		// open file
		$fp = fopen(getcwd()."/Districts.latest", "r");
		if ( ! $fp )
			{
				$session->set('message_2', 'The latest districts file could not be opened. Cannot refresh districts. Send email to '.$session->linbmd2_email);
				$session->set('message_class_2', 'alert alert-danger');
				return redirect()->to( base_url('housekeeping/index/2') );
			}
		// read file and split to array
		$insert_count = 0;
		while (($line = fgetcsv($fp, 100, "|")) !== FALSE)
			{
				// does the district exist in districts table?
				$district = $districts_model->where('District_name', $line[0])->find();
				if ( ! $district )
					{
						$insert_count = $insert_count + 1;
						$district_name = strtoupper($line[0]);
						// if not found insert it
						$data =	[
									'District_name' => $district_name,
									'Added_by_user' => 'ADMIN',
								];
						$district_index = $districts_model->insert($data);
					}
				else
					{
						$data =	[
									'Added_by_user' => 'ADMIN',
								];
						$districts_model->update($district[0]['District_name'], $data);
						$district_index = $district[0]['district_index'];
					}
				// pad quarters with zeros, this is required for the transcription programs
				dd($line);
				$line[2] = str_pad($line[2], 2, "0", STR_PAD_LEFT);
				$line[4] = str_pad($line[4], 2, "0", STR_PAD_LEFT);
				
				// get ranges by BMD_type and add records according to type for each district
				// BMD_key will be B, M, D in turn. $session->types_upper is loaded in common_helper
				foreach ($session->types_upper as $BMD_key => $BMD_value)
					{
						$ranges = $volume_ranges_model->where('BMD_type', $BMD_key)->find();
						if ($ranges)
							{
								// Add volume records for this type
								// line array definitions
								// line[0] = district name
								// line[1] = start year
								// line[2] = start quarter
								// line[3] = last year
								// line[4] = last quarter
								// line[5] = volume code if year/quarter in range 183701:185104
								// line[6] = volume code if year/quarter in range 185201:194602
								// line[7] = volume code if year/quarter in range 194603:196501
								// line[8] = volume code if year/quarter in range 196502:197401
								// line[9] = volume code if year/quarter in range 197402:199204 if B,D
								// line[9] = volume code if year/quarter in range 197402:199304 if M
								// line[10] = volume code if year/quarter in range 199301:999999 if B,D
								// line[10] = volume code if year/quarter in range 199401:999999 if M
								// starting with the start year/quarter from line[], loop through all year/quarter, picking up the appropriate volume from range
								// insert record, BMD_type, district, year, quarter. 
								// initialise depending on whether there is a volume for the range
								// now read through the line ranges to see if there is a volume in that range
								// is there a volume in this range?
								
								$id = 5;
								while ( $id <= 10 )
									{
										if ( $line[$id] != 0 )
											{
												// set start of range
												if ( $line[1].$line[2] >= $ranges[$id-5]['BMD_range_from'] )
													{
														$range_start = $line[1].$line[2];
													}
												else
													{
														$range_start = $ranges[$id-5]['BMD_range_from'];
													}
												// set end of range
												if ( $line[3].$line[4] <= $ranges[$id-5]['BMD_range_to'] )
														{
															$range_end = $line[3].$line[4];
														}
													else
														{
															$range_end = $ranges[$id-5]['BMD_range_to'];
														}
												// does this range already exist
												$volume = $volumes_model	->where('district_index', $district_index)
																			->where('volume_from', $range_start)
																			->where('volume_to', $range_end)
																			->where('volume', $line[$id])
																			->where('BMD_type', $BMD_key)
																			->find();
												// insert record if not found
												if ( ! $volume )
												{
													$data =	[
																'district_index' => $district_index,
																'volume_from' => $range_start,
																'volume_to' => $range_end,
																'volume' => $line[$id],
																'BMD_type' => $BMD_key,
															];
													$volumes_model->insert($data);
												}
											}
										// increment ID
										$id = $id + 1;
									}
							}
					}			
			}	
		// all records read so close input file	
		fclose($fp);
		// update last file size
		$data =	[
					'Parameter_value' => filesize(getcwd()."/Districts.latest")
				];
		$parameter_model->update('sizeoflastrefresheddistrictfile', $data);
		// set return message		
		$session->set('message_2', 'Districts database has been refreshed. '.$insert_count.' records added');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );
	}
	
	public function firstnames()
	{
		// initialise
		$session = session();
		$firstname_model = new Firstname_Model();
		// get firstnames
		$session->set('names', $firstname_model->select('Firstname AS name')
																			->select('Firstname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'First names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('linBMD2/transcribe_script');
		echo view('templates/footer');
	}
	
	public function surnames()
	{
		// initialise
		$session = session();
		$surname_model = new Surname_Model();
		// get surnames
		$session->set('names', $surname_model->select('Surname AS name')
																			->select('Surname_popularity AS popularity')
																			->orderby('popularity', 'DESC')
																			->findAll());
		// show views
		$session->set('message_1', 'Family names listed in descending order by popularity');
		$session->set('message_class_1', 'alert alert-primary');
		echo view('templates/header');
		echo view('linBMD2/show_names');
		echo view('linBMD2/transcribe_script');
		echo view('templates/footer');
	}
	
	public function database_backup()
	{
		// initialise
		$session = session();
		// do the backup
		database_backup();
		
		$session->set('message_2', 'The linBMD2 database has been backed up to your web user folder.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );
	}
	
	public function admin_user_step1($start_message)
	{		
		// initialise method
		$session = session();
		
		// this function is work in progress
		$session->set('message_2', 'Admin user - This function is work-in-progress and is not available at this time');
		$session->set('message_class_2', 'alert alert-warning');
		//return redirect()->to( base_url('housekeeping/index/2') );

		// set values
		switch ($start_message) 
			{
				case 0:
					// initialise values
					$session->set('admin-user', '');
					// message defaults
					$session->set('message_1', 'Give webBMD admin rights to a webBMD user.');
					$session->set('message_class_1', 'alert alert-primary');
					$session->set('message_2', '');
					$session->set('message_class_2', '');
					break;
				case 1:
					break;
				case 2:
					$session->set('message_1', 'Give webBMD admin rights to a webBMD user.');
					$session->set('message_class_1', 'alert alert-primary');
					break;
				default:
			}	
	
		echo view('templates/header');
		echo view('linBMD2/admin_user');
		echo view('templates/footer');
	}
	
	public function merge_names()
	{		
		// initialise method
		$session = session();
		$detail_data_model = new Detail_Data_Model();
		
		// get all details
		$detail_data = $detail_data_model	
			->findAll();
			
		// read data
		foreach ($detail_data as $detail_line) 
			{
				// merge second and third name to first name
				$detail_line['BMD_firstname'] = $detail_line['BMD_firstname'].' '.$detail_line['BMD_secondname'].' '.$detail_line['BMD_thirdname'];
				
				// update record
				$data =	[
							'BMD_firstname' => $detail_line['BMD_firstname'],
							'BMD_secondname' => '',
							'BMD_thirdname' => '',
						];
				$detail_data_model->update($detail_line['BMD_index'], $data);
			}
			
		// all done
		$session->set('message_2', 'Second and third names have been merged to first name.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );	
	}
	
	public function create_header_data_entry_dimensions()
	{		
		// initialise method
		$session = session();
		$table_details_model = new Table_Details_Model();
		$header_table_details_model = new Header_Table_Details_Model();
		$header_model = new Header_Model();
		$allocation_model = new Allocation_Model();
		
		// read headers
		$headers = $header_model ->findall();
		
		foreach ($headers as $header)
			{
				// do data entry table dimensions axist already for this header
				$dimensions = $header_table_details_model
					->where('BMD_header_index', $header['BMD_header_index'])
					->find();
				
				// found?
				if ( ! $dimensions )
					{
						// get allocation
						$allocation = $allocation_model
							->where('BMD_allocation_index', $header['BMD_allocation_index'])
							->find();
						
						// allocation found?
						if ( $allocation )
							{
								// create the data entry table details
								// set format 
								// format change year depends on scan type = controller
								// default format = post
								$format = 'post';
								switch ($allocation[0]['BMD_type'])
									{
										case 'B':
											$controller = 'births';
											if ( $allocation[0]['BMD_year'] < 1993 )
												{
													$format = 'prior';
												}
											break;
										case 'D':
										$controller = 'deaths';
											if ( $allocation[0]['BMD_year'] < 1993 )
												{
													$format = 'prior';
												}
											break;
										case 'M':
										$controller = 'marriages';
											if ( $allocation[0]['BMD_year'] < 1994 )
												{
													$format = 'prior';
												}
											break;
										default:
											break;
									}
					
									// get the records
									$table_details = $table_details_model	
											->where('BMD_controller', $controller)
											->where('BMD_table_attr', 'body')
											->where('BMD_format', $format)
											->orderby('BMD_order','ASC')
											->find();
									// loop through table element by element and write the header specific table details
									foreach ($table_details as $td) 
										{ 
											// write to header table details
											$data =	[
													'BMD_header_index' => $header['BMD_header_index'],
													'BMD_table_details_index' => $td['BMD_index'],
													'BMD_header_span' => $td['BMD_span'],
													'BMD_header_align' => $td['BMD_align'],
													'BMD_header_pad_left' => $td['BMD_pad_left'],
													];
											$header_table_details_model->insert($data);
										}
							}
					}
			}
			
		// all done
		$session->set('message_2', 'Header data entry table dimensions have been created for all existing headers.');
		$session->set('message_class_2', 'alert alert-success');
		return redirect()->to( base_url('housekeeping/index/2') );	
	}
}
