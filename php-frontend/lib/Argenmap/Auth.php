<?php

namespace Argenmap;

class Auth {
  static function isAdmin()
  {
    global $CONFIG, $app;
    $admin_key = \Argenmap\Config::admin_key();
    $client_key = @$_GET['key'];
    
    if ($admin_key == '' || ($admin_key != $client_key) ) {
      $app->halt(401);  
    }
  }
}