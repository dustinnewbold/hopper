<?php

namespace hopper\lib;

class API {
	private static $ch = null;
	// const apibase = 'https://api.ornicept.com';
	const apibase = 'https://api.ornicept.com';

	public static function request($url, $method = 'get', $fields = array()) {
		if ( self::$ch === null ) {
			self::$ch = curl_init();
		}

		$method = strtoupper($method);

		curl_setopt(self::$ch, CURLOPT_URL, self::apibase . $url);
		// curl_setopt(self::$ch, CURLOPT_HEADER, true);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt(self::$ch, CURLOPT_AUTOREFERER, true);
		curl_setopt(self::$ch, CURLOPT_TIMEOUT, 120);
		curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt(self::$ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, $method);

		$response = json_decode(curl_exec(self::$ch));

		return $response;
	}
}
