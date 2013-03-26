<?php

require "../../config.inc.php";
require "../argenmap_cache_stats.php";
require "Slim/Slim.php";

$app = new Slim( array(
  'debug'=>true
));

$app->get('/stats/ping', 'ping');
$app->get('/stats/este_nodo_ping', 'este_nodo_ping');
$app->get('/stats/estenodo/:datetime/requests/referers', 'requests_por_datetime_referers_este_nodo');
$app->get('/stats/estenodo/:datetime/requests/resumen', 'requests_resumen_por_datetime_este_nodo');
$app->get('/stats/estenodo/:datetime/requests', 'requests_por_datetime_este_nodo');
$app->get('/stats/estenodo/ultimosrequests', 'ultimos_requests_este_nodo');
$app->get('/stats/ultimosrequests', 'ultimos_requests');



function ping() {
  global $CONFIG;
  
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
    echo json_encode($relaciones);
    echo json_encode($nodos);
}

function ultimos_requests() {
  global $CONFIG;
  
  //include('../este_nodo_ping.php');
  $requests = array();
  $nodos = array();
  $nodos_url = $CONFIG['otros_nodos_url'];
  $nodos_url[] = $CONFIG['este_nodo_url'];
  foreach ($nodos_url as $url) {
    $a = json_decode(file_get_contents($url . '/consola/api/stats/estenodo/ultimosrequests'),true);
    $requests = array_merge($relaciones, $a['requests']);

  }
    echo json_encode($relaciones);
}

function requests_por_datetime_este_nodo($datetime)
{
  global $CONFIG;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();
  $lines = $stats->requestsPorDateTime($datetime);
  
  $ret = array();

  $ret['este_nodo'] = $thisNode;
  $ret['count'] = count($lines);
  $ret['consumo_estimado'] = 8 * count($lines) . ' KB';
  $ret['requests'] = $lines; 
  
  echo json_encode($ret);  
  return json_encode($ret);

}

function requests_resumen_por_datetime_este_nodo($datetime)
{
  global $CONFIG;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();

  $lines = $stats->requestsPorDateTime($datetime);
  $clientes = $stats->clientesPorDateTime($datetime);

  $ret = array();
  $ret['este_nodo'] = $thisNode;
  $ret['count'] = count($lines);
  $ret['count_clientes'] = count($clientes);
  //Asumo que cada tile pesa 8KB
  //Tengo quecalcular un promedio real en base
  // a los requests reales.
  $ret['consumo_estimado'] = 8 * count($lines) . ' KB';
  $ret['segundos'] = $stats->_segundosTotalesPorDateTime($datetime);  

  $ret['ancho_de_banda_estimado'] = ( ( 8 * count($lines) ) /  $ret['segundos']). ' KB/S';
    
  echo json_encode($ret);  
  return json_encode($ret);

}

function requests_por_datetime_referers_este_nodo($datetime)
{
  global $CONFIG;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();
  $lines = $stats->referersPorDateTime($datetime);
  
  $ret = array();

  $ret['este_nodo'] = $thisNode;
  $ret['count'] = count($lines);
  $ret['referers'] = $lines; 
  
  echo json_encode($ret);  
  return json_encode($ret);

}

function ultimos_requests_este_nodo()
{
  global $CONFIG;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();
  $lines = $stats->ultimosRequests();
  $uniq = array();
  $uniq_referers = array();
  
  $ret = array();

  $ret['este_nodo'] = $thisNode;
  $ret['requests'] = $lines; 
  
  echo json_encode($ret);  
  return json_encode($ret);

  foreach ($lines as $ll) {
    $referer = $ll['client']['referer'];
    $ip = $ll['client']['ip'];
    $private_ip = $ll['client']['forwarded_for'];
    if ( $ip &&  $private_ip ) {
      $private_ip = "IP privada N/D";
    }
    if (! $ip ) {
      $ip = "IP pública N/D. Raro";
    }
    $tein_proxy = ($private_ip != 'IP privada N/D');
      if ($referer) {
        if ($tein_proxy) {
          $uniq[] = "$referer -> Proxy $ip  {color:#218559, weight:2}"; 
        $uniq[] = "$thisNode -> Proxy $ip {color:#192823, weight:5}";
        $uniq[] = "Proxy $ip -> $private_ip  {color:#D0C6B1, weight:2}";
        } else {
          $uniq[] = "$referer -> $ip  {color:#218559, weight:2}"; 
        $uniq[] = "$thisNode -> $ip {color:#192823, weight:5}";
        }
      
      $uniqnodes[] = "$referer {color:#218559} ";
    } else {
      if ($tein_proxy) {
        $uniq[] = "Proxy $ip -> $private_ip {color:red, weight:2}";
        $uniq[] = "$thisNode -> Proxy $ip {color:red, weight:5}";
      } else {
        $uniq[] = "$thisNode -> $ip {color:red, weight:5}";
      }
    }
    if ($tein_proxy) {
      $uniqnodes[] = "Proxy $ip {color:#D0C6B1}";
      $uniqnodes[] = "$private_ip {color:#EBB035}";
    } else {
      $uniqnodes[] = "$ip {color:#EBB035}";
    }

  }

}

$app->run();

