<?php

require('cache.php');

$q= $_SERVER['QUERY_STRING']; 
$tileUrl = 'http://wms.ign.gob.ar/geoserver/gwc/service/wms?' . $q;

define('TTL',  31536000);

$tile = traerTile($tileUrl) ;

header("Content-Type: image/png");
header("Content-Length: " . strlen(traerTile($tileUrl)) );
echo traerTile($tileUrl);

function traerTile($url)
{	
	$cache = new JG_Cache('./cache');
	// Agrego tiled=true para forzar el caché
	$f = $cache->get($url, TTL);
	
	if ($f === false || strlen($f) == 0) {
		$handler = curl_init($url);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
		$f = curl_exec($handler);
		curl_close($handler);
		$cache->set($url, $f);
	}
	unset($cache);
	return $f;
}


?>
