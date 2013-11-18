<?php

namespace API\lib;

/**
 * This class is designed to interact with request variables.
 * Example: GETs, POSTs, etc.
 */

class Event {
	public static function trigger($event, $data = null) {
		global $events;

		$func = $events->$event;

		if ( $data === null ) {
			$func();
		} else {
			$func($data);
		}
	}

	public static function create($event, $function) {
		global $events;
		if ( !$events ) {
			$events = new \stdClass();
		}

		global $events;
		$events->$event = $function;
	}
}


// Create your events here
Event::create('site_created', function($data) {
	dd($data);
});