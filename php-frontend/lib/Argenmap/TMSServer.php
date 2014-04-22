<?php

namespace Argenmap;

class TMSServer {
  
  static function tms($capa,$z,$x,$y,$format)
  {

    // un año en segundos
    define('CACHE_TTL',  31536000);

    $cache = new \Argenmap\Cache();

    $baseURL = 'http://mapa.ign.gob.ar/geoserver/gwc/service/tms/1.0.0';

    $capa = $capa . '@EPSG:3857@png8';
    //apendeo el 8 para pedir png8
    $tileURL = sprintf("%s/%s/%s/%s/%s.%s", $baseURL, $capa, $z, $x, $y,$format."8");

    $bytes_sent = NULL;
    if ($bytes_sent = $cache->traerTile($tileURL) ) {
      $cache->logTileServida($z, $x, $y);
    } else {
      $cache->logger->LogError("\tNo se pudo leer la tile desde el servicio TMS remoto:\t%s\t%x\%y", $z, $x, $y); 
    }

      return array(
        'bytes_sent' => $bytes_sent,
        'ETag' => $cache->calcularETag($tileURL)
      );

  }

  
}