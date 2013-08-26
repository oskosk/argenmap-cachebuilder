<?php

namespace Argenmap;

class Live
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
	private $logger;

	function __construct($useSession = true)
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
		$this->logger = new \Argenmap\Logger();
		$this->LOG_FILE_PATH = realpath($this->logger->logFilename());
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
			$request = $this->logger->_parseLogfileLine($ll);
			
			$date = $request['date'];
			$datetime = $request['datetime'];
			$referer = $request['referer'];
			$ip = $request['ip'];
			$private_ip = $request['private_ip'];
			$tile = $request['tile'];
			$tile = $tile['z'].'-'.$tile['x'].'-'.$tile['y'];
			$this->indexedDiffLines['porReferer'][$referer][] = &$ll;
			$this->indexedDiffLines['porIP'][$ip][] = &$ll;
			$this->indexedDiffLines['porDate'][$date][] = &$ll;
			$this->indexedDiffLines['porDateTime'][$datetime][] = &$ll;
			$this->indexedDiffLines['porTile'][$tile][] = &$ll;
		}
	}


}
