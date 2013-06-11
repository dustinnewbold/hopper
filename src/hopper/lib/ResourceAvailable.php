<?php

namespace hopper\lib;

class ResourceAvailable {
	protected function has_access($auth = null, $siteid = null, $available = true) {
		if ( empty($auth) ) {
			$response = array(
				'status' => 'Missing auth key'
			);
			echo json_encode($response);
			die();
		}

		if ( !($siteid > 0) ) {
			$response = array(
				'status' => 'Missing site id'
			);
			echo json_encode($response);
			die();
		}

		$api = \hopper\lib\API::request('/sites/' . $siteid . '?auth=' . $auth);
		if ( empty($api) || !isset($api->id) ) {
			$response = array(
				'status' => 'You do not have access to this resource'
			);
			echo json_encode($response);
			die();
		}

		if ( $available === true ) {
			$file = '/var/www/sites/' . $siteid . '/' . $siteid . '.zip';
			if ( !file_exists($file) ) {
				$response = array(
					'status' => 'unavailable'
				);
				echo json_encode($response);
				die();
			}
		}

		return $api;
	}
}