<?php

function stats_ping() {
  global $CONFIG, $app;
  
  //include('../este_nodo_ping.php');
  $relaciones = array();
  $nodos = array();
  $nodos_url = $CONFIG['otros_nodos_url'];
  $nodos_Url[] = $CONFIG['este_nodo_url'];
  foreach ($nodos_url as $url) {
    $a = json_decode(file_get_contents($url . '/consola/api/stats/este_nodo_ping'),true);
    $relaciones = array_merge($relaciones, $a['relaciones']);
    $nodos = array_merge($nodos, $a['nodos']);

  }
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($relaciones);
    echo json_encode($nodos);
}




function stats_este_nodo_requests_por_date($date)
{
  global $CONFIG, $app;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();

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
  $stats = new ArgenmapCacheStats();
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

function stats_ultimos_requests() {
  global $CONFIG, $app;
  
  $requests = array();
  $nodos = array();
  $nodos_url = $CONFIG['otros_nodos_url'];
  $requests[] = stats_estenodo_ultimos_requests(true);
  foreach ($nodos_url as $url) {
    $remote = file_get_contents($url . '/consola/api/stats/estenodo/ultimosrequests');

    $a = json_decode($remote,true);

    $requests[] = $a;

  }
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode($requests);
}

function stats_estenodo_ultimos_requests($dont_echo=false)
{
  global $CONFIG, $app;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();
  $lines = $stats->ultimosRequests();
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