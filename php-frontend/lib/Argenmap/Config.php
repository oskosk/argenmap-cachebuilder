<?php

namespace Argenmap;

class Config {
  static function logs_path()
  {
    global $CONFIG;
    return $CONFIG['logs_path'];
  }

  static function cache_path()
  {
    global $CONFIG;
    return $CONFIG['cache_path'];
  }  

  static function admin_key()
  {
    global $CONFIG;
    return trim(@$CONFIG['admin_key']);
  }
}