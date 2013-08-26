<?php

namespace Controllers;

class Live {
  static function live_consola()
  {
    global $app;
    require "templates/live.html";
  }

  static function live($usarSesion=false)
  { 
    global $app;
    
    //$usarSesion = isset($_GET['polling']) && $_GET['polling'] == true;
    $interval = isset($_GET['interval']) && !$usarSesion ? int($_GET['interval']) : 1;
    header("Access-Control-Allow-Origin: *");
    if($usarSesion)
    {
      header('Content-Type: text/text');
    }else{
      header('Content-Type: text/event-stream');
    }
    header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

    $serverTime = time();
    $live = new \Argenmap\Live($usarSesion);

    if($usarSesion)
    {
      echo "data: " . json_encode($live->results) . " " . PHP_EOL;
      echo PHP_EOL;
    //  ob_flush();
      flush();
    }else{
      //ver este ejemplo de implementacion php de sse
      //https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
      //se puede hacer sin session con el while, creo
      while(1) {
        echo "event: ping\n";
        $live->procesar();
        echo 'data: ' . json_encode($live->results) . " \n";
        echo "\n\n";
        ob_flush();
        flush();
        sleep($interval);
      }
    }
  }

  static function live_poll()
  { 
    self::live(true);
  }
}