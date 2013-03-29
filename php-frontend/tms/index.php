<?php

require('KLogger.php');
require('cache.php');
require('argenmap_cache.php');

// un año en segundos
define('CACHE_TTL',  31536000);

$cache = new Argenmap_Cache('./cache');


$q= @$_GET['q']; 

$baseURL = 'http://mapa.ign.gob.ar/geoserver/gwc/service/tms/1.0.0';
$tileURL = $baseURL;

$pieces = explode('/', $q);
if (count($pieces) != 4) {
	$cache->LogError("\tMALA_URL\t$_SERVER[QUERY_STRING]\t");
	die('hard');
}

$capa = $pieces[0] . '@EPSG:3857@png8';
$z = $pieces[1];
$x = $pieces[2];
$y = $pieces[3]; 
$y = explode('.', $y);
$format = $y[1];
$y=$y[0];
//apendeo el 8 para pedir png8
$tileURL = sprintf("%s/%s/%s/%s/%s.%s", $baseURL, $capa, $z, $x, $y,$format."8");

//$tile = traerTile($tileURL) ;

if ($cache->traerTile($tileURL) ) {
	$cache->logTileServida($z, $x, $y);
} else {
			$cache->LogError("\tNo se pudo leer la tile desde el servicio TMS remoto:\t%s\t%x\%y", $z, $x, $y);	
}






?>
