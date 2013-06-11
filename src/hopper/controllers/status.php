<?php

namespace hopper\controllers;

class Status extends \hopper\lib\ResourceAvailable {
	function get() {
		$api = $this->has_access(\hopper\lib\Input::get('auth'), \hopper\lib\URI::segment(2));

		$response = array(
			'status' => 'ready'
		);
		echo json_encode($response);
		die();
	}
}