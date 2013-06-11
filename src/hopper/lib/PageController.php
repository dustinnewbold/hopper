<?php

namespace hopper\lib;

class PageController {
	private $segments = null;
	private $controller = null;

	function __construct() {
		if ( count(URI::segments()) < 2 ) {
			die('Invalid API call');
		}
		$this->segments = URI::segments();
		$this->controller = URI::segment(1);
		$this->doAction();
	}

	function doAction() {
		$method = $_SERVER['REQUEST_METHOD'];
		$controller = '\\hopper\\controllers\\' . $this->controller;
		if ( !class_exists($controller) ) {
			die('Invalid API call');
		}

		$log = 'API call for ' . $this->controller . '::' . $method;
		if ( isset($_SERVER['HTTP_REFERER']) ) {
			$log .= ' from ' . $_SERVER['HTTP_REFERER'];
		}
		$log = date('m/d/Y h:i:s') . PHP_EOL . $log;
		\errlog('---------------------------------------------' . PHP_EOL . $log);

		$action = new $controller();

		// echo('{ "status" : "' . $method . '"}');
		// die();

		if ( !method_exists($action, $method) ) {
			return Response::fail('Invalid API call : ' . $this->controller . '::' . $method);
		}

		$action->$method();
	}
}
