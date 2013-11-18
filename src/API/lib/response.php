<?php

namespace API\lib;

class Response {
	public static function status($code, $status = null, $data = null) {
		// If an object or array, assume passing data back with 200
		if ( gettype($code) === 'array' || gettype($code) === 'object' ) {
			self::output($code);
			die();
		}

		if ( !($code > 0) ) {
			dd('this');
			return false;
		}

		$responsestatus = '';
		if ( $status !== null ) {
			if ( gettype($status) === 'array' ) {
				foreach ( $status as $key => $value ) {
					$responsestatus .= ' // api-' . $key . '=' . $value;
					header('api-' . $key . ': ' . $value);
				}
			} else {
				header('api-status: ' . $status);
				$responsestatus = $status;
			}
		}

		if ( $responsestatus ) {
			\errlog(PHP_EOL . 'RESPONSE: ' . $code . ' ' . $responsestatus);
		} else {
			\errlog(PHP_EOL . 'RESPONSE: ' . $code);
		}
		http_response_code($code);

		if ( $data !== null ) {
			header('Content-Type: Application/json');
			echo json_encode($data);
		}

		return true;
	}

	public static function fail($error) {
		\errlog(PHP_EOL . 'RESPONSE: FAIL');
		$response = array();
		$response['status'] = 'fail';
		$response['time']   = time();
		$response['description'] = $error;
		self::output($response);
		die();
	}

	public static function pass($data = null) {
		\errlog(PHP_EOL . 'RESPONSE: PASS');
		if ( gettype($data) !== 'array' ) {
			$data = (array)$data;
		}
		$response = array(
			'status' => 'pass',
			'time'   => time()
		);

		$response['data'] = $data;
		self::output($response);
		die();
	}

	protected static function output($data) {
		self::keys_to_lower($data);

		if ( gettype($data) === 'array' && count($data) === 1 && isset($data[0]) && count(URI::segments()) > 2 ) {
			$data = $data[0];
		}

		// $data = self::xml_array($data, substr(URI::segment(1), 0, strlen(URI::segment(1)) - 1));
		if ( Input::get('output') === 'xml' ) {
			$xml = new \SimpleXMLElement('<response/>');
			$data = self::recursive_array($data);
			self::array_to_xml($data, $xml);
			header('Content-Type: text/xml');
			echo($xml->asXML());
			return true;

		} else {
			$output = '';
			if ( Input::get('callback') ) {
				$output = Input::get('callback') . '(';
			}

			if ( !$data ) {
				$data = (object)array();
			}
			$output .= json_encode($data);
			if ( Input::get('callback') ) {
				$output .= ')';
			}

			header('Content-Type: application/json');
			echo($output);
			errlog(PHP_EOL . $output);

			return true;
		}
	}

	public static function xml_array($array, $root_element = null ) {
		$count = 0;
		foreach ( $array as $key => $value ) {
			if ( $key === 'categories' ) {
				foreach ( $value as $catkey => $single ) {
					foreach ( $single['questions'] as &$question ) {
						$question = array('question' => $question);
					}

					$single = array('category' => $single);

					$value[$catkey] = $single;
				}

				$array[$key] = $value;
			} else if ( $key === 'questions' ) {
				foreach ( $value as &$single ) {
					$single = array('question' => $single);
				}

				$array[$key] = $value;
			}

			$array[$key] = array($root_element => $value);
		}

		return $array;
	}

	protected static function xml_wrap($array, $element) {
		array_walk($array, function(&$single) use ($element) {
			$single = array($element => $single);
		});

		return $array;
	}

	public static function keys_to_lower(&$array) {
		$array = self::to_array($array);

		$array = array_change_key_case($array, CASE_LOWER);
		array_walk_recursive($array, function(&$single) {
			$single = \API\lib\Response::to_array($single);
			if ( is_array($single) ) {
				$single = array_change_key_case($single, CASE_LOWER);
			}
		});

		$tmp = json_encode($array);
		$array = json_decode($tmp);

		return $array;
	}

	public static function to_array($object) {
		if ( is_object($object) ) {
			return (array)$object;
		}
		return $object;
	}

	public static function recursive_array(&$array) {
		array_walk($array, function(&$single) {
			if ( is_object($single) ) {
				$single = (array)$single;
			}

			if ( is_array($single) ) {
				Response::recursive_array($single);
			}
		});
		$array = (array)$array;
		return $array;
	}

	// protected static function wrap_array($array, $element) {
		// array_walk($array, function(&$single) use ($element) {
		// 	$single = array($element => $single);
		// });

		// return $array;
	// }

	protected static function array_to_xml($input, &$output) {
		foreach ( $input as $key => $value ) {
			if ( is_array($value) ) {
				if ( !is_numeric($key) ) {
					$subnode = $output->addChild("$key");
					self::array_to_xml($value, $subnode);
				} else {
					self::array_to_xml($value, $output);
				}
			} else {
				$output->addChild("$key", "$value");
			}
		}
	}
}
