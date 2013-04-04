<?php
class ArgenmapCacheStats
{
	protected $LOG_DIR; 
	protected $NLINES = 100;
	protected $_access_log_path;
	protected $_logLines;
	protected $_logIndexed;

	function __construct($NLINES=FALSE)
	{
		if ($NLINES) {
			$this->NLINES = $NLINES;
		}
		$this->LOG_DIR = dirname(dirname(__FILE__)) . "/tms/logs";
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

	public function requestsPorDate($date)
	{
		$requests = $this->_indexedLogLines();
		if ( isset($requests['porDate'][$date]) ) {
			return $requests['porDate'][$date];	
		} else {
			return array();
		}

		
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
	


	

	public function ultimosRequests()
	{
		$cmd = "tail -n $this->NLINES $this->LOG_DIR/log.txt";
		$lines =  shell_exec( $cmd ); 

		$lines = explode("\n",  $lines);
		$uniq = array();		
		foreach ($lines as $ll) {
			if (trim($ll) == '') {
				continue;
			}
			$ll = $this->_parseLine($ll);
			$uniq[] = $ll;
		}		

		return $uniq;
	}


	protected function _parseLine($line) 
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
			//caso de request normal con proxy pero
			// con dos 127.0.0.1 en el campo x_forwarded_for
			$ip = $private_stuff[2];
			$private_ip = $private_stuff[0];
		}
		$ret['ip'] = trim($ip);		
		$ret['private_ip'] = trim($private_ip);

		return $ret;
	}

	public function tutti()
	{
		$f = file($this->_access_log_path);
		
		foreach($f as $line) {
			var_dump($this->_parseLine($line));
		}
	}
}

