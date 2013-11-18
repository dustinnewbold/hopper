<?php

namespace API\lib;

/**
 * This class is designed to interact with request variables.
 * Example: GETs, POSTs, etc.
 */

class Input {
	public static function get($var = null) {
		if ( $var === null ) {
			 return self::getAll();
		} else {
			if ( !isset($_GET[$var]) ) {
				return false;
			}
			return $_GET[$var];
		}
	}

	public static function post($var = null) {
		if ( $var === null ) {
			return self::postAll();
		}

		if ( isset($_POST[$var]) ) {
			return $_POST[$var];
		}

		return false;
	}

	public static function request( $var = null ) {
		if ( $var === null ) {
			return self::requestAll();
		}

		if ( isset($_REQUEST[$var]) ) {
			return $_REQUEST[$var];
		}

		return false;
	}

	public static function put ( $var = null ) {
		global $_PUT;

		if ( !$_PUT ) {
			parse_str(file_get_contents("php://input"), $_PUT);
		}

		if ( $var === null ) {
			return $_PUT;
		}

		if ( !isset($_PUT[$var]) ) {
			return false;
		}

		return $_PUT[$var];
	}

	protected static function getAll() {
		return $_GET;
	}

	protected static function postAll() {
		return $_POST;
	}

	protected static function requestAll() {
		return $_REQUEST;
	}
}
