<?php


namespace NCSC;


/**
 * Class ViewHelper -- this is the one and only class that \Wyolution\View::render will register
 * with Smarty just before rendering our layouts. It contains methods for:
 * - giving the views access to specific data sets needed for drop downs, etc
 * - concatenating or formatting data in ways that are easier or better done in php code than in smarty
 */
class ViewHelper extends ModelBase
{

    protected $permissions;
    
	public function __construct()
	{
	    $this->permissions = new \NCSC\Permissions();
		parent::__construct();
	}
	
	public function getFilterName(string $value):string {
	    $tables = \NCSC\Constants::TABLE_NAMES;
	    if (isset($tables[$value])) {
	        return $tables[$value]['label'];
	    }
	    else {
	        return 'Unknown';
	    }
	}
	
	public function getIcon(string $value):string {
	    $tables = \NCSC\Constants::TABLE_NAMES;
	    if (isset($tables[$value])) {
	        return $tables[$value]['icon'];
	    }
	    else {
	        return 'Unknown';
	    }
	}
	
	public function getCourtLevelColor($courtLevel) {
	    switch ($courtLevel) {
	        case 'COLR':
	            $color = 'warning';
	            break;
	        case 'GJ':
	            $color = 'info';
	            break;
	        case 'LJ':
	            $color = 'primary';
	            break;
	        default:
	            $color = 'success';
	            break;
	    }
	    
	    return $color;
	} 
		
	public function getFileIcon($filename) {
	    $parts = pathinfo($filename);
	    $extension = $parts['extension'] ?? '';
	    $icon = 'fa-file-o';
	    switch ($extension) {
	        case 'xls':
	        case 'xlsx':
	            $icon = 'fa-file-excel-o';
	            break;
	        case 'doc':
	        case 'docx':
	            $icon = 'fa-file-word-o';
	            break;
	        case 'pdf':
	            $icon = 'fa-file-pdf-o';
	            break;
	        case 'zip':
	        case 'rar':
	            $icon = 'fa-file-archive-o';
	            break;
	        case 'ppt':
	        case 'pptx':
	            $icon = 'fa-file-powerpoint-o';
	            break;
	        case 'jpg':
	        case 'png':
	        case 'gif':
	            $icon = 'fa-file-image-o';
	            break;
	    }
	    return $icon . ' ' . $filename;
	}
	
	/**
	 * generate minified js
	 *
	 * @return string one or more script tags for JS
	 */
	public function genMinifiedJs($name,$files) {
	    // minify js
	    $targetJs = \F3::get('BASEDIR').'/public/'.$name.'.js';
	    if(!file_exists($targetJs) || \F3::get('env') == 'dev') {
	        $js = \Web::instance()->minify($files, null, false, \F3::get('BASEDIR'));
	        $js = '';
	        foreach ($files as $file) {
	            $js .= file_get_contents(\F3::get('BASEDIR').$file)."\n";
	        }
	        file_put_contents($targetJs, $js);
	    }
	    // return either each js in a script tag or return one script tag.
	    $result = '';
	    $appPath = \Wyolution\View::instance()->getAppPath();
	    if (\F3::get('LIST_JS_CSS') == '1') {
	        foreach ($files as $file) {
	            $result .= '<script src="'.$appPath.$file.'?v='.\F3::get('_version').'"></script>'."\n";
	        }
	    }
	    else {
	        $result = '<script src="'.$appPath.'/public/'.$name.'.js?v='.\F3::get('_version').'"></script>'."\n";
	    }
	    echo $result;
	}
	
	/**
	 * generate minified css
	 *
	 * @return void
	 */
	public function genMinifiedCss($name,$files) {
	    // minify css
	    $targetCss = \F3::get('BASEDIR').'/public/css/'.$name.'.css';
	    if(!file_exists($targetCss) || \F3::get('env') == 'dev') {
	        $css = \Web::instance()->minify($files, null, false, \F3::get('BASEDIR'));
	        $css = '';
	        foreach ($files as $file) {
	            $css .= file_get_contents(\F3::get('BASEDIR').$file)."\n";
	        }
	        file_put_contents($targetCss, $css);
	    }
	    // return either each css in a link tag or return one script tag.
	    $result = '';
	    $appPath = \Wyolution\View::instance()->getAppPath();
	    if (\F3::get('LIST_JS_CSS') == '1') {
	        foreach ($files as $file) {
	            $result .= '<link href="'.$appPath.$file.'?v='.\F3::get('_version').'" rel="stylesheet">'."\n";
	        }
	    }
	    else {
	        $result = '<link href="'.$appPath.'/public/css/'.$name.'.css?v='.\F3::get('_version').'" rel="stylesheet">'."\n";
	    }
	    echo $result;
	}
	
}