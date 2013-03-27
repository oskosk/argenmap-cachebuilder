<?php
class ArgenmapCacheStats
{
	protected $LOG_DIR; 
	protected $NLINES = 100;
	protected $access_log_path;
	protected $_logLines;
	protected $_logIndex;

	function __construct($NLINES=FALSE)
	{
		if ($NLINES) {
			$this->NLINES = $NLINES;
		}
		$this->LOG_DIR = dirname(dirname(__FILE__)) . "/tms/logs";
		$this->access_log_path = $this->LOG_DIR . '/log.txt';

		
	}

	function _readLog()
	{
		$this->_logLines = file($this->access_log_path);
		$this->_indexLog($this->_logLines);
	}

	function _indexLog(&$lines)
	{
		foreach($lines as &$ll) {
			if ($ll == '') {
				continue;
			}
			$request = explode("\t", $ll);
			$trash = explode(' - ', $request[0]);
			$datetime = $trash[0];
			$trash = explode(' ', $datetime);
			$date = $trash[0];
			$referer = $request[4];
			$ip = $request[5];
			//este campo suele venir vacío porque
			// generalmente no hay proxies involucrador en el request
			// De hecho, muchois proxies ocultan la ip privada			
			$private_ip = @$request[6];

			$ret['porReferer'][$referer][] = &$ll;
			$ret['porIP'][$ip][] = &$ll;
			$ret['porDate'][$date][] = &$ll;
			$ret['porDateTime'][$datetime][] = &$ll;

		}	
		$this->_logIndex = $ret;

	}
	
	function urls()
	{
		$urls = explode("\n", $this->_urlsCrudos() );
		return $urls;
	}

	function _urlsCrudos()
	{
	 	return shell_exec("awk -F'\t' '{print $2}' $this->access_log_path " 
		. " | awk -F'?' '{print $1}' |sort|uniq");
	}

	function _segundosTotalesPorDateTime($datetime)
	{
	 	$out = shell_exec("egrep '^$datetime' $this->LOG_DIR/log.txt "
		. " |  awk -F' - ' '{print $1}'  |uniq |wc -l");		
		
		return trim($out);

	}

	public function referersPorDateTime($datetime)
	{

		if (! date_parse($datetime)) {
			return false;
		}
		$cmd = "egrep '^$datetime' $this->LOG_DIR/log.txt | awk -F'\t' '{print $3}'  |sort |uniq" ;

		//$cmd = "egrep '^$datetime' $this->LOG_DIR/log.txt | awk -F' --> ' '{print $2}' "
		//. " |awk -F' - ' '{print $2}' | awk -F',' '{print $1}' | awk -F'?' '{print $1}' |sort|uniq";

		$lines =  shell_exec( $cmd ); 

		$lines = explode("\n",  $lines);
		$referers =  $lines;
		return $referers;
	}

	public function clientesPorDateTime($datetime)
	{

		if (! date_parse($datetime)) {
			return false;
		}
		$cmd = "egrep '^$datetime' $this->LOG_DIR/log.txt | awk -F'\t' '{print $4}' |sort|uniq";
		
		$lines =  shell_exec( $cmd ); 

		$lines = explode("\n",  $lines);
		$referers =  $lines;
		return $referers;
	}
	

	public function requestsPorDateTime($datetime)
	{

		if (! date_parse($datetime)) {
			return false;
		}
		$cmd = "egrep '^$datetime' $this->LOG_DIR/log.txt";

		$lines = shell_exec($cmd);

		$lines = explode("\n",  $lines);
		$requests = array();		
		$requests = array_map(array($this, '_parseLine'), $lines);
		return $requests;

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

	public function stockChartData()
	{
		$lines = file($this->access_log_path);
		
		$chart_data = array();
		$por_fecha = array();
		
		foreach ($lines as $ll) {
			$p = $this->_parseLine($ll);
			//$date = explode(' ', $p['date']);
			//$dia = $date[0]
			//die(var_dump(date_parse($p['date'])));
			$dia = date_parse($p['date']);
			$dia = sprintf("%s %s %s %s %s %s", $dia['year'], $dia['month'], $dia['day'], $dia['hour'], 0, 0);

			if ( ! isset($por_fecha[$dia]) ) {
				$por_fecha[$dia] = array( );
			}
			$por_fecha[$dia][] = $p;
		}
		foreach ($por_fecha as $dia=>$requests) {
			$clientes = array();
			foreach($requests as $r) {
				$ip = $r['client']['ip'];
				if ( ! isset( $clientes[$ip] ) ) {
					$clientes[$ip] = array();
				}
				$clientes[$ip][] = $r;
			}
			$aux = array();
			$aux['total_tiles'] = count($requests);
			$aux['date'] = $dia;
			$aux['total_clientes'] = count($clientes);
			$chart_data[] = $aux;
			
		}			
		return $chart_data;

	}

	public function chartData()
	{
		$requests = $this->ultimosRequests();

		$referers = array();
		foreach ($requests as $b) {
			$r = explode('?',$b['client']['referer']);
			$r = $r[0];
			if (! array_key_exists($r, $referers)) {
				$referers[$r] = 0;
			} else {
				$referers[$r] ++;
			}
		}
		$a = array();
		foreach($referers as $k=>$v) {
			$b = array(
				'referer' => $k,
				'visits' => $v
			);
			$a[] = $b;
		}
		return $a;
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
		// De hecho, muchois proxies ocultan la ip privada		
		$private_ip = @$request[6];

		$ret = array();
		$ret['date'] = $date;
		$ret['tile'] = array(
			'z'=>$request[1],
			'x'=>$request[2],
			'y'=>$request[3]
		);
		$ret['referer'] = $referer;
		$ret['ip'] = $ip;
		$ret['private_ip'] = $private_ip;

		return $ret;
	}

	public function tutti()
	{
		$f = file($this->access_log_path);
		
		foreach($f as $line) {
			var_dump($this->_parseLine($line));
		}
	}
}

