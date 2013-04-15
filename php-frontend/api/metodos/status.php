<?php

function status_estenodo()
{
	global $CONFIG, $app;
	$stats = new ArgenmapCacheStats();
	try {
		$estenodo = array(
			'status'=> 'UP',
			'url'=> $CONFIG['este_nodo_url'],
			'otros_nodos_url'=> $CONFIG['otros_nodos_url'],
			'cache_disk_usage'=> $stats->cacheDiskUsage(),
			'oldcaches_disk_usage'=> $stats->oldCachesDiskUsage()
		);		
	  $app->response()->header('Content-Type', 'application/json');  
		echo json_encode($estenodo);		
	} catch (Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}

}

