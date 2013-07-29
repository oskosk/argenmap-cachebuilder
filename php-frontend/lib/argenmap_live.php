<?php

//$usarSesion = isset($_GET['polling']) && $_GET['polling'] == true;
$interval = isset($_GET['interval']) && !$usarSesion ? int($_GET['interval']) : 1;
header("Access-Control-Allow-Origin: *");
if($usarSesion)
{
	header('Content-Type: text/text');
}else{
	header('Content-Type: text/event-stream');
}
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.

$serverTime = time();
$live = new ArgenmapLive("data/logs/log.txt",$usarSesion);

if($usarSesion)
{
	echo "data: " . json_encode($live->results) . " " . PHP_EOL;
	echo PHP_EOL;
//	ob_flush();
	flush();
}else{
	//ver este ejemplo de implementacion php de sse
	//https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
	//se puede hacer sin session con el while, creo
	while(1) {
		echo "event: ping\n";
		$live->procesar();
		echo 'data: ' . json_encode($live->results) . " \n";
		echo "\n\n";
		ob_flush();
		flush();
		sleep($interval);
	}
}
class ArgenmapLive
{
	private $LOG_LINE_COUNT;
	private $LOG_FILE_PATH;
	private $FROM_LINE = 0;
	private $START_MICRO_TIME;
	private $error = false;
	private $errorTxt = "OK";
	private $USE_SESSION = true;
	private $numDiffLines;
	private $rawDiffLines;
	private $indexedDiffLines;
	public $results;

	function __construct($filePath, $useSession = true)
	{
		$this->USE_SESSION = $useSession;
		$this->START_MICRO_TIME = microtime(true);
		$this->resetVars();
		if(@$_GET['sid']) session_id($_GET['sid']);
		session_start();
		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 180))
		{
			// last request was more than 30 minutes ago
			session_unset();     // unset $_SESSION variable for the run-time 
			session_destroy();   // destroy session data in storage
		}
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
		$this->LOG_FILE_PATH = realpath($filePath);
		if(!$this->LOG_FILE_PATH) 
		{
			$this->setError("No se encuentra el log. ({$filePath})");
			return;
		}
		$wc = shell_exec("wc -l $this->LOG_FILE_PATH");
		$r = explode(" ", $wc);
		$this->LOG_LINE_COUNT = (int) $r[0];
		if(!isset($_SESSION["argenmap_stats"]))
		{
			$_SESSION["argenmap_stats"] = array();
			$_SESSION["argenmap_stats"]["leerDesde"] = $this->LOG_LINE_COUNT;
		}
		$this->FROM_LINE = $_SESSION["argenmap_stats"]["leerDesde"];
		$this->countDiffLines();
		$this->getRawDiffLines();
		$this->indexLogLines();
		$this->consolidarResultados();
	}
	function __destruct()
	{
		$_SESSION["argenmap_stats"]["leerDesde"] = $this->LOG_LINE_COUNT;
	}
	private function resetVars()
	{
		$this->numDiffLines = 0;
		$this->rawDiffLines = array();
		$this->indexedDiffLines = array();
		$this->results = array(
			'requestsTotales' => 0,
			'requestsNuevos' => 0,
			'requestsCrudos' => array(),
			'requestsIndexados' => array(),
			'processData' => array(
				'status' => 'OK',//o success, no se
				'error' => false,
				'serverTime' => time(),
				'processMicrotime' => 0
			)
		);
	}
	private function setError($txt)
	{
		$this->error = true;
		$this->errorTxt = $txt;
		$this->consolidarResultados();
	}
	public function procesar()
	{
		$this->START_MICRO_TIME = microtime(true);
		$this->resetVars();
		
		$wc = shell_exec("wc -l $this->LOG_FILE_PATH");
		$r = explode(" ", $wc);
		$this->LOG_LINE_COUNT = (int) $r[0];
		$this->countDiffLines();
		$this->getRawDiffLines();
		$this->indexLogLines();
		$this->consolidarResultados();
	}
	private function consolidarResultados()
	{
		$this->results['requestsTotales'] = $this->LOG_LINE_COUNT;
		$this->results['requestsNuevos'] = $this->numDiffLines;
		$this->results['requestsCrudos'] = $this->rawDiffLines;
		$this->results['requestsIndexados'] = $this->indexedDiffLines;
		$this->results['processData']['processMicrotime'] = microtime(true) - $this->START_MICRO_TIME;
		$this->results['processData']['error'] = $this->error;
		$this->results['processData']['status'] = $this->errorTxt;
		$this->results['processData']['sessionId'] = session_id();
		$this->FROM_LINE = $this->LOG_LINE_COUNT;
	}
	private function countDiffLines()
	{
		$this->numDiffLines = $this->LOG_LINE_COUNT - $this->FROM_LINE;
	}
	public function getRawDiffLines()
	{
		if($this->numDiffLines < 1)
		{
			return false;
		}
		$cmd = "tail -n {$this->numDiffLines} $this->LOG_FILE_PATH";
		$tail = shell_exec($cmd);
		$this->rawDiffLines = explode("\n", $tail);
		for($ii = 0; $ii < count($this->rawDiffLines); $ii++)
		{
			if(!$this->rawDiffLines[$ii]) unset($this->rawDiffLines[$ii]);
		}
	}
	private function indexLogLines()
	{
		$lines = &$this->rawDiffLines;
		$this->indexedDiffLines = array(
			'porReferer' => array(),
			'porIP' => array(),
			'porDate' => array(),
			'porDateTime' => array(),
			'porTile' => array()
		);

		if (count($lines) == 0 ) {
			return;
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
			$tile = $request['tile'];

			$this->indexedDiffLines['porReferer'][$referer][] = &$ll;
			$this->indexedDiffLines['porIP'][$ip][] = &$ll;
			$this->indexedDiffLines['porDate'][$date][] = &$ll;
			$this->indexedDiffLines['porDateTime'][$datetime][] = &$ll;
			$this->indexedDiffLines['porTile'][$tile][] = &$ll;
		}
	}
	protected function _parseLine(&$line) 
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
		$ret['tile'] = $request[1].'-'.$request[2].'-'.$request[3];
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
