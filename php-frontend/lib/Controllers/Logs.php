<?php

namespace Controllers;

class Logs {

  static function logs_disponibles()
  {
    global $app;
    $logger = new \Argenmap\Logger();
    $lines = $logger->logsDisponibles();
    $app->response()->header('Content-Type', 'application/json');    
    $lines['requests'] = array_map(function($v) {
      global $app;
      $v['url'] = $app->request()->getUrl() . $app->request()->getRootUri() . "/logs/$v[date]/requests.json";
      return $v;
    }, $lines['requests']);
    $lines['errors'] = array_map(function($v) {
      global $app;
      $v['url'] = $app->request()->getUrl() . $app->request()->getRootUri() . "/logs/$v[date]/errors.json";
      return $v;
    }, $lines['errors']);    
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

  static function requests_por_date($date)
  {
    global $CONFIG, $app;

    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ){
      $app->response()->status(400);
      $app->response()->header('X-Status-Reason', 'Malformed date');
    }

    $thisNode = $CONFIG['este_nodo_url'];
    $logger = new \Argenmap\Logger();

    $lines = $logger->requestsPorDate($date);
    //$clientes = $stats->clientesPorDate($date);
    //$segundos = $stats->_segundosTotalesPorDate($date);  
    
    $app->response()->header('Content-Type', 'application/json');  
    echo json_encode($lines);  
  }

  static function requests_por_date_txt($date)
  {
    global $CONFIG, $app;

    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ){
      $app->response()->status(400);
      $app->response()->header('X-Status-Reason', 'Malformed date');
    }

    $thisNode = $CONFIG['este_nodo_url'];
    $logger = new \Argenmap\Logger();

    $txt = $logger->requestsPorDateTxt($date);
 
    
    $app->response()->header('Content-Type', 'text/plain');  
    echo $txt;  
  }

  static function errors_por_date($date)
  {
    global $CONFIG, $app;

    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ){
      $app->response()->status(400);
      $app->response()->header('X-Status-Reason', 'Malformed date');
    }

    $thisNode = $CONFIG['este_nodo_url'];
    $logger = new \Argenmap\Logger();

    $lines = $logger->errorsPorDate($date);
    //$clientes = $stats->clientesPorDate($date);
    //$segundos = $stats->_segundosTotalesPorDate($date);  
    
    $app->response()->header('Content-Type', 'application/json');  
    echo json_encode($lines);  
  }

  static function errors_por_date_txt($date)
  {
    global $CONFIG, $app;

    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date) ){
      $app->response()->status(400);
      $app->response()->header('X-Status-Reason', 'Malformed date');
    }

    $thisNode = $CONFIG['este_nodo_url'];
    $logger = new \Argenmap\Logger();

    $txt = $logger->errorsPorDateTxt($date);
    
    $app->response()->header('Content-Type', 'text/plain');  
    echo $txt;  
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