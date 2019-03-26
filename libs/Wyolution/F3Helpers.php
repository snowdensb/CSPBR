<?php

namespace Wyolution;

/** 
 * Helper functions to augment default F3 behavior and/or
 * give devs behavior they are used to from other frameworks.
 */

// AG SOMEDAY: This would be a great first class to test with php unit
class F3Helpers
{
	/**
	 * getParam -- a wrapper for a series of f3->get calls so the
	 * calling code does not need to worry about:
	 *  - whether the param is in PARAMS or GET or POST groups
	 *  - param name upper/lower case
	 *  - cleansing the gotten value to prevent a cross-site scripting attack
	 * 
	 * "Be lenient in what you accept . . ."
	 * 
	 * @param $name -- parameter name 
	 * @param string $defaultValue, optional (empty string if not specified)
	 * @return mixed|string
	 */
	public static function getParam($name, $defaultValue = '') {
		$value = self::getRawF3Param($name, $defaultValue);

		// prevent cross-site scripting attacks
		if (is_array($value)) {
			$cleanArray = array();
			foreach ($value as $key => $item) {
				$cleanArray[$key] = self::sanitize($item);
			}
			$value = $cleanArray;
			// By definition, if what we got from the browser is an
			// array, then it is not missing and we do not need to 
			// apply a default value.
		} else {
			$value = self::sanitize($value);
			if ($value === '' && $defaultValue !== '') {
				$value = $defaultValue;
			}
		}

		return $value;
	}

	/**
	 * getRawF3Param -- leniently get a param from f3, but don't cleanse it
	 * 
	 * @param $name
	 * @param string $defaultValue
	 * @return mixed|string
	 */
	private static $paramSourceList = array('PARAMS.', 'GET.', 'POST.');
	public static function getRawF3Param($name, $defaultValue = '') {
		$value = '';

		$f3 = \Base::instance();

		foreach (self::$paramSourceList as $paramSource) {
			$value = $f3->get($paramSource . $name);
			if (isset($value)) {
				break;
			}
			$value = $f3->get($paramSource . strtolower($name));
			if (isset($value)) {
				break;
			}
		}

		return $value;
	}

	/**
	 * getRawPhpParam -- retrieve a GET or POST parameter straight from
	 * the php horse's mouth, no leniency, no cleansing
	 * 
	 * @param $name
	 * @param string $defaultValue
	 * @return mixed|string
	 */
	public static function getRawPhpParam($name, $defaultValue = '') {
		$value = '';

		if (isset($_GET[$name])) {
			$value = $_GET[$name];
		}
		elseif (isset($_POST[$name])) {
			$value = $_POST[$name];
		}
		else {
			$value = "";
		}
		
		if ($value == '' && $defaultValue != '') {
			$value = $defaultValue;
		}

		return $value;
	}

	/** executionTime -- This method is run as F3 unloads our code if it has been set as
	 * our unload handler in init.php with a line like:
	 * $f3->set('UNLOAD','\\Wyolution\\F3Helpers::executionTime');
	 * Normally it calculate execution time and memory and display them at the bottom of the page source.
	 * But, if we are in debug mode, it checks for a php error and displays it first.
	 * F3 catches many errors but not, for example, calls via autoloader to non-existent methods
	 */
	public static function executionTime() {
		$f3 = \Base::instance();
		// Never add anything to the output stream if we are returning json to the client
		$jsonSent = $f3->get('JSONSENT') == '1';
		if (!$jsonSent) {
			$doShowDetails = false;
			if (\Wyolution\Logger::getLogLevel() == \Wyolution\Logger::DEBUG) {
				$doShowDetails = true;
			}
			if ($doShowDetails) {
				$error=error_get_last();
				if (isset($error)) {
					echo("\n" . '<!-- ' . "\n");
					echo("\n" . 'error type: ' . $error['type'] . "\n");
					echo("\n" . 'error message: ' . $error['message'] . "\n");
					echo("\n" . 'error file: ' . $error['file'] . "\n");
					echo("\n" . 'error line: ' . $error['line'] . "\n");
					echo("\n" . '--> ' . "\n");
				}
				else {
					echo("\n" . '<!-- $error == null -->' . "\n");
				}
			}
			if ($doShowDetails || $f3->get('SUPPRESS_EXECUTION_DETAILS') != '1') {
				$executionTime = round(microtime(true) - $f3->TIME, 4);
			$timeStamp = date("m-d-y") . " " . date("G:i:s") . " "; 
			echo( "\n".'<!-- '.$timeStamp.' Request executed in '.$executionTime.' seconds using '.
					round(memory_get_usage() / 1024 / 1024, 2) . '/' .
					round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB memory/peak -->');
			}
		}
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	private static function sanitize($value)
	{
		$value = str_replace('<', '', $value);
		$value = str_replace('>', '', $value);
		$value = str_replace('"', '', $value);
		return $value;
	}
}