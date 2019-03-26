<?php

namespace controllers;

/**
 *
 *
 * @package controllers
 *        
 */
class CourtDetails extends ControllerPublic {
	
	/**
	 * Details for a given state
	 *
	 * @return void
	 */
	public function getCourtDetails() {
	    $courtId = \Wyolution\F3Helpers::getParam('courtId', '');
	    $courtDetails = new \NCSC\CourtDetails();
	    $details = $courtDetails->getCourtDetails($courtId);
	    if ($this->f3->get('VERB') == 'GET') {
	        echo "\n\n" . print_r($details,true);
	        exit(0);
	    }
	    $this->view->jsonRaw($details);
	}
	
}