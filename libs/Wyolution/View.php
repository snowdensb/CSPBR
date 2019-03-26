<?PHP

namespace Wyolution;

/**
 * Helper class for rendering template
 *
 * @package    Wyolution
 * @author     Mark Thoney <mark@wyolution.com>
 * 
 */
class View extends \Prefab {

	private $smarty;
	private $messages = ['success' => [], 'info' => [], 'warning' => [], 'error' => []];
	
	public function getResultStatus() {
		$status = 'success';
		$errorCount = count($this->messages['error']);
		if ($errorCount > 0) {
			$status = "failed with $errorCount error messages";
		}
		return $status;
	}

	public function messageSuccess($msg) {
		$this->messages['success'][] = $msg;
		$this->updateSessionMessages();
	}
	public function messageInfo($msg) {
		$this->messages['info'][] = $msg;
		$this->updateSessionMessages();
	}
	public function messageWarning($msg) {
		$this->messages['warning'][] = $msg;
		$this->updateSessionMessages();
		\Wyolution\Logger::warning($msg);
	}
	public function messageError($msg) {
		$this->messages['error'][] = $msg;
		$this->updateSessionMessages();
		\Wyolution\Logger::error($msg);
	}
	public function haveErrors() {
		if (count($this->messages['error']) > 0) {
			return true;
		} else {
			return false;
		}
	}

	function clearMessages() {
		$this->messages = ['success' => [], 'info' => [], 'warning' => [], 'error' => []];
	}

	private function updateSessionMessages() {
		$f3 = \Base::instance();
		$f3->set('SESSION.appMessages',$this->messages);
	}
	
	public function renderPartial($view, $returnResults=true) {
		if ($returnResults)
			return $this->render($view,'',false,$returnResults);
		else 
			$this->render($view,'',false,$returnResults);
	}
	
	public function render($view, $layout='', $fullLayout = true, $returnResults = false) {
		$f3 = \Base::instance();
		$this->smarty = $f3->get('smarty');
		if ($layout == '') $layout = 'layout.html';
		
		// Automagically take any hive variables whose names start with
		// an underscore and assign them to be smarty variables.
		foreach ($f3->hive() as $key => $value) {
			if (!preg_match('/^_/', $key)) continue;
			$key = str_replace('_','',$key);
			$this->smarty->assign($key,$value);
		}
		
		// If the product has specified a viewhelper class (in init),
		// register it now.
		$viewHelper = $f3->get('viewHelper');
		if (isset($viewHelper)) {
			$this->smarty->registerObject('viewHelper', $viewHelper, null, false);
		}
		
		$this->smarty->assign('env', $f3->get('env'));
		$this->smarty->assign('appPath',$this->getRelativeUrl());
		$this->smarty->assign('appPathFull',$this->getBaseUrl());
		$this->smarty->assign('route',$f3->get('PATH'));
		$this->smarty->assign('appMessages',$f3->get('SESSION.appMessages'));
		$appPermissions = $f3->get('appPermissions');
		if (isset($appPermissions)) {
		    $this->smarty->assign('appPermissions',$appPermissions);
		}
		$appConstants = $f3->get('appConstants');
		if (isset($appConstants)) {
		    $this->smarty->assign('appConstants',$appConstants);
		}
		$f3->clear('SESSION.appMessages');
		
		if ($f3->get('smartyDebug')==1)
			\Smarty_Internal_Debug::display_debug($this->smarty);
	
		if ($fullLayout) {
			$this->smarty->assign('smartyIncludeFile',$view);
			$output = $this->smarty->fetch($layout);
		}
		else {
			$output = $this->smarty->fetch($view);
		}

		//\Wyolution\Logger::debug('render about to echo/return results');
		if (!$returnResults)
			echo $output;
		else
			return $output;
	}
	
	public function getRelativeUrl() {
		return $this->getBaseUrl(false);
	}
	
