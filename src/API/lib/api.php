<?php

namespace API\lib;

class API {
	private static $ch = null;
	const apibase = 'http://cookapi.api';

	public static function request($url, $method = 'get', $fields = array()) {
		if ( self::$ch === null ) {
			self::$ch = curl_init();
		}

		if ( substr($url, 0, 4) !== 'http' ) {
			$url = self::apibase . $url;
		}

		$method = strtoupper($method);

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		// curl_setopt(self::$ch, CURLOPT_HEADER, true);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt(self::$ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt(self::$ch, CURLOPT_AUTOREFERER, true);
		curl_setopt(self::$ch, CURLOPT_TIMEOUT, 120);
		curl_setopt(self::$ch, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt(self::$ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt(self::$ch, CURLOPT_SSLVERSION, 3);

		if ( $method === 'POST' ) {
			curl_setopt(self::$ch, CURLOPT_POST, true);
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $fields);
		} else {
			curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, $method);
		}

		if ( $method === 'POST' || $method === 'PUT' ) {
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $fields);
		}

		$response = (object)array();
		$response->headers = null;
		$response->data = json_decode(curl_exec(self::$ch));
		$response->headers = curl_getinfo(self::$ch);

		return $response;
	}
}
