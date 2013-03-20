<?php
// Permanent redirection
header("HTTP/1.1 301 Moved Permanently");
header("Location: http://mapaabierto.com.ar/igncache/?" . $_SERVER['QUERY_STRING']);
exit();
?>
