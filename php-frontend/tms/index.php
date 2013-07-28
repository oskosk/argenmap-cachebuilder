<?php
function tms($capa,$z,$x,$y,$format)
{
require_once('KLogger.php');
require_once('cache.php');
require_once('argenmap_cache.php');

// un año en segundos
define('CACHE_TTL',  31536000);

$cache = new Argenmap_Cache('tms/cache');


$baseURL = 'http://mapa.ign.gob.ar/geoserver/gwc/service/tms/1.0.0';
$tileURL = $baseURL;

$capa = $capa . '@EPSG:3857@png8';
//apendeo el 8 para pedir png8
$tileURL = sprintf("%s/%s/%s/%s/%s.%s", $baseURL, $capa, $z, $x, $y,$format."8");

$bytes_sent = NULL;
if ($bytes_sent = $cache->traerTile($tileURL) ) {
	$cache->logTileServida($z, $x, $y);
} else {
	$cache->LogError("\tNo se pudo leer la tile desde el servicio TMS remoto:\t%s\t%x\%y", $z, $x, $y);	
}

	return $bytes_sent;
}



?>
