<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

// home routes group
$routes->group
	("home", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('signout', 'Home::signout');
			$routes->get('close', 'Home::close');
			$routes->get('help', 'Home::help');
		}
	);
	
// identity routes group
$routes->group
	("identity", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('signin_step1/(:segment)', 'Identity::signin_step1/$1');
			$routes->post('signin_step2', 'Identity::signin_step2');
			$routes->get('create_identity_step1/(:segment)', 'Identity::create_identity_step1/$1');
			$routes->post('create_identity_step2', 'Identity::create_identity_step2');
			$routes->get('change_password_step1/(:segment)', 'Identity::change_password_step1/$1');
			$routes->post('change_password_step2', 'Identity::change_password_step2');
			$routes->get('retrieve_password_step1/(:segment)', 'Identity::retrieve_password_step1/$1');
			$routes->post('retrieve_password_step2', 'Identity::retrieve_password_step2');
			$routes->get('admin_user_step1/(:segment)', 'Identity::admin_user_step1/$1');
		}
	);

// transcriibe routes group
$routes->group
	("transcribe", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('/', 'Transcribe::index');
			$routes->get('transcribe_step1/(:segment)', 'Transcribe::transcribe_step1/$1');
			$routes->post('next_action', 'Transcribe::transcribe_next_action');
			$routes->get('create_BMD_file/(:segment)', 'Transcribe::create_BMD_file/$1');
			$routes->get('upload_BMD_file/(:segment)', 'Transcribe::upload_BMD_file/$1');
			$routes->get('submit_details/(:segment)', 'Transcribe::submit_details/$1');
			$routes->get('close_header_step1/(:segment)', 'Transcribe::close_header_step1/$1');
			$routes->post('close_header_step2', 'Transcribe::close_header_step2');
			$routes->get('verify_BMD_file_step1/(:segment)', 'Transcribe::verify_BMD_file_step1/$1');
			$routes->get('verify_BMD_trans_step1/(:segment)', 'Transcribe::verify_BMD_trans_step1/$1');
			$routes->get('search_synonyms', 'Transcribe::search_synonyms');
			$routes->get('search_districts', 'Transcribe::search_districts');
			$routes->get('search_volumes', 'Transcribe::search_volumes');
			$routes->get('search_firstnames', 'Transcribe::search_firstnames');
			$routes->get('search_surnames', 'Transcribe::search_surnames');
			$routes->get('update_firstnames/(:segment)', 'Transcribe::update_firstnames/$1');
			$routes->get('update_surnames/(:segment)', 'Transcribe::update_surnames/$1');
			$routes->get('image_parameters_step1/(:segment)', 'Transcribe::image_parameters_step1/$1');
			$routes->post('image_parameters_step2/(:segment)', 'Transcribe::image_parameters_step2/$1');
			$routes->get('enter_parameters_step1/(:segment)', 'Transcribe::enter_parameters_step1/$1');
			$routes->post('enter_parameters_step2/(:segment)', 'Transcribe::enter_parameters_step2/$1');
			$routes->get('delete_line_step1/(:segment)', 'Transcribe::delete_line_step1/$1');
			$routes->post('delete_line_step2', 'Transcribe::delete_line_step2');
			$routes->get('send_email/(:segment)', 'Transcribe::send_email/$1');
		}
	);

// header routes group
$routes->group
	("header", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('create_BMD_step1/(:segment)', 'Header::create_BMD_step1/$1');
			$routes->post('create_bmd_step2', 'Header::create_BMD_step2');
			$routes->get('reopen_BMD_step1/(:segment)', 'Header::reopen_BMD_step1/$1');
			$routes->post('reopen_BMD_step2', 'Header::reopen_BMD_step2');
		}
	);
	
// allocation routes group
$routes->group
	("allocation", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('create_allocation_step1/(:segment)', 'Allocation::create_allocation_step1/$1');
			$routes->post('create_allocation_step2', 'Allocation::create_allocation_step2');
			$routes->get('manage_allocations/(:segment)', 'Allocation::manage_allocations/$1');
			$routes->post('next_action', 'Allocation::next_action');
		}
	);

