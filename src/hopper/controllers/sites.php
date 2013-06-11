<?php

namespace hopper\controllers;

class Sites extends \hopper\lib\ResourceAvailable {
	function get() {
		$api = $this->has_access(\hopper\lib\Input::get('auth'), \hopper\lib\URI::segment(2));

		$file = '/var/www/sites/' . \hopper\lib\URI::segment(2) . '/' . \hopper\lib\URI::segment(2) . '.zip';
		header("Content-Type: application/octet-stream");
		header("Content-Length: " .(string)(filesize($file)) );
		header('Content-Disposition: attachment; filename="map.zip"');
		header("Content-Transfer-Encoding: binary\n");
		readfile($file);
	}

	function post() {
		$api = $this->has_access(\hopper\lib\Input::get('auth'), \hopper\lib\URI::segment(2), false);
		$user = \hopper\lib\API::request('/profile?auth=' . \hopper\lib\Input::get('auth'));
		$output = exec('python /var/www/mapper.py ' . $api->id . ' ' . $api->latitude . ' ' . $api->longitude . ' ' . $user->firstname . ' ' . $user->lastname . ' ' . $user->email . ' > /dev/null 2>&1 &');
		error_log($output);
		die();
	}
}