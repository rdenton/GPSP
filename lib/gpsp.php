<?php

/*
 *	@package gpsp
 * 	@version 0.1.2
 * 	@author Richard Denton
 */

class gpsp {
	
	function __construct() {
		//Do stuff.
	}
	
	function parseLine($sGPSLine) {
		
		//Check if the line supplied is a valid supported GPS line.
		//$GPGGA,023809.000,3749.7211,S,14515.4549,E,1,09,1.0,162.5,M,-3.6,M,,0000*55
		$aLineEntries = explode(",", $sGPSLine);
		if ( count($aLineEntries) < 2 ) {
			//Impossible entry. All GPS signals require at least one comma.
			$this->error("Invalid GPS Line supplied. All GPS protocols require at least one comma.",true);
		}
		
		//Check the first set in the array.
		//This should be the protocol line.
		//Ensure the protocol specified is supported.
		if ( $aLineEntries[0] != '$GPGGA' ) {
			//Non-supported protocol entered.
			$this->error("Protocol supplied is not valid, '" . $aLineEntries[0] . "'.", true);
		}
		
		$aReturnBuffer = null;
		
		//At this stage, any protocol that has been specified should be supported.
		//We should be safe to parse work on to specific functions now.
		switch ($aLineEntries[0]) {
			case '$GPGGA':
				$aReturnBuffer = $this->parseGPGGA($sGPSLine);
				break;
		}
		
		return $aReturnBuffer;
		
	}
	
	function isProtocol($sProtocol,$sLine) {
		switch ($sProtocol) {
			case "GPGGA":
				$aColumns = explode(",", $sLine);
				if ( count($aColumns) != 15 ) {
					return false;
				} else {
					return true;
				}
				break;
		}
		
		return false;
	}
	
	function parseGPGGA($sGPGGALine) {
		
		//Parse a GPGGA GPS Line and return it as an array.
		
		if (!$this->isProtocol("GPGGA",$sGPGGALine)) {
			die("Wrong protocol called.");
		}
		
		$aColumns = explode(",", $sGPGGALine);
		
		//Add each entry to our return array.
		$aReturnGPGGA = array(
			"protocol"		=>		$aColumns[0],
		);
		
		//Parse the timestamp.
		$aTime = explode(".", $aColumns[1]);
		if ( count($aTime) < 2 ) {
			//No micro seconds
			$aReturnGPGGA["timestamp"] = substr($aColumns[1],0,2) . ":" . substr($aColumns[1],2,2) . ":" . substr($aColumns[1],4,2);
		} else {
			//Microseconds!kittycat1
			
			$aReturnGPGGA["timestamp"] = substr($aColumns[1],0,2) . ":" . substr($aColumns[1],2,2) . ":" . substr($aColumns[1],4,2) . ":" . $aTime[1];
		}
		
		$aReturnGPGGA["time_raw"] = $aColumns[1];
		
		//Latitude and longtitude.
		$aReturnGPGGA["latitude"] = $aColumns[2] . ", " . $aColumns[3];
		$aReturnGPGGA["longtitude"] = $aColumns[4] . ", " . $aColumns[5];
		
		//Decimals.
		$aReturnGPGGA["latitude_decimal"] = $this->degreesToDecimal($aColumns[2], $aColumns[3]);
		$aReturnGPGGA["longtitude_decimal"] = $this->degreesToDecimal($aColumns[4], $aColumns[5]);
		
		//Clean line (Useful for maps)
		$aReturnGPGGA["coordinates"] = $aReturnGPGGA["latitude_decimal"] . ", " . $aReturnGPGGA["longtitude_decimal"];
		
		//Fix quality
		$aReturnGPGGA["fix_quality"] = $aColumns[6];
		if ($aColumns[6] == "1") {
			$aReturnGPGGA["has_fix"] = "true";
		} else {
			$aReturnGPGGA["has_fix"] = "false";
		}
		
		//Satellite connections
		$aReturnGPGGA["satellites_connected"] = $aColumns[7];
		
		//Horizontal dilution of precision
		$aReturnGPGGA["hdop"] = $aColumns[8];
		
		//Altitude
		$aReturnGPGGA["altitude"] = $aColumns[9];
		$aReturnGPGGA["altitude_feet"] = ($aColumns[9] * 3.2808399);
		
		//GeoID Height
		$aReturnGPGGA["geoid_height"] = $aColumns[11];
		$aReturnGPGGA["geoid_height_feet"] = ($aColumns[11] * 3.2808399);
		
		
		return $aReturnGPGGA;
		
		
	}
	
	function degreesToDecimal($fDeg, $cDirection)
	{
		$degree=(int)($fDeg/100); //simple way
		$minutes= $fDeg-($degree*100);
		$dotdegree=$minutes/60;
		$decimal=$degree+$dotdegree;
		
		//South latitudes and West longitudes need to return a negative result
		if (($cDirection=="S") or ($cDirection=="W"))
		{ $decimal=$decimal*(-1);}
		$decimal=number_format($decimal,4,'.',''); //truncate decimal to 4 places
		return $decimal;
	}
	
	function error($sErrorMsg, $bFatal=false) {
		
		if ($bFatal == true) {
			print("Fatal: " . $sErrorMsg . "\n");
			
		} else {
			print("Warning: " . $sErrorMsg . "\n");
		}
		
	}
	
}

?>