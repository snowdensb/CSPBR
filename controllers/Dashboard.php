<?php

namespace controllers;

/**
 *
 *
 * User dashboard
 *
 * @package controllers
 *        
 */
class Dashboard extends ControllerPublic {
     
    public function main() {
        $stateFilters = new \NCSC\States();
        $filters = $stateFilters->getFilters();
        $this->f3->set('_stateFilters',$filters);
        $this->f3->set('_caseTypeCourtsFilter',\NCSC\Constants::CASE_TYPES_BY_COURT);
        $this->view->render('main.html');
    }   

}