<?php

function stats_ping() {
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


function stats_este_nodo_requests_por_date($date)
{
  global $CONFIG;

  $thisNode = $CONFIG['este_nodo_url'];
  $stats = new ArgenmapCacheStats();

  $lines = $stats->requestsPorDate($date);
  $clientes = $stats->clientesPorDate($date);

  $ret = array();
  $ret['este_nodo'] = $thisNode;
  $ret['count'] = count($lines);
  $ret['count_clientes'] = count($clientes);
  //Asumo que cada tile pesa 8KB
  //Tengo quecalcular un promedio real en base
  // a los requests reales.
  $ret['consumo_estimado'] = 8 * count($lines) . ' KB';
  $ret['segundos'] = $stats->_segundosTotalesPorDate($date);  

  $ret['ancho_de_banda_estimado'] = ( ( 8 * count($lines) ) /  $ret['segundos']). ' KB/S';
    
  echo json_encode($ret);  
  return json_encode($ret);

}