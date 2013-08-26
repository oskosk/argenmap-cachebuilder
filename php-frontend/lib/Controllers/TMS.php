<?php

namespace Controllers;

class TMS {

  static function get($capa, $z, $x, $y, $format)
  {
    global $app;
    
    if (!in_array($format, array('png') ) ) {
      $app->notFound();
    }

    $response = \Argenmap\TMSServer::tms( $capa, $z, $x, $y, 'png' );
    $bytes_sent = $response['bytes_sent'];
    $ETag = $response['ETag'];

    if ( $bytes_sent ) {
      $app->etag($ETag);
      $res = $app->response();
      $res["Content-Type"] = "image/png";
      $res["Content-Length"] = $bytes_sent;
    } else {
      $app->error('No se pudo conseguir la tile de la capa ' . $capa);
    }  
  } 

  static function status($capa, $z, $x, $y, $format)
  {
    global $app;
    
    $cache = new \Argenmap\Cache();
    $response = $cache->tileStatus( $capa, $z, $x, $y, $format );
    
    if ( $response ) {
      $res = $app->response();
      $res["Content-Type"] = "application/json";
      echo json_encode($response);
    } else {
      $app->error('No se pudo conseguir la tile de la capa ' . $capa);
    }  


  }   
}