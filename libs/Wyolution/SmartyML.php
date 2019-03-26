<?php

namespace Wyolution;

class SmartyML extends \Smarty {
	var $language;
	var $languagePathRoot;
	
	function __construct($baseDir, $templateDir, $languagePathRoot, $locale = "en", $forceCompile = true) {
		parent::__construct();
		
		$this->template_dir = $templateDir;
		$this->config_dir = "$baseDir/smarty/restrict/config";
		$this->compile_dir = "$baseDir/smarty/restrict/templates_c";
		$this->cache_dir = "$baseDir/smarty/restrict/cache";
		$this->caching = false;
		$this->languagePathRoot = $languagePathRoot;
		$this->force_compile = $forceCompile;
		
		// Multilanguage Support
		// use $smarty->language->setLocale() to change the language of your template
		// $smarty->loadTranslationTable() to load custom translation tables
		$this->language = new SmartyTranslationHelper ( $locale ); // create a new language object
		$this->language->loadTranslationTable ( $locale, $this->languagePathRoot );
		$GLOBALS ['_NG_LANGUAGE_'] = &$this->language;
		$this->registerFilter( 'pre', "smartyMLPrefilterI18N" ); // smartyMLPrefilterI18N defined in SmartyMLHelpers.php
	}
	
	function fetch($template = null, $cache_id = null, $compile_id = null, $parent = null, $display = false, $merge_tpl_vars = true, $no_output_filter = false) {
		// We need to set the cache id and the compile id so a new script will be
		// compiled for each language. This makes things really fast ;-)
		$_smarty_compile_id = $this->language->getCurrentLanguage () . '-' . $compile_id;
		$_smarty_cache_id = $_smarty_compile_id;
		
		// Now call parent method
		return parent::fetch ( $template, $_smarty_cache_id, $_smarty_compile_id, $parent, $display, $merge_tpl_vars, $no_output_filter );
	}
	
	/**
	 * test to see if valid cache exists for this template
	 *
	 * @param string $tpl_file
	 *        	name of template file
	 * @param string $cache_id        	
	 * @param string $compile_id        	
	 * @return string false of {@link _read_cache_file()}
	 */
	public function isCached($template = null, $cache_id = null, $compile_id = null, $parent = null) {
		if (! $this->caching)
			return false;
		
		if (! isset ( $compile_id )) {
			$compile_id = $this->language->getCurrentLanguage () . '-' . $this->compile_id;
			$cache_id = $compile_id;
		}
		
		return parent::is_cached ( $tpl_file, $cache_id, $compile_id, $parent);
	}
}

?>
