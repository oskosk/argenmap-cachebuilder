<?php
	include('este_nodo_ping.php');
	echo "\n";
	echo file_get_contents('http://sig.ign.gob.ar/consola/ping.php');
	echo "\n";
	echo file_get_contents('http://190.220.8.216/consola/ping.php');

