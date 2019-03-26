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
class Main extends ControllerPublic {
	
	/**
	 * Get user to the home dashboard screen with enough data to fill in the various boxes
	 *
	 * @return void
	 */
	public function dashboard() {

	    $state = \Wyolution\F3Helpers::getParam('state','Wyoming');
        // $this->f3->set('SESSION.foobar',1);
    	            
        //$this->f3->set('_courts',$courts);
        $this->f3->set('_currentState',$state);
        $this->f3->set('_states',$this->getStates());
        $caseTypes = $this->getCaseTypesForView($state, ["LJ","GJ"]);
        $this->f3->set('_caseTypes',$caseTypes);
        $this->f3->set('_caseTypesJson',json_encode($caseTypes));
        $courts = $this->getCourtsByState($state);
        $courtLevels = [];
        foreach ($courts as $court) {
            $courtLevels[$court['CourtLevelID']] = 1;
        }
        $this->f3->set('_courts',$courts);
        $this->f3->set('_courtLevels',$courtLevels);
        $this->f3->set('_courtLevelsAll',['LJ'=>'Lowest Jurisdiction','GJ'=>'General Jurisdiction','IAC'=>'Intermediate Appellate Court','COLR'=>'Court of Last Resort']);
        
	    $this->view->render('main.html');
	}
	
	public function courtChart() {
	    
	    $state = \Wyolution\F3Helpers::getParam('state','Wyoming');
	    
	    $sql = '
    	    select a.ChildCourtID, c.CourtName as CourtName, a.ParentCourtID, b.CourtName as ParentCourtName from appealprocess as a
    	    left join court as b on b.CourtID = a.ParentCourtID
    	    left join court as c on c.CourtID = a.ChildCourtID
    	    where a.ParentCourtID in
    	    (
	            select a.CourtID from court as a
	            left join usstatecourt as b on b.CourtID = a.CourtID
	            left join usstate as c on c.USStateID = b.USStateID
	            where c.USStateName = ?
	            order by a.DisplayOrder
            )
            order by a.ParentCourtID;
        ';
	    
	    $courts = $this->db->exec($sql, [$state], 1);
	    
	    $results = [];
	    foreach($courts as $court) {
	        if (!isset($results[$court['ParentCourtID']])) {
	            $results[$court['ParentCourtID']] = ['id' => $court['ParentCourtID'],'text' => $court['ParentCourtName']];
	        }
	        $results[$court['ChildCourtID']] = ['id' => $court['ChildCourtID'],'text' => $court['CourtName'],'parent' => $court['ParentCourtID']];
	    }

	    $this->f3->set('_currentState',$state);
	    $this->f3->set('_states',$this->getStates());
	    $this->f3->set('_courtsJson',json_encode(array_values($results)));
	    
	    $this->view->render('chart.html');
	}
	
	public function chart2() {
		
		$state = \Wyolution\F3Helpers::getParam('state','Wyoming');
		$this->f3->set('_currentState',$state);
		$this->f3->set('_states',$this->getStates());
		$caseTypes = $this->getCaseTypesForView($state,["LJ","GJ","IAC","COLR"]);
		$this->f3->set('_caseTypes',$caseTypes);
		$this->f3->set('_caseTypesJson',json_encode($caseTypes));
		$courts = $this->getCourtsByState($state);
		$courtLevels = [];
		foreach ($courts as &$court) {
			$courtLevels[$court['CourtLevelID']] = 1;
			$court["Parents"] = $this->getCourtParents($court["CourtID"], false);
		}
		$this->f3->set('_courts',$courts);
		$this->f3->set('_courtsJson', json_encode($courts));
		$this->f3->set('_courtLevels',$courtLevels);
		$this->f3->set('_courtLevelsAll',['LJ'=>'Lowest Jurisdiction','GJ'=>'General Jurisdiction','IAC'=>'Intermediate Appellate Court','COLR'=>'Court of Last Resort']);
		
		$this->view->render('chart2.html');
	}
	
	public function chart3() {
	    $stateFilters = new \NCSC\States();
	    $filters = $stateFilters->getFilters();
	    $this->f3->set('_stateFilters',$filters);
		$this->view->render('chart3.html');
	}
	
