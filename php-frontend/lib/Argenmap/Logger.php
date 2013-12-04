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
      return array();
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
		$ip = $request[5];
		//este campo suele venir vacío porque
		// generalmente no hay proxies involucrador en el request
		// De hecho, muchos proxies ocultan la ip privada		
		$private_ip = @$request[6];

		$ret = array();
		$ret['date'] = $date;
		$ret['datetime'] = $datetime;
		$ret['tile'] = array(
			'z'=>$request[1],
			'x'=>$request[2],
			'y'=>$request[3]
		);
		$ret['referer'] = $referer;
		
		// Este chequeo es porque el proxy de AppFog, 
		// en el header(string) X_FORWARDED_FOR mete
		// la IP pública del cliente y un 127.0.0.1
		// separados por comas porque usa reverse
		// proxies para balancear los pedidos a cada app
		$private_stuff = explode(',', $private_ip);
		if ( count($private_stuff) == 2) {
			//caso de request normal sin proxy
			$ip = $private_stuff[0];
			$private_ip = false;
		} elseif( count($private_stuff) == 3) {
			//caso de request normal con proxy
			$ip = $private_stuff[1];
			$private_ip = $private_stuff[0];
		} elseif( count($private_stuff) == 4) {
			// caso de request normal con proxy pero
			// con dos 127.0.0.1 en el campo x_forwarded_for
			$ip = $private_stuff[2];
			$private_ip = $private_stuff[0];
		}
		$ret['ip'] = trim($ip);		
		$ret['private_ip'] = trim($private_ip);

		return $ret;
	}

}