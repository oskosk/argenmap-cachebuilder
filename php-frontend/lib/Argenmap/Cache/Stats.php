<?php

namespace Argenmap\Cache;

class Stats
{
	protected $LOG_DIR;
	protected $CACHE_DIR;
	protected $OLDCACHES_DIR;

	protected $_access_log_path;
	protected $_logLines;
	protected $_logIndexed;

	function __construct($NLINES=FALSE)
	{
		if ($NLINES) {
			$this->NLINES = $NLINES;
		}
		$this->LOG_DIR = dirname(dirname(__FILE__)) . "/tms/logs";
		$this->CACHE_DIR = dirname(dirname(__FILE__)) . "/tms/cache";
		$this->OLDCACHES_DIR = dirname(dirname(__FILE__)) . "/tms/oldcaches";
		$this->_access_log_path = $this->LOG_DIR . '/log.txt';

		
	}

	function _indexedLogLines()
	{
		if (!$this->_logLines) {
			$this->_logLines = file($this->_access_log_path);	
			$this->_logIndexed = $this->_indexLog($this->_logLines);
		}

		return $this->_logIndexed;
	}

	function _indexLog(&$lines)
	{

		$ret = array(
			'porReferer' => array(),
			'porIP' => array(),
			'porDate' => array(),
			'porDateTime' => array()			
		);

		if (count($lines) == 0 ) {
			return $ret;
		}		
		foreach($lines as &$ll) {
			if ($ll == '') {
				continue;
			}
			$request = $this->_parseLine($ll);
			
			$date = $request['date'];
			$datetime = $request['datetime'];
			$referer = $request['referer'];
			$ip = $request['ip'];
			$private_ip = $request['private_ip'];

			$ret['porReferer'][$referer][] = &$ll;
			$ret['porIP'][$ip][] = &$ll;
			$ret['porDate'][$date][] = &$ll;
			$ret['porDateTime'][$datetime][] = &$ll;
		}	
		
		return $ret;
	}
	
	function urls()
	{
		$urls = explode("\n", $this->_urlsCrudos() );
		return $urls;
	}

	function _urlsCrudos()
	{
	 	return shell_exec("awk -F'\t' '{print $2}' $this->_access_log_path " 
		. " | awk -F'?' '{print $1}' |sort|uniq");
	}

	function _segundosTotalesPorDate($date)
	{

		if (! date_parse($date)) {
			return false;
		}
		$log = $this->_indexedLogLines();

		$requests = $this->_indexLog($log['porDate'][$date]);
		$segundos = array_keys($requests['porDateTime']);

		return count( $segundos );

	}



	public function referersPorDate($date)
	{

		if (! date_parse($date)) {
			return false;
		}
		$log = $this->_indexedLogLines();

		$requests = $this->_indexLog($log['porDate'][$date]);
		$referers = array_keys($requests['porReferer']);

		return $referers;
	}

	public function clientesPorDate($date)
	{
		if (! date_parse($date)) {
			return false;
		}
		$log = $this->_indexedLogLines();

		$requests = $this->_indexLog($log['porDate'][$date]);
		$clientes = array_keys($requests['porIP']);

		return $clientes;
	}
	

	public function tutti()
	{
		$f = file($this->_access_log_path);
		
		foreach($f as $line) {
			var_dump($this->_parseLine($line));
		}
	}

	public function cacheDiskUsage()
	{
		$usage = $this->_diskSpace($this->CACHE_DIR);
		return ($usage / 1024) . ' KB';
	}

	public function oldCachesDiskUsage()
	{
		$usage = $this->_diskSpace($this->OLDCACHES_DIR);
		return ($usage / 1024) . ' KB';
	}	

	function _diskSpace($dir) 
	{ 
		$s = stat($dir); 
		$space = $s["blocks"]*512; 
		if (is_dir($dir)) { 
		 	$dh = opendir($dir); 
			while (($file = readdir($dh)) !== false)  {
		  	if ($file != "." and $file != "..") {
		    	$space += $this->_diskSpace($dir."/".$file); 
		    }
			}
		 	closedir($dh); 
		} 
		return $space; 
	}	
}

