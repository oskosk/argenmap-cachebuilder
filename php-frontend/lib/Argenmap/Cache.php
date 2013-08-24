<?php
/*
  For explanation and usage, see:
  http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
*/  

namespace Argenmap;


class Cache extends \JG_Cache
{
    private $log;
    private $error;

    
    function __construct()
    {
        $hoy = date("Y-m-d");
        $log_filename = "log-$hoy.txt";
        $error_filename = "error-$hoy.txt";
        $this->log = new \KLogger ( \Argenmap\Config::logs_path() . "/$log_filename" , \KLogger::DEBUG );
        $this->error = new \KLogger ( \Argenmap\Config::logs_path() . "/$error_filename" , \KLogger::DEBUG );
        $cache_dir = \Argenmap\Config::cache_path();

        parent::__construct($cache_dir); 
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
            $this->error->LogError("\tEl archivo del caché existe pero tiene 0 bytes");
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
            'size' => filesize($cache_path),
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

    function LogError($s)
    {
       $this->error->LogError($s); 
    }

    function LogInfo($s)
    {
        $this->log->LogInfo($s);     
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

        $this->log->LogInfo("\t$z\t$x\t$y\t$referer\t$ip\t$forwarded_for");        
    }

    function truncate()
    {
            $dir = $this->dir;
            $oldcaches_path = $dir.DIRECTORY_SEPARATOR.'../oldcaches';
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
}
