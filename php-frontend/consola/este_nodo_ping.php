<?php

require_once "argenmap_cache_stats.php";

$thisNode = "http://www.ign.gob.ar/tms";
$stats = new ArgenmapCacheStats();
$lines = $stats->ultimosRequests();
$uniq = array();
$uniqnodes = array();
foreach ($lines as $ll) {
	$referer = $ll['client']['referer'];
	$ip = $ll['client']['ip'];
	$private_ip = $ll['client']['forwarded_for'];
	if ( $ip &&  $private_ip ) {
		$private_ip = "IP privada N/D";
	}
	if (! $ip ) {
		$ip = "IP pública N/D. Raro";
	}
	$tein_proxy = ($private_ip != 'IP privada N/D');
  	if ($referer) {
  		if ($tein_proxy) {
  			$uniq[] = "$referer -> Proxy $ip  {color:#218559, weight:2}";	
			$uniq[] = "$thisNode -> Proxy $ip {color:#192823, weight:5}";
			$uniq[] = "Proxy $ip -> $private_ip  {color:#D0C6B1, weight:2}";
  		} else {
  			$uniq[] = "$referer -> $ip  {color:#218559, weight:2}";	
			$uniq[] = "$thisNode -> $ip {color:#192823, weight:5}";
  		}
		
		$uniqnodes[] = "$referer {color:#218559} ";
	} else {
		if ($tein_proxy) {
			$uniq[] = "Proxy $ip -> $private_ip {color:red, weight:2}";
			$uniq[] = "$thisNode -> Proxy $ip {color:red, weight:5}";
		} else {
			$uniq[] = "$thisNode -> $ip {color:red, weight:5}";
		}
	}
	if ($tein_proxy) {
		$uniqnodes[] = "Proxy $ip {color:#D0C6B1}";
		$uniqnodes[] = "$private_ip {color:#EBB035}";
	} else {
		$uniqnodes[] = "$ip {color:#EBB035}";
	}

}
	$uniqnodes[] = "$thisNode {color:#192823}";
	echo implode("\n", array_unique($uniq)); 
	echo "\n";
	echo implode("\n", array_unique($uniqnodes)); 
