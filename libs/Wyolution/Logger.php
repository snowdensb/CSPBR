<?PHP

namespace Wyolution;

/**
 * Logging and debugging functions
 *
 * @package Wyolution
 * @author Mark Thoney <mark@wyolution.com>
 *
 */
class Logger {

	/**
	 * Log levels
	 */
	const DEBUG = 5;
	const INFO = 4;
	const NOTICE = 3;
	const WARNING = 2;
	const ERROR = 1;
	const NONE = 0;

	/**
	 * textual representation of severity levels
	 * @var array
	 */
	private static $severityText = array(
		Logger::DEBUG   => "Debug",
		Logger::INFO    => "Info",
		Logger::NOTICE  => "Notice",
		Logger::WARNING => "Warning",
		Logger::ERROR   => "Error",
		Logger::NONE    => "None"
	);

	public static function debug($message) {
		Logger::log($message, Logger::DEBUG);
	}
	public static function info($message) {
		Logger::log($message, Logger::INFO);
	}
	public static function notice($message) {
		Logger::log($message, Logger::NOTICE);
	}
	public static function warning($message) {
		Logger::log($message, Logger::WARNING);
	}
	public static function error($message) {
		Logger::log($message, Logger::ERROR);
	}

	/**
	 * write log statement
	 *
	 * @return void
	 * @param string $message
	 * @param int $severity
	 */
	private static function log($message, $severity) {

		$f3 = \Base::instance();
		$logLevel = self::getLogLevel();
		$logType = intval($f3->get('wyolutionLoggerType'));

		if (($severity > $logLevel) || ($logType == 0)) {
			return;
		}

		$msg = date("m-d-y") . " " .
			date("G:i:s") . " " .
			Logger::$severityText[$severity] . " " .
			$_SERVER['REMOTE_ADDR'] . " " .
			$message;

		if ($logType & 1) {
			echo "<!-- $msg -->\n";
		}

		if ($logType & 2) {

			$logFoldername = $f3->get('BASEDIR').'/data/logs';
			if (!file_exists($logFoldername)) {
				mkdir($logFoldername, $f3::MODE, true);
			}
			$logFilename = $logFoldername . '/' . $f3->get('logFileRoot') . '.log';
			$fileHandle = fopen($logFilename, "at+");
			// Can't open the log file? Heck, not much to do about that. Be gone.
			if($fileHandle === false) {
				return;
			}

			fwrite($fileHandle, $msg . "\n");
			fclose($fileHandle);
		}
	}
	
	/**
	 * write email to file
	 *
	 * @return void
	 * @param string $message
	 * @param int $severity
	 */
	public static function email(string $to, string $subject, string $message) {
	    
	    $f3 = \Base::instance();
	    
	    $msg = date("m-d-y") . " " . date("G:i:s") . "\n---\nTo: " . $to . "\nSubject: " . $subject . "\n\n" . $message . "\n---\n";
	       	        
        $logFoldername = $f3->get('BASEDIR').'/data/logs';
        if (!file_exists($logFoldername)) {
            mkdir($logFoldername, $f3::MODE, true);
        }
        $logFilename = $logFoldername . '/email.log';
        $fileHandle = fopen($logFilename, "at+");
        // Can't open the log file? Heck, not much to do about that. Be gone.
        if($fileHandle === false) {
            return;
        }
        
        fwrite($fileHandle, $msg . "\n");
        fclose($fileHandle);
	}

	/**
	 * @param $f3
	 * @return int
	 */
	public static function getLogLevel() {
		$logLevel = 0;

		$f3 = \Base::instance();
		if (\Wyolution\F3Helpers::getParam('proddebug') == '1') {
			// If we get this parameter on any request, we need to force
			// debug logging on
			$logLevel = Logger::DEBUG;
			if ($f3->get('wyolutionLoggerType') == 0) {
				// Logging is turned off completely. Turn on logging to browser.
				$f3->set('wyolutionLoggerType', 1);
			}
		} elseif ($f3->exists('wyolutionLoggerLevel')) {
			$logLevel = intval($f3->get('wyolutionLoggerLevel'));
		} // else, logging will be turned off

		return $logLevel;
	}
}
