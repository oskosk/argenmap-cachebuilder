<?php

require "config.inc.php";
require "lib/argenmap_cache_stats.php";
require "lib/Slim/Slim.php";

require "lib/api/metodos/auth.php";
require "lib/api/metodos/stats.php";
require "lib/api/metodos/cache.php";
require "lib/api/metodos/status.php";


$app = new Slim( array(
  'debug'=>true
));

$app->notFound(function () use ($app) {
    $app->render( '404.html');
});

$app->get('/tms/:capa/:z/:y/:x\.:format', 'tms_get');

function tms_get($capa, $z, $x, $y, $format)
{
  global $app;
  require "lib/tms_slim_handler.php";
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

$app->get('/api/stats/estenodo/:date/requests', 'stats_este_nodo_requests_por_date');
$app->get('/api/stats/estenodo/requests', 'stats_estenodo_requests');
$app->get('/api/stats/estenodo/ultimosrequests', 'stats_estenodo_ultimos_requests');
$app->get('/api/stats/ultimosrequests', 'stats_ultimos_requests');

$app->get('/api/cache/truncate', 'cache_truncate');
$app->get('/api/cache/estenodo/truncate', 'auth_admin', 'cache_estenodo_truncate');

$app->get('/api/status/estenodo', 'status_estenodo');
$app->get('/', 'indexchris');
$app->run();

function indexchris()
{
	require "indexchris.php";
}
