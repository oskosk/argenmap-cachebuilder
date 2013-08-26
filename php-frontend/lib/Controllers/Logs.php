<?php

namespace Controllers;

class Logs {

  static function dias_disponibles()
  {
    global $app;
    $logger = new \Argenmap\Logger();
    $lines = $logger->diasDisponibles();
    $app->response()->header('Content-Type', 'application/json');    
    echo json_encode($lines);
  }

  static function ultimos_requests($dont_echo=false)
  {
    global $CONFIG, $app;
    //var_dump($_SERVER);die();

    $thisNode = $CONFIG['este_nodo_url'];
    $logger = new \Argenmap\Logger();
    $lines = $logger->ultimosRequests();
    $uniq = array();
    $uniq_referers = array();
    
    $ret = array();

    $ret['este_nodo'] = $thisNode;
    $ret['requests'] = $lines; 

    $app->response()->header('Content-Type', 'application/json');
    if ( $dont_echo === false ) {
      echo json_encode($ret);    
    } else{
      return $ret;
    }
    
  }

  function requests_por_date($date)
  {
    global $CONFIG, $app;

    $thisNode = $CONFIG['este_nodo_url'];
    $stats = new \Argenmap\Cache\Stats();

    $lines = $stats->requestsPorDate($date);
    $clientes = $stats->clientesPorDate($date);
    $segundos = $stats->_segundosTotalesPorDate($date);  
    
    if (count($lines) == 0 || count($clientes) == 0) {
      $app->response()->header('Content-Type', 'application/json');
      echo json_encode(array());
      return;
    }

    $ret = array();
    $ret['este_nodo'] = $thisNode;
    $ret['count'] = count($lines);
    $ret['count_clientes'] = count($clientes);
    //Asumo que cada tile pesa 8KB
    //Tengo quecalcular un promedio real en base
    // a los requests reales.
    $ret['consumo_estimado'] = 8 * count($lines) . ' KB';
    $ret['segundos'] = $segundos; 
    $ret['ancho_de_banda_estimado'] = ( ( 8 * count($lines) ) /  $ret['segundos']). ' KB/S';
    
    $app->response()->header('Content-Type', 'application/json');  
    echo json_encode($ret);  
    

  }


  function stats_estenodo_requests()
  {
    global $CONFIG, $app;

    $thisNode = $CONFIG['este_nodo_url'];
    $stats = new \Argenmap\Cache\Stats();
    $log = $stats->_indexedLogLines();
    $retAll = array();
    foreach(array_keys($log['porDate']) as $date)
    {
      $lines = $stats->requestsPorDate($date);
      $clientes = $stats->clientesPorDate($date);

      $ret = array();
      $ret['este_nodo'] = $thisNode;
      $ret['fecha'] = $date;
      $ret['count'] = count($lines);
      $ret['count_clientes'] = count($clientes);
      //Asumo que cada tile pesa 8KB
      //Tengo quecalcular un promedio real en base
      // a los requests reales.
      $ret['consumo_estimado'] = 8 * count($lines) . ' KB';
      $ret['segundos'] = $stats->_segundosTotalesPorDate($date);  

      $ret['ancho_de_banda_estimado'] = ( ( 8 * count($lines) ) /  $ret['segundos']). ' KB/S';
      $retAll[] = $ret;  
    } 
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($retAll);
  }

 
}