	/**
     * Returns the base url of the page. If a base url was configured in the 
     * config.ini this will be used. Otherwise base url will be generated from
     * global server variables ($_SERVER).
     */
    public function getBaseUrl($fullPath = true) {
        $base = '';
        
        // base url in config.ini file
        if(strlen(trim(\F3::get('base_url')))>0) {
            $base = \F3::get('base_url');
            $length = strlen($base);
            if($length>0 && substr($base, $length-1, 1)!="/")
                $base .= '/';
                
        // auto generate base url
        } else {
            $lastSlash = strrpos($_SERVER['SCRIPT_NAME'], '/');
            $subdir = $lastSlash!==false ? substr($_SERVER['SCRIPT_NAME'], 0, $lastSlash) : '';
            
            $protocol = 'http';
            if (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"]=="on" || $_SERVER["HTTPS"]==1)) {
                $protocol = 'https';
            }
            
            $port = $_SERVER["SERVER_PORT"];
            if (\F3::exists('port')) {
            	$port = \F3::get('port');
            }
            if (($protocol == 'http' && $port!="80") ||
                ($protocol == 'https' && $port!="443")) {
                $port = ':' . $port;
            }
            else {
                $port = '';
            }
            
            if ($fullPath)
            	$base = $protocol . '://' . $_SERVER["SERVER_NAME"] . $port . $subdir . '/';
            else 
            	$base = $subdir;
        }
        
        return $base;
    }
    
    
    /**
     * render template
     *
     * @return string rendered html
     * @param string $template file
     */
    /*
    public function render($template) {
        ob_start();
        include $template;
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }
    */
    
    
    /**
     * send error message
     *
     * @return void
     * @param string $message
     */
    public function error($message) {
        header("HTTP/1.0 400 Bad Request");
        die($message);
    }
    
    
    /**
     * send error message as json string
     *
     * @return void
     * @param mixed $datan
     */
    public function jsonError($data) {
        header('Content-type: application/json');
		\F3::set('JSONSENT', '1');
        $this->error( json_encode($data) );
    }
    
    
    /**
     * send success message as json string
     *
     * @return void
     * @param mixed $datan
     */
    public function jsonSuccess($results) {
		ob_clean();
        header('Content-Type: application/json');
        header('Status: 200');
		// Tell the little time/memory diagnostic to shush
		// and not pollute our nice clean json response.
		\F3::set('JSONSENT', '1');
        echo json_encode(array('status' => 'success','message' => 'json data', 'results' => $results));
        exit(0);        
    }

	/**
	 * raw response
	 *
	 * @return void
	 * @param mixed $datan
	 */
	public function jsonRaw($results) {
	    $debug = ob_get_clean();
		header('Content-Type: application/json');
		header('Status: 200');
		// Tell the little time/memory diagnostic to shush
		// and not pollute our nice clean json response.
		\F3::set('JSONSENT', '1');
        echo json_encode($results);
		exit(0);
	}



	/**
	 *	Transmit csv/text file to HTTP client.
	 *  Return file size if successful, FALSE otherwise.
	 * 
	 *  This implementation uses a simplified version of the F3 Web send() code
	 *  and marries it with an MT-specified set of header values that increase
	 *  browser compatibility and has the calling code name the destination file.
	 * 
	 *	@param $sourceFilePath -- string full path to a local file, source of the download
	 *	@param $destinationFileName -- string filename (no path) for output file, including extension
	 * 
	 *	@return int|FALSE
	 **/
    public function sendFile($file, $downloadFileName)
	{
		if (!is_file($file)) {
			return FALSE;
		}
		
		// Scrub the output buffer before we starting pouring csv lines into it
		ob_clean();
		
		$size=filesize($file);
		if (PHP_SAPI!='cli') {
			header("Pragma: public");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false);
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"$downloadFileName\";" );
			header("Content-Transfer-Encoding: binary");
			header('Accept-Ranges: bytes');
			header('Content-Length: '.$size);		
		}
		
		$handle=fopen($file,'rb');
		while (!feof($handle) &&
			($info=stream_get_meta_data($handle)) &&
			!$info['timed_out'] && !connection_aborted()) {
			echo fread($handle,1024);
		}
		fclose($handle);
		
		return $size;
	}
    
    /**
     * generate minified css and js
     *
     * @return void
     */
    public function genMinifiedJsAndCss() {
        // minify js
        $targetJs = \F3::get('BASEDIR').'/public/all.js';
        if(!file_exists($targetJs) || \F3::get('DEBUG')!=0) {
            $js = "";
            foreach(\F3::get('js') as $file)
                $js = $js . "\n" . $this->minifyJs(file_get_contents(\F3::get('BASEDIR').'/'.$file));
            file_put_contents($targetJs, $js);
        }
    
        // minify css
        $targetCss = \F3::get('BASEDIR').'/public/all.css';
        if(!file_exists($targetCss) || \F3::get('DEBUG')!=0) {
            $css = "";
            foreach(\F3::get('css') as $file)
                $css = $css . "\n" . $this->minifyCss(file_get_contents(\F3::get('BASEDIR').'/'.$file));
            file_put_contents($targetCss, $css);
        }
    }
    
}
