<?php


namespace Wyolution;


class TimedTest extends \Test
{
	private $time_start;
	
	function __construct()
	{
		parent::__construct();
		$this->time_start = microtime(true);
	}



	public function displayTestResults()
	{
		// Get time in seconds 
		$time_end = microtime(true);
		$elapsedTime = round($time_end - $this->time_start,2);
		
		// Display the results; not MVC but let's keep it simple
		$numTests = count($this->results());
		$numFails = 0;
		foreach ($this->results() as $result) {
			$text = $result['text'];
			if ($result['status']) {
				echo "<div style=\"color:green;\">Pass: $text</div>\n";
			} else {
				$source = $result['source'];
				echo "<h3 style=\"color:red;\">Fail: $text ($source)</h3>\n";
				$numFails++;
			}
		}

		echo "<h3> Tests took $elapsedTime seconds</h3>\n";
		echo "<h3> $numTests tests, $numFails failures</h3>\n";
	}
}