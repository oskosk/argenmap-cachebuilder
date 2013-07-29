<?php
/*
	For explanation and usage, see:
	
	http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
*/	
class Argenmap_Cache extends JG_Cache
{
    private $log;
    private $error;

    function __construct($dir)
    {
        $this->log = new KLogger ( dirname(__FILE__)."/../data/logs/log.txt" , KLogger::DEBUG );
        $this->error = new KLogger ( dirname(__FILE__)."/../data/logs/error.txt" , KLogger::DEBUG );
        parent::__construct($dir); 
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
