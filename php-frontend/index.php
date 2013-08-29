<?php

require "config.inc.php";
require "lib/php-autoloader/autoloader.php";
//require "lib/argenmap_cache_stats.php";
require "lib/Slim/Slim.php";

require "controllers/status.php";

date_default_timezone_set('America/Argentina/Buenos_Aires');

$app = new Slim( array(
  'debug'=>true
));

$app->notFound(function () use ($app) {
    $app->render( '404.html');
});

$app->get('/tms/:capa/:z/:y/:x\.:format', 'Controllers\TMS::get');
$app->get('/tms/:capa/:z/:y/:x\.:format/status.json', 'Controllers\TMS::status');
$app->get('/cache/truncate', '\Argenmap\Auth::isAdmin', '\Controllers\Cache::truncate');
$app->get('/cache/status.json', '\Argenmap\Auth::isAdmin', '\Controllers\Cache::status');

$app->get('/logs/ultimosrequests.json', '\Argenmap\Auth::isAdmin', 'Controllers\Logs::ultimos_requests');
$app->get('/logs/disponibles.json', '\Argenmap\Auth::isAdmin', 'Controllers\Logs::logs_disponibles');
$app->get('/logs/:date/requests.json', '\Argenmap\Auth::isAdmin', 'Controllers\Logs::requests_por_date');
$app->get('/logs/:date/errors.json', '\Argenmap\Auth::isAdmin', 'Controllers\Logs::errors_por_date');

$app->get('/live/poll', '\Controllers\Live::live_poll');
$app->get('/live', '\Controllers\Live::live');
$app->get('/', '\Controllers\Live::live_consola');

$app->get('/stats/estenodo/:date/requests', 'stats_este_nodo_requests_por_date');
$app->get('/stats/estenodo/requests', 'stats_estenodo_requests');
$app->get('/stats/ultimosrequests', 'stats_ultimos_requests');


$app->get('/status/estenodo', 'status_estenodo');

$app->run();


