<?php

namespace controllers;

/**
 *
 *
 * @package controllers
 *        
 */
class StateFilter extends ControllerPublic {
	
	/**
	 * Get user to the home dashboard screen with enough data to fill in the various boxes
	 *
	 * @return void
	 */
	public function getStates() {
	    $filter = \Wyolution\F3Helpers::getParam('stateFilter');
	    $filter = empty($filter)?[]:$filter;
	    $states = new \NCSC\States();
	    $filteredStates = $states->getStates($filter);
	    if ($this->f3->get('VERB') == 'GET') {
	        echo "\n\n" . print_r($filteredStates,true);
	        exit(0);
	    }
	    $this->view->jsonRaw($filteredStates);
	}

	public function getStatesCategory() {
	    $states = new \NCSC\States();
	    $dataTable = [];
	    $dataTable['data'] = $states->getStateCategoryData();
	    $temp = $states->getCategoryTotals();
	    $dataTable['info'] = [];
	    foreach ($temp as $item) {
	        $dataTable['info'][$item['category']] = $item['total'];
	    }
	    if ($this->f3->get('VERB') == 'GET') {
	        echo "\n\n" . print_r($dataTable,true);
	        exit(0);
	    }
	    $this->view->jsonRaw($dataTable);
	}
	
	public function getCourtsByCaseType() {
	    $states = new \NCSC\States();
	    $temp = $states->getCourtCaseType();
	    $data = [];
	    foreach ($temp as $item) {
	        if (empty($item['abv'])) {
	            continue;
	        }
	        if (!isset($data[$item['abv']][$item['CaseTypeID']])) {
	            $data[$item['abv']][$item['CaseTypeID']] = [];
	        }
	        $data[$item['abv']][$item['CaseTypeID']][] = $item['CourtID'];
	    }
	    if ($this->f3->get('VERB') == 'GET') {
	        echo "\n\n" . print_r($data,true);
	        exit(0);
	    }
	    $this->view->jsonRaw($data);
	}
	
}