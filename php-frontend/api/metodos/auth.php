<?php

function auth_admin()
{
	global $app;
	$app->halt(401);
}