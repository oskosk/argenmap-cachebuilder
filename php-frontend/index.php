<?php

require "config.inc.php";
require "lib/php-autoloader/autoloader.php";
//require "lib/argenmap_cache_stats.php";
require "lib/Slim/Slim.php";

require "controllers/auth.php";
require "controllers/stats.php";
require "controllers/cache.php";
require "controllers/status.php";


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
  $app->etag(md5("$capa-$z-$x-$y-$format"));
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
$app->get('/live/poll', 'live_poll');
$app->get('/live', 'live');
$app->get('/', 'live_consola');
$app->run();


function live_consola()
{
  global $app;
	require "controllers/live.php";
}

function live()
{ 
  global $app;
  $usarSesion=false;
  require "lib/argenmap_live.php";  
}

function live_poll()
{ 
  global $app;
  $usarSesion=true;
  require "lib/argenmap_live.php";
}