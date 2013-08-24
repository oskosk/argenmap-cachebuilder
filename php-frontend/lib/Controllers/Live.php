<?php

namespace Controllers;

class Live {
  static function live_consola()
  {
    global $app;
    require "controllers/live.php";
  }

  static function live()
  { 
    global $app;
    $usarSesion=false;
    require "lib/argenmap_live.php";  
  }

  static function live_poll()
  { 
    global $app;
    $usarSesion=true;
    require "lib/argenmap_live.php";
  }
}