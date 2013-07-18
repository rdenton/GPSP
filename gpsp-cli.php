<?php

require_once 'lib/gpsp.php';

$line = '$GPGGA,023843.000,3749.7141,S,14515.4581,E,1,09,1.0,150.5,M,-3.6,M,,0000*58';

$gpsp = new gpsp;
$array = $gpsp->parseLine($line);

print_r($array);

?>