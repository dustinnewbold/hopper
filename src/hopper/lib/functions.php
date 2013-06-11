<?php

function dd($object) {
	$bt = debug_backtrace();
	echo('<pre>');
	echo($bt[0]['file'] . ':' . $bt[0]['line'] . PHP_EOL);
	print_r($object);
	echo('</pre>');
	die();
}

function errlog($string, $newline = true) {
	if ( gettype($string) === 'object' ) {
		dd($string);
	}
	$string = (string)$string;
	if ( $newline === true ) {
		$string .= PHP_EOL;
	}

	$fh = fopen(__DIR__ . '/../../../../apilog.txt', 'a');
	fwrite($fh, $string);
	fclose($fh);
}
