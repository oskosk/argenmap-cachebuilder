<?php
require(dirname(__FILE__). "/../lib/KLogger.php");
require(dirname(__FILE__). "/../lib/cache.php");
require(dirname(__FILE__). "/../lib/argenmap_cache.php");

function cache_estenodo_truncate()
{
	global $app;
	try {
		$cache = new Argenmap_Cache(dirname(__FILE__). '/../../tms/cache');	
		$cache->truncate();
		$app->response()->header('Content-type', 'application/json');
		echo json_encode(array(
			'status'=>'OK'
		));
	} catch(Exception $e) {
		$app->response()->status(400);
		$app->response()->header('X-Status-Reason', $e->getMessage());
	}
	

}
