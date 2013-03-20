<?php

require('cache.php');
require('argenmap_cache.php');
// un año en segundos
define('CACHE_TTL',  31536000);

$q= $_GET['q']; 

$baseURL = 'http://mapa.ign.gob.ar/geoserver/gwc/service/tms/1.0.0';
$tileURL = $baseURL;

$pieces = explode('/', $q);
if (count($pieces) != 4) {
	die('hard');
}

$capa = $pieces[0] . '@EPSG:3857@png8';
$z = $pieces[1];
$x = $pieces[2];
$y = $pieces[3] . '8'; //apendeo el 8 para pedir png8
$tileURL = sprintf("%s/%s/%s/%s/%s", $baseURL, $capa, $z, $x, $y);



//$tile = traerTile($tileURL) ;

header("Content-Type: image/png");
header("Content-Length: " . strlen(traerTile($tileURL)) );
echo traerTile($tileURL);

function traerTile($url)
{	
	$cache = new Argenmap_Cache('./cache');
	
	$f = $cache->getAndPassthru($url, CACHE_TTL);
	
	if ($f === false || strlen($f) == 0) {
		$handler = curl_init($url);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
		$f = curl_exec($handler);
		curl_close($handler);
		$cache->setRaw($url, $f);
	}
	unset($cache);
	return $f;
}


?>