// births routes group
$routes->group
	("births", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('transcribe_births_step1/(:segment)', 'Births::transcribe_births_step1/$1');
			$routes->post('transcribe_births_step2', 'Births::transcribe_births_step2');
			$routes->get('select_line/(:segment)', 'Births::select_line/$1');
			$routes->get('delete_line_step1/(:segment)', 'Births::delete_line_step1/$1');
			$routes->post('delete_line_step2', 'Births::delete_line_step2');
			$routes->get('comment_step1/(:segment)', 'Births::comment_step1/$1');
			$routes->post('comment_step2', 'Births::comment_step2');
			$routes->get('select_comment/(:segment)', 'Births::select_comment/$1');
			$routes->get('remove_comments/(:segment)/(:segment)', 'Births::remove_comments/$1/$2');
		}
	);

// marriages routes group
$routes->group
	("marriages", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('transcribe_marriages_step1/(:segment)', 'Marriages::transcribe_marriages_step1/$1');
			$routes->post('transcribe_marriages_step2', 'Marriages::transcribe_marriages_step2');
			$routes->get('select_line/(:segment)', 'Marriages::select_line/$1');
			$routes->get('delete_line_step1/(:segment)', 'Marriages::delete_line_step1/$1');
			$routes->post('delete_line_step2', 'Marriages::delete_line_step2');
			$routes->get('comment_step1/(:segment)', 'Marriages::comment_step1/$1');
			$routes->post('comment_step2', 'Marriages::comment_step2');
			$routes->get('select_comment/(:segment)', 'Marriages::select_comment/$1');
			$routes->get('remove_comments/(:segment)/(:segment)', 'Marriages::remove_comments/$1/$2');
		}
	);

// deaths routes group
$routes->group
	("deaths", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('transcribe_deaths_step1/(:segment)', 'Deaths::transcribe_deaths_step1/$1');
			$routes->post('transcribe_deaths_step2', 'Deaths::transcribe_deaths_step2');
			$routes->get('select_line/(:segment)', 'Deaths::select_line/$1');
			$routes->get('delete_line_step1/(:segment)', 'Deaths::delete_line_step1/$1');
			$routes->post('delete_line_step2', 'Deaths::delete_line_step2');
			$routes->get('comment_step1/(:segment)', 'Deaths::comment_step1/$1');
			$routes->post('comment_step2', 'Deaths::comment_step2');
			$routes->get('select_comment/(:segment)', 'Deaths::select_comment/$1');
			$routes->get('remove_comments/(:segment)/(:segment)', 'Deaths::remove_comments/$1/$2');
		}
	);

// housekeeping routes group
$routes->group
	("housekeeping", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get("index/(:segment)", "Housekeeping::index/$1");
			$routes->get("districts_staleness", "Housekeeping::districts_staleness");
			$routes->get("districts_refresh", "Housekeeping::districts_refresh");
			$routes->get("database_backup", "Housekeeping::database_backup");
			$routes->get("export_names", "Housekeeping::export_names");
			$routes->get("import_names", "Housekeeping::import_names");
			$routes->get("firstnames", "Housekeeping::firstnames");
			$routes->get("surnames", "Housekeeping::surnames");
			$routes->get("phpinfo", "Housekeeping::phpinfo");
			$routes->get("merge_names", "Housekeeping::merge_names");
			$routes->get("create_header_data_entry_dimensions", "Housekeeping::create_header_data_entry_dimensions");
		}
	);
	
// syndicate routes group
$routes->group
	("syndicate", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('refresh_syndicates', 'Syndicate::refresh_syndicates');
			$routes->get('manage_syndicates/(:segment)', 'Syndicate::manage_syndicates/$1');
			$routes->post('next_action', 'Syndicate::next_action');
		}
	);

// surname routes group
$routes->group
	("surname", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('manage_surnames/(:segment)', 'Surname::manage_surnames/$1');
			$routes->get('search', 'Surname::search');
			$routes->get('correct_surname_step1/(:segment)', 'Surname::correct_surname_step1/$1');
			$routes->get('correct_surname_step2', 'Surname::correct_surname_step2');
		}
	);

// firstname routes group
$routes->group
	("firstname", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('manage_firstnames/(:segment)', 'Firstname::manage_firstnames/$1');
			$routes->get('search', 'Firstname::search');
			$routes->get('correct_firstname_step1/(:segment)', 'Firstname::correct_firstname_step1/$1');
			$routes->get('correct_Firstname_step2', 'Firstname::correct_firstname_step2');
		}
	);

// projects routes group
$routes->group
	("projects", ['filter' => 'sessionexists'], function($routes)
		{
			$routes->get('load_project/(:segment)', 'Projects::load_project/$1');
		}
	);

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
