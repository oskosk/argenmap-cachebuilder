<?php

require('cache.php');
require('argenmap_cache.php');
require('KLogger.php');
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
$y = $pieces[3]; 
$y = explode('.', $y);
$format = $y[1];
$y=$y[0];
//apendeo el 8 para pedir png8
$tileURL = sprintf("%s/%s/%s/%s/%s.%s", $baseURL, $capa, $z, $x, $y,$format."8");

//$tile = traerTile($tileURL) ;

traerTile($tileURL);
logIt($z, $x, $y);

function logIt($z, $x, $y)
{
	$log = new KLogger ( "logs/log.txt" , KLogger::DEBUG );
	$ip='';
	$referer='';
	$forwarded_for = '';
	if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ){
  		$forwarded_for  = $_SERVER["HTTP_X_FORWARDED_FOR"];
  		$ip = $_SERVER["REMOTE_ADDR"];
	} else {
  		$ip = $_SERVER["REMOTE_ADDR"];
	}
	if ( isset($_SERVER["HTTP_REFERER"]) ){
  		$referer = $_SERVER["HTTP_REFERER"];
	}

	$log->LogInfo("\t$z\t$x\t$y\t$referer\t$ip\t$forwarded_for");
}

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
		$f = $cache->getAndPassthru($url, CACHE_TTL);
	}
	unset($cache);
	return $f;
}


?>
