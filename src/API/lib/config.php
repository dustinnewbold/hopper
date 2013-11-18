<?php

namespace API\lib;

class Config {
	public static function get($var) {
		global $config;
		return $config[$var];
	}
}
