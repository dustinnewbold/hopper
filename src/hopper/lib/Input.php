<?php

namespace hopper\lib;

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

	protected static function getAll() {
		return $_GET;
	}

	protected static function postAll() {
		return $_POST;
	}
}
