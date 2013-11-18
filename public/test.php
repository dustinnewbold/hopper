<?php

$fh = fopen('/tmp/gps.txt', 'w');


fwrite($fh, '<xml><tracking>');
for ( $i = 0; $i < 17000; $i++ ) {
	fwrite($fh, '<gps><latitude>10.1401401014</latitude><longitude>106.140140104</longitude></gps>');
}
fwrite($fh, '</tracking>');
fclose($fh);

echo('done');