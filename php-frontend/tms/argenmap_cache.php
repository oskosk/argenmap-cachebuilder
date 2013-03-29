<?php
/*
	For explanation and usage, see:
	
	http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
*/	
class Argenmap_Cache extends JG_Cache {

    public function getAndPassthru($key, $expiration = 3600)
    {
        global $error;

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
	    header("Content-Type: image/png");
	    header("Content-Length: " . $fsize );
	    $cache = fpassthru( $fp );
        }
        else
        {
            $error->LogError("\tEl archivo del caché existe pero tiene 0 bytes");
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

}
