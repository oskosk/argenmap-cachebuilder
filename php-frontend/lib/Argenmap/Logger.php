<?php

namespace Argenmap;

class Logger {

	private $error;
	private $log;
	protected $NLINES = 100;

	function __construct()
	{
    $log_filename = $this->logFilename();
    $error_filename = $this->errorlogFilename();
		$this->log = new \KLogger ( $log_filename, \KLogger::DEBUG );
    $this->error = new \KLogger ( $error_filename , \KLogger::DEBUG );
	}

	/** 
	 * Devuelve el nombre del archivo de log de requests
	 */
	function logFilename($date=false)
    {
      if (! $date ) {
        $date = date("Y-m-d");
      }
      $log_filename = "log-$date.txt";
      $log_filename = \Argenmap\Config::logs_path() . "/$log_filename" ;
      return $log_filename;
    }

	/** 
	 * Devuelve el nombre del archivo de log de errores
	 */
    function errorlogFilename($date=false)
    {
      if (! $date ) {
        $date = date("Y-m-d");
      }      
      $log_filename = "error-$date.txt";
      $log_filename = \Argenmap\Config::logs_path() . "/$log_filename";
      return $log_filename;
    }

    function logError($s)
    {
       $this->error->LogError($s); 
    }

    function logInfo($s)
    {
        $this->log->LogInfo($s);     
    }

	public function ultimosRequests()
	{
		$fname = $this->logFilename();
		$cmd = "tail -n $this->NLINES $fname";
		
		$lines =  shell_exec( $cmd ); 

		$lines = explode("\n",  $lines);
    return $this->jsonifyParsedLogLines($lines);
	}

  function jsonifyParsedLogLines($lines)
  {
    $uniq = array();    
    foreach ($lines as $ll) {
      if (trim($ll) == '') {
        continue;
      }
      $ll = $this->_parseLogfileLine($ll);
      $uniq[] = $ll;
    }   

    return $uniq;
  }

	function logsDisponibles()
	{
		$dirname = dirname($this->logFilename());
		$request_logs = glob("$dirname/log*.txt");
    //Filtro los size==0
    $request_logs = array_filter($request_logs, function($v) {
      if (0 != filesize($v) ) {
        return true;
      }
    });
    /*
     * http://php.net/manual/es/function.array-filter.php
     * Because array_filter() preserves keys, you should consider the
     * resulting array to be an associative array even if the original
     * array had integer keys for there may be holes in your sequence of keys. 
     * This means that, for example, json_encode() will convert your result 
     * array into an object instead of an array. 
     * Call array_values() on the result array to guarantee json_encode() gives you an array.
     */
    $request_logs = array_values($request_logs) ;
		$request_logs = array_map(function($v) {
			$fname = basename($v);
			$fname = explode('log-', $fname);
			$fname = explode('.', $fname[1]);  
			return array(
        'file_name' => basename($v),
        'size' => filesize($v) . " bytes",
        'date' => $fname[0]
      );
		}, $request_logs);

    $dirname = dirname($this->errorlogFilename());
    $error_logs = glob("$dirname/error*.txt");
    //Filtro los size==0
    $error_logs = array_filter($error_logs, function($v) {

      if (0 != filesize($v) ) {
        return true;
      }
    });    
    $error_logs = array_values($error_logs) ;
    $error_logs = array_map(function($v) {
      $fname = basename($v);
      $fname = explode('error-', $fname);
      $fname = explode('.', $fname[1]);

      return array(
        'file_name' => basename($v),
        'size' => filesize($v) . " bytes",
        'date' => $fname[0]
      );
    }, $error_logs);

		return array(
      'requests' => $request_logs,
      'errors' => $error_logs
    );


	}

  function requestsPorDate($date)
  {
    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
      return array();
    }
    $fname = $this->logFilename($date);
    if (! file_exists($fname) ) {
      return array();
    }
    $lines = file_get_contents($fname);
    $lines = explode("\n",  $lines);
    return $this->jsonifyParsedLogLines($lines);

    
  } 

  function requestsPorDateTxt($date)
  {
    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
      return array();
    }
    $fname = $this->logFilename($date);
    if (! file_exists($fname) ) {
      return false;
    }
    $txt = file_get_contents($fname);
    return $txt;

    
  } 

  function errorsPorDate($date)
  {
    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
      return array();
    }
    $fname = $this->errorlogFilename($date);
    if (! file_exists($fname) ) {
      return array();
    }
    $lines = file_get_contents($fname);
    $lines = explode("\n",  $lines);
    return $this->jsonifyParsedLogLines($lines);

    
  }     

  function errorsPorDateTxt($date)
  {
    if(! preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $date)){
      return array();
    }
    $fname = $this->errorlogFilename($date);
    if (! file_exists($fname) ) {
      return false;
    }
    $txt = file_get_contents($fname);
    return $txt;
   
  }    

	function _parseLogfileLine(&$line) 
	{
		if (trim($line) == '') {
			return false;
		}
		
		$request = explode("\t", $line);
		$trash = explode(' - ', $request[0]);
		$datetime = $trash[0];
		$trash = explode(' ', $datetime);
		$date = $trash[0];
		$referer = $request[4];

		$ret = array();
		$ret['date'] = $date;
		$ret['datetime'] = $datetime;
		$ret['tile'] = array(
			'z'=>$request[1],
			'x'=>$request[2],
			'y'=>$request[3]
		);
		$ret['referer'] = $referer;
		

		return $ret;
	}

}
