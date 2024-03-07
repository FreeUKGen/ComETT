<?php namespace App\Controllers;
class Phpmongo extends BaseController
{
	public function index() 
	{
		// Configuration
		$dbhost = 'localhost';
		$dbport = '27017';

		$conn = new MongoDB("mongodb://$dbhost:$dbport");

		print_r($conn);
	}
}

?>
