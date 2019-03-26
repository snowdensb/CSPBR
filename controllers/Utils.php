<?php

namespace controllers;

/**
 * Utility class with methods for use during development
 *
 * @package		controllers
 * @author		Mark Thoney <mark@wyolution.com>
 * 
 */
class Utils extends ControllerPublic {
    
    /**
     * Show all values currently in the session
     *
     * @return void
     */
    public function session() {
    	$f3 = \Base::instance();
    	$f3->set('_session',print_r($_SESSION,true));
    	\Wyolution\View::instance()->renderPartial('utils/session.html',false);
    }
    /**
     * Show all values currently in the hive
     *
     * @return void
     */
    public function hive() {
    	$f3 = \Base::instance();
		// This is actually rather mind-blowingly meta
    	$f3->set('_hiveArray',$f3->hive());
		\Wyolution\View::instance()->renderPartial('utils/hive.html',false);
    }

	public function error() {
		$error = $this->f3->get('ERROR');
		\Wyolution\Logger::error(print_r($error, true));
		
		$this->f3->set('_f3error', $error);
		$this->view->render('utils/error.html','layout.html');
	}
}