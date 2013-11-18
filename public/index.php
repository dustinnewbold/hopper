<?php
	$appbase = __DIR__ . '/..';

	// Set date
	date_default_timezone_set('UTC');

	// Autoload
	require_once('../vendor/autoload.php');

	require_once('../../config.php');
	require_once('../src/API/config/' . $env . '.config.php');
	require_once('../src/API/lib/functions.php');

	// Set up the global database controller
	global $db;
	$db = new \API\lib\DB();

	$page = new \API\lib\PageController();

	if ( \API\lib\Input::get('pagequery') ) {
		dd($db->count());
	}