<?php
class ArgenmapCacheStats
{
	protected $LOG_DIR; 
	protected $NLINES = 100;
	protected $access_log_path;

	function __construct($NLINES=FALSE)
	{
		if ($NLINES) {
			$this->NLINES = $NLINES;
		}
		$this->LOG_DIR = dirname(dirname(__FILE__)) . "/tms/logs";
		$this->access_log_path = $this->LOG_DIR . '/log.txt';
	}
	
	function urls()
	{
		$urls = explode("\n", $this->_urlsCrudos() );
		return $urls;
	}

	function _urlsCrudos()
	{
	 	return shell_exec("awk -F' --> ' '{print $2}' "
	 	. $this->access_log_path 
		. " |awk -F' - ' '{print $2}' | awk -F',' '{print $1}' | awk -F'?' '{print $1}' |sort|uniq");
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
		$ll = explode(' --> ', $line);
		$timeField = $ll[0];
		$requestField = explode(' - ', $ll[1]);
		$tileField = $requestField[0];
		$clientField = $requestField[1];;

		$request = array();
		$request['date'] = $this->_parseTimeField($timeField);
		$request['client'] = $this->_parseClientField($clientField);
		$request['tile'] = $this->_parseTileField($tileField);
		return $request;
	}

	protected function _parseTimeField($text)
	{
		$a = explode(' - ', $text);
		return $a[0];
	}

	protected function _parseClientField($text)
	{
		$a = explode(',', $text);
		return array(
			'referer' => $a[0],
			'ip' => $a[1],			
			'forwarded_for' => $a[2]

		);
		return $a;
	}

	protected function _parseTileField($text)
	{
		$ll = explode(' ', $text);
		$ll = array(
			'z'=>$ll[0],
			'x'=>$ll[1],
			'y'=>$ll[2]
		);		
		return $ll;
	}

	public function tutti()
	{
		$f = file($this->access_log_path);
		
		foreach($f as $line) {
			var_dump($this->_parseLine($line));
		}
	}
}

