<?php

require "config.inc.php";
require "api/argenmap_cache_stats.php";
require "api/Slim/Slim.php";

require "api/metodos/auth.php";
require "api/metodos/stats.php";
require "api/metodos/cache.php";
require "api/metodos/status.php";


$app = new Slim( array(
  'debug'=>true
));

$app->notFound(function () use ($app) {
    $app->render( '404.html');
});

$app->get('/ptms/:capa/:z/:y/:x\.:format', 'tms_get');

function tms_get($capa, $z, $x, $y, $format)
{
  global $app;
  require "tms/index.php";
  if (!in_array($format, array('png') ) ) {
    $app->notFound();
  }
  $bytes_sent = tms( $capa, $z, $x, $y, 'png' );
  if ( $bytes_sent ) {
    $res = $app->response();
    $res["Content-Type"] = "image/png";
    $res["Content-Length"] = $bytes_sent;
  } else {
    $app->error('No se pudo conseguir la tile de la capa ' . $capa);
  }  


} 
$app->get('/stats/estenodo/:date/requests', 'stats_este_nodo_requests_por_date');
$app->get('/stats/estenodo/requests', 'stats_estenodo_requests');
$app->get('/stats/estenodo/ultimosrequests', 'stats_estenodo_ultimos_requests');
$app->get('/stats/ultimosrequests', 'stats_ultimos_requests');

$app->get('/cache/truncate', 'cache_truncate');
$app->get('/cache/estenodo/truncate', 'auth_admin', 'cache_estenodo_truncate');

$app->get('/status/estenodo', 'status_estenodo');
$app->run();

