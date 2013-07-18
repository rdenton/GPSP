<?php

/*
 *	@package gpsp
 * 	@version 0.1.2
 * 	@author Richard Denton
 * 
 * 	Finds the most recent GPGGA location from a raw GPS log file.
 * 	This can run in real time, on the assumption live GPS logging has been started.
 * 	Initiate GPS logging before running with something like
 * 	sudo cat /dev/usbTTY0 > ~/Desktop/gps.log 
 * 	Where /dev/usbTTY0 is your raw GPS source.
 * 
*/

define ("gps_log",				"~/Desktop/gps.log");

require_once 'lib/gpsp.php';

$gpsp = new gpsp;

while (1) {

	$fh = fopen(gps_log, "r");
	$file_data = fread($fh, filesize(gps_log));
	fclose($fh);
	
	//Split the file into lines.
	$lines = explode("\n", $file_data);
	
	$most_recent_entry = 0;	
	$most_recent_array = null;
	
	foreach($lines as $line) {
		
		if ($line != null) {
			if ($gpsp->isProtocol("GPGGA", $line)) {
				$array = $gpsp->parseLine($line);
				if ($array) {
					
					if ($array["time_raw"] > $most_recent_entry) {
						//New most recent entry.
						$most_recent_entry = $array["time_raw"];
						$most_recent_array = $array;
					}
					
					
				}
			}
		}
	}
	
	print($most_recent_array["latitude_decimal"] . ", " . $most_recent_array["longtitude_decimal"] . " Alt: " . $most_recent_array["altitude"] . "\n" );
	
	sleep(1);
	
}
?>