<?php

namespace API\lib;

/**
 * This class is designed to interact specifically with
 * URL segments.
 */

class URI {
	protected static function getURI() {
		return str_replace('?' . $_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
	}

	public static function complete() {
		return $_SERVER['REQUEST_URI'];
	}

	public static function segments($segments = null) {
		if ( !$segments ) {
			$segments = explode('/', self::getURI());
		}

		$segments = array_filter($segments, function($segment) {
			if ( trim($segment) === '' ) {
				return false;
			}
			return true;
		});

		$segments = array_values($segments);
		array_unshift($segments, null);

		return $segments;
	}

	public static function segment($segmentid, $segments = null) {
		if ( !$segments ) {
			$segments = self::segments();
		}

		if ( !isset($segments[$segmentid]) ) {
			return null;
		}

		return $segments[$segmentid];
	}
}
