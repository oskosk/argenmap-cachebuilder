<?php


function auth_admin()
{
	global $CONFIG, $app;
	$admin_key = trim(@$CONFIG['admin_key']);
	$client_key = @$_GET['key'];
	if ($admin_key == '') {
		$app->halt(401);	
	}
	if ($admin_key != $client_key) {
		$app->halt(401);
	}
	
}