	private function getStates():array {
	    $sql = 'select USStateName from usstate order by USStateName';
	    $states = $this->db->exec($sql, [], 1);
	    $results = [];
	    foreach ($states as $state) {
	        $results[] = $state['USStateName'];
	    }
	    return $results;
	}
	
	private function getCaseTypesForView(string $USStateName, array $arCourtLevels): array {
		$caseTypes = $this->getCaseTypesByState($USStateName);
		$arCaseTypes = [];
		foreach ($caseTypes as $caseType) {
			$arCaseType = ["label"=>$caseType["CaseTypeDescription"], "caseTypeID"=>(int)$caseType["CaseTypeID"]];
			$courts = $this->getCourtsByStateAndType($USStateName, $arCourtLevels, $caseType["CaseTypeID"]);
			$arCourts = [];
			foreach ($courts as $court) $arCourts[] = (int)$court["CourtID"];
			$arCaseType["courts"] = $arCourts;
			$arCaseTypes["case".$caseType["CaseTypeID"]] = $arCaseType;
		}
		return $arCaseTypes;
	}
		
	/**
	 * Given a USStateName (e.g. 'Wyoming') return all of the case types in use by courts in that state
	 * @param string $USStateName
	 * @return array
	 */
	private function getCaseTypesByState(string $USStateName): array {
		$sql = "SELECT distinct casetype.* FROM casetype
					inner join courtcasetype using (CaseTypeID)
					inner join court using (CourtID)
					inner join usstatecourt using (CourtID)
					inner join usstate using (USStateID)
					where USStateName = ?
					order by DisplayOrder";
		$caseTypes = $this->db->exec($sql, [$USStateName], 1);
		return $caseTypes;
	}
	
	/**
	 * Given a USStateName (e.g. 'Wyoming') and a case type id (e.g. 1), get the courts that satisfy those conditions
	 * @param string $USStateName
	 * @param array $arCourtLevels
	 * @param int $CaseTypeID - get from getCaseTypesByState
	 * @return array
	 */
	private function getCourtsByStateAndType(string $USStateName, array $arCourtLevels, int $CaseTypeID): array {
		for ($i=0; $i<count($arCourtLevels); $i++) $arCourtLevels[$i] = '"'.$arCourtLevels[$i].'"';
		$values = implode(",", $arCourtLevels);
		$sql = "SELECT distinct court.* FROM court
					inner join usstatecourt using (CourtID)
					inner join usstate using (USStateID)
					inner join courtcasetype using (CourtID)
					where USStateName = ? and CaseTypeID=? and CourtLevelID in ($values)
					order by court.DisplayOrder";
		$courts = $this->db->exec($sql, [$USStateName, $CaseTypeID], 2);
		return $courts;
	}
	
	/**
	 * Given a USStateName (e.g. 'Wyoming'), get all the courts across all court levels
	 * @param string $USStateName
	 * @return array
	 */
	private function getCourtsByState(string $USStateName): array {
	    $sql = '
            select a.CourtID, a.CourtName, a.Notes, a.CourtLevelID, d.CourtLevelDescription from court as a
            left join usstatecourt as b on b.CourtID = a.CourtID
            left join usstate as c on c.USStateID = b.USStateID
            left join courtlevel as d on d.CourtLevelID = a.CourtLevelID
            where c.USStateName = ?
            order by d.DisplayOrder
        ';
	    $courts = $this->db->exec($sql, [$USStateName], 2);
	    return $courts;
	}

	/**
	 * Get a court's parents
	 * @param int $CourtID The ID of the court in question
	 * @param bool $getHierarchy True if you want to get the Parents' parents, recursively; False to just get this courts next-level parents
	 * @return array
	 */
	private function getCourtParents(int $CourtID, bool $getHierarchy): array {
		$sql = "select distinct court.* from court
					inner join appealprocess on (appealprocess.ParentCourtID = court.CourtID)
					where appealprocess.ChildCourtID = ? 
					order by court.DisplayOrder";
		$courts = $this->db->exec($sql, [$CourtID], 1);
		$arCourts = [];
		foreach ($courts as $court) {
			if ($getHierarchy && $court["CourtLevelID"]!="COLR") {
				$court["Parents"] = $this->getCourtParents($court["CourtID"], true);
			}
			$arCourts[] = $court;
		}
		return $arCourts;
	}
}