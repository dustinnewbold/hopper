<?php

namespace API\lib;

class PageController extends AuthSession {
	private $segments = null;
	private $controller = null;

	function __construct() {
		if ( count(URI::segments()) < 2 ) {
			return Response::fail('API Function unavailable');
		}
		$this->segments = URI::segments();
		$this->controller = URI::segment(1);
		$this->doAction();
	}

	function doAction() {
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		$function = $method;

		if ( URI::segment(2) ) {
			$function .= '_' . strtolower(URI::segment(2));
		} else {
			$function .= '_index';
		}
		$controller = '\\API\\controllers\\' . $this->controller;
		if ( !class_exists($controller) ) {
			return Response::fail('Invalid API call');
		}

		$log = strtoupper($method) . ' ' . $this->controller . '/' . strtolower(URI::segment(2)) . '  ( ' . URI::complete() . ')';
		if ( isset($_SERVER['HTTP_REFERER']) ) {
			$log .= ' from ' . $_SERVER['HTTP_REFERER'];
		}
		$log = date('m/d/Y h:i:s') . PHP_EOL . $log;

		$post = '';
		if ( isset($_POST) ) {
			foreach ( $_POST as $key => $value ) {
				if ( strtolower($key) !== 'password' ) {
					$post .= ' // ' . $key . '=' . $value;
				} else {
					$post .= ' // ' . $key;
				}
			}
		}

		$put = Input::put();
		if ( $put ) {
			foreach ( $put as $key => $value ) {
				if ( strtolower($key) !== 'password' ) {
					$post .= ' // ' . $key . '=' . $value;
				} else {
					$post .= ' // ' . $key;
				}
			}
		}

		if ( isset($_FILES) ) {
			foreach ( $_FILES as $key => $value ) {
				$post .= ' // FILEUPLOAD=' . $key;
			}
		}

		if ( $post !== '' ) {
			$post = substr($post, 4);
		}
		\errlog('---------------------------------------------' . PHP_EOL . $log . PHP_EOL . $post);

		$action = new $controller();

		$segments = URI::segments();
		for ( $end = count($segments) - 1; $end > 1; $end-- ) {

			$function = '';
			for ( $start = 2; $start <= $end; $start++ ) {
				if ( $segments[$start] > 0 ) {
					$function .= '__num';
				} else {
					$function .= '_' . $segments[$start];
				}
			}
			$function = $method . $function;

			if ( method_exists($action, $function) ) {
				$params = $segments;
				$params = array_splice($params, $start);
				return $action->$function($params);
			}
		}

		if ( method_exists($action, $method) ) {
			$params = $segments;
			$params = array_splice($params, 2);

			if ( count($params) > 0 ) {
				return $action->$method($params);
			} else {
				return $action->$method();
			}
		}

		return Response::fail('Invalid API call : ' . $this->controller . '::' . $function);
	}
}
