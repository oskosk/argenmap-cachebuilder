<?php

namespace Controllers;

class Cache {
  static function truncate()
  {
    global $app;
    try {
      $cache = new \Argenmap\Cache(); 
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
}
  