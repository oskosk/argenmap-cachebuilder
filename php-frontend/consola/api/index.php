<?php

require "../../config.inc.php";
require "../argenmap_cache_stats.php";
require "Slim/Slim.php";

require "metodos/stats.php";

$app = new Slim( array(
  'debug'=>true
));

$app->get('/stats/ping', 'stats_ping');
$app->get('/stats/estenodo/:date/requests', 'stats_este_nodo_requests_por_date');
$app->get('/stats/estenodo/requests', 'stats_estenodo_requests');
$app->get('/stats/estenodo/ultimosrequests', 'stats_estenodo_ultimos_requests');
$app->get('/stats/ultimosrequests', 'stats_ultimos_requests');

$app->run();

