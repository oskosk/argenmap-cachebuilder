<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
/**
 * Constructs the SSE data format and flushes that data to the client.
 *
 * @param string $id Timestamp/id of this connection.
 * @param string $msg Line of text that should be transmitted.
 */
function _sendMsg($id, $msg) {
        echo "id: $id" . PHP_EOL;
        //echo "data: {" . PHP_EOL;
        echo "data: $msg" . PHP_EOL;
        //echo "data: }" . PHP_EOL;
        echo PHP_EOL;
        ob_flush();
        flush();
}

$serverTime = time();
$live = new ArgenmapLive("tms/logs/log.txt");
$live->getNewLines();
_sendMsg($serverTime, count($live->LOG_DIFF_LINES));
//echo "<pre>";
//print_r($live);
//echo "</pre>";
class ArgenmapLive
{
        private $SID;
        private $LOG_FILE;
        private $FROM_LINE = 0;
        public $LOG_LINE_COUNT;
        public $LOG_DIFF_LINES;

        function __construct($filePath)
        {
                $this->LOG_FILE = realpath($filePath);
                $this->countLogLines();
                $this->initializeSession();
        }
        private function initializeSession()
        {
                session_start();
                $this->SID = session_id();
                if(!isset($_SESSION["argenmap_stats"]))
                {
                        $_SESSION["argenmap_stats"] = array();
                        $_SESSION["argenmap_stats"]["from_line"] = $this->LOG_LINE_COUNT;
                }
                $this->FROM_LINE = $_SESSION["argenmap_stats"]["from_line"];
        }
        function __destruct()
        {
                $_SESSION["argenmap_stats"]["from_line"] = $this->LOG_LINE_COUNT;
        }
        private function countDiffLines()
        {
                return $this->LOG_LINE_COUNT - $this->FROM_LINE;
        }
        private function countLogLines()
        {
                $wc = shell_exec("wc -l $this->LOG_FILE");
                $r = explode(" ", $wc);
                $this->LOG_LINE_COUNT = $r[0];
        }
        public function getNewLines()
        {
                if($this->countDiffLines() == 0)
                {
                        $this->LOG_DIFF_LINES = array();
                        return $this->LOG_DIFF_LINES;
                }
                $cmd = "tail -n {$this->countDiffLines()} $this->LOG_FILE";
                $tail = shell_exec($cmd);
                $this->LOG_DIFF_LINES = explode("\n", $tail);
                return $this->LOG_DIFF_LINES;
        }
}
