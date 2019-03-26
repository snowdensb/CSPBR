<?php 
/**
 * smarty_prefilter_i18n()
 * This function takes the language file, and rips it into the template
 * $GLOBALS['_NG_LANGUAGE_'] is not unset anymore
 *
 * @param
 *        	$tpl_source
 * @return
 *
 *
 */
function smartyMLPrefilterI18N($tpl_source, $smarty) {
	if (! is_object ( $GLOBALS ['_NG_LANGUAGE_'] )) {
		die ( "Error loading Multilanguage Support" );
	}
	// load translations (if needed)
	$GLOBALS ['_NG_LANGUAGE_']->loadCurrentTranslationTable ();
	// Now replace the matched language strings with the entry in the file
	return preg_replace_callback ( '/##(.+?)##/', 'smartyMLCompileLang', $tpl_source );
}

/**
 * _compile_lang
 * Called by smarty_prefilter_i18n function it processes every language
 * identifier, and inserts the language string in its place.
 */
function smartyMLCompileLang($key) {
	return $GLOBALS ['_NG_LANGUAGE_']->getTranslation ( $key [1] );
}