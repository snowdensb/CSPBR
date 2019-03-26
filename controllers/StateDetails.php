<?php

namespace controllers;

/**
 *
 *
 * @package controllers
 *        
 */
class StateDetails extends ControllerPublic {
	
	/**
	 * Details for a given state
	 *
	 * @return void
	 */
	public function getStateDetails() {
	    $states = \Wyolution\F3Helpers::getParam('states', '');
	    $dir = \Wyolution\F3Helpers::getParam('dir', 'TD');
	    $stateDetails = new \NCSC\StateDetails();
	    $details = $stateDetails->getStateDetails($states, $dir);
	    if ($this->f3->get('VERB') == 'GET') {
	        echo "\n\n" . print_r($details,true);
	        exit(0);
	    }
	    $this->view->jsonRaw($details);
	}
	
}