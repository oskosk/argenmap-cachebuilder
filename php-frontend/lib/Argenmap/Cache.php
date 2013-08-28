<?php
/*
  For explanation and usage, see:
  http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
*/  

namespace Argenmap;


class Cache extends \JG_Cache
{
    private $error;

    
    function __construct()
    {
        $this->logger = new \Argenmap\Logger();        
        $cache_dir = $this->cache_directory();

        parent::__construct($cache_dir); 
    }

    function cache_directory()
    {
      return \Argenmap\Config::cache_path();
    }

    function oldcachesDirname()
    {
      $dir = $this->dir;
      return $dir.DIRECTORY_SEPARATOR.'../oldcaches';
    }

    public function getAndPassthru($key, $expiration = 3600)
    {

        if ( !is_dir($this->dir) OR !is_writable($this->dir))
        {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if (!@file_exists($cache_path))
        {
            return FALSE;
        }

        if (filemtime($cache_path) < (time() - $expiration))
        {
            $this->clear($key);
            return FALSE;
        }

        if (!$fp = @fopen($cache_path, 'rb'))
        {
            return FALSE;
        }

        flock($fp, LOCK_SH);

        $cache = '';

      $fsize = filesize($cache_path);
        if ( $fsize > 0)
        {
      //header("Content-Type: image/png");
      //header("Content-Length: " . $fsize );
      $cache = fpassthru( $fp );
        }
        else
        {
            $this->logger->LogError("\tEl archivo del caché existe pero tiene 0 bytes");
            $cache = NULL;
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        return $cache;
    }

    public function setRaw($key, $data)
    {

        if ( !is_dir($this->dir) OR !is_writable($this->dir))
        {
            return FALSE;
        }

        $cache_path = $this->_name($key);

        if ( ! $fp = fopen($cache_path, 'wb'))
        {
            return FALSE;
        }

        if (flock($fp, LOCK_EX))
        {
            fwrite($fp, $data);
            flock($fp, LOCK_UN);
        }
        else
        {
            return FALSE;
        }
        fclose($fp);
        @chmod($cache_path, 0777);
        return TRUE;
    }

    function traerTile($url)
    {   
        
        $f = $this->getAndPassthru($url, CACHE_TTL);
        
        if ($f === false || strlen($f) == 0) {
            $handler = curl_init($url);
            curl_setopt($handler, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handler, CURLOPT_FAILONERROR, true);
            $f = curl_exec($handler);
            if ( $f === false) {
                return false;
            }           
            curl_close($handler);
            $this->setRaw($url, $f);
            $f = $this->getAndPassthru($url, CACHE_TTL);
        }

        return $f;
    }   

    function calcularETag($url)
    {   
        $cache_path = $this->_name($url);

        if (!@file_exists($cache_path)) {
            return FALSE;
        }

        $mtime = filemtime($cache_path);
        $ETag = md5("$url-$mtime");
        return $ETag;
    }        


    function logTileServida($z, $x, $y)
    {
        $ip='';
        $referer='';
        $forwarded_for = '';
        if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ){
            $forwarded_for  = $_SERVER["HTTP_X_FORWARDED_FOR"];
            $ip = $_SERVER["REMOTE_ADDR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        if ( isset($_SERVER["HTTP_REFERER"]) ){
            $referer = $_SERVER["HTTP_REFERER"];
        }

        $this->logger->LogInfo("\t$z\t$x\t$y\t$referer\t$ip\t$forwarded_for");        
    }

    function truncate()
    {
      $dir = $this->dir;
      $oldcaches_path = $this->oldcachesDirname();

      if(!is_dir($oldcaches_path)) {
          throw new Exception('truncate: No existe oldcaches');
      }

      if(count(glob($dir.DIRECTORY_SEPARATOR."*")) === 0) {
          return;
      } 
      $newDir = time();
      if (FALSE === system("mkdir $oldcaches_path/".$newDir) ) {
          throw new Exception('truncate: no se puede crear el caché viejo');
      }
      if (FALSE === system("mv $dir/* $oldcaches_path/".$newDir) ) {
          throw new Exception('truncate: no se puede mover el caché');
      }            

    }    

    function status()
    {
      
      $ret = array(
        'cache_size' => $this->cacheSize() . " KB",
        'tiles_count' => count(glob($this->cache_directory().DIRECTORY_SEPARATOR."*")),
        'old_caches' => $this->oldcachesStatus()
      );


      return $ret;
    }

    function tileStatus($capa,$z,$x,$y,$format)
    {

      // un año en segundos
      define('CACHE_TTL',  31536000);


      $baseURL = 'http://mapa.ign.gob.ar/geoserver/gwc/service/tms/1.0.0';

      $capa = $capa . '@EPSG:3857@png8';
      //apendeo el 8 para pedir png8
      $tileURL = sprintf("%s/%s/%s/%s/%s.%s", $baseURL, $capa, $z, $x, $y,$format."8");

      $bytes_sent = NULL;
      $cache_path = $this->_name($tileURL);

      if (file_exists($cache_path)) {
        return array(
          'origin' => $tileURL,
          'updated' =>  date ("F d Y H:i:s.T", filemtime($cache_path)),
          'size' => filesize($cache_path) . " bytes",
          'ETag' => $this->calcularETag($tileURL)
        );
      } else {
        return array(
          'origin' => $tileURL,
          'updated' => FALSE,
          'size' => 0,
          'ETag' => FALSE
        );          
      }

    }    

    /*
     Devuelve el tamaño del caché actual en KB
     */
    function cacheSize()
    {
      $f = $this->cache_directory();
      return $this->_dirSize($f);
    }
    /*
     Devuelve el tamaño de un dir en KB
     */
    function _dirSize($dir)
    {
      $cmd = '/usr/bin/du -sk ' . $dir;
      $io = popen ( $cmd , 'r' );
      $size = fgets ( $io, 4096);
      $size = explode("\t", $size);
      $size = $size[0];
      pclose ( $io );
      return $size;      
    }

    function oldcachesStatus()
    {
      $ret = array();
      $oldcaches_path = realpath($this->oldcachesDirname());
      if(!is_dir($oldcaches_path)) {
          return array();
      }

      if(count(glob($oldcaches_path.DIRECTORY_SEPARATOR."*")) === 0) {
          return array();
      } else {
        $oldcaches = glob($oldcaches_path.DIRECTORY_SEPARATOR."*");
        foreach($oldcaches as $oc) {
          if ((int) basename($oc)) {
            $date_truncated = date('Y-m-d H:i:s', basename($oc));
            $ret[] = array(
              'directory_name' => basename($oc),
              'tiles_count' => count(glob($oc.DIRECTORY_SEPARATOR."*")),
              'cache_size' => $this->_dirSize($oc)  . " KB",
              'date_truncated' => $date_truncated
            );
          }
        }
        
      }
      return $ret;

    }

}
