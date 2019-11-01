<?php

namespace NCSC;

class StateDetails extends NCSCBase {
    
	public function getStateDetails($states, $dir): array {
	    if (empty($states)) {
	        $geoMapper = new \DB\SQL\Mapper($this->db,'geo_states');
		    $stateList = $geoMapper->find([],['order' => 'name']);  
		    foreach ($stateList as $state) {
		        $states .= $state->abv . ',';
		    }
		    $states = substr($states,0,-1);
	    }
	    
		$arCourtInfo = [];
		$arStates = explode(",", $states);
		foreach ($arStates as $state) {
			$state = str_replace("us-", "", strtolower($state));

			/* 
			 * build up the mermaid/appeals info 
			 * 	union added to pick up the top-level courts which don't have appeals entries
			 * */
			$appealsSql = '
		    	    select a.ChildCourtID, c.CourtName as CourtName, a.ParentCourtID, 
					b.CourtName as ParentCourtName, c.CourtLevelID as CourtLevelID, c.CSPAggID as CSPAggID,
					b.CourtLevelID as ParentCourtLevelID, b.CSPAggID as ParentCSPAggID
					from appealprocess as a
		    	    inner join court as b on b.CourtID = a.ParentCourtID
		    	    inner join court as c on c.CourtID = a.ChildCourtID
		    	    where a.ParentCourtID in
		    	    (
			            select a.CourtID from court as a
			            inner join usstatecourt as b on b.CourtID = a.CourtID
			            inner join usstate as c on c.USStateID = b.USStateID
						inner join geo_states on (geo_states.name = c.USStateName)
			            where geo_states.abv = ?
			            order by a.DisplayOrder
		            )
                    
                    UNION
                    
                    select CourtID as ChildCourtID, CourtName, null as ParentCourtID, null as ParentCourtName, 
					CourtLevelID, CSPAggID, null as ParentCourtLevelID, null as ParentCSPAggID
                    from court
                    inner join usstatecourt using (CourtID)
                    inner join usstate using (USStateID)
                    inner join geo_states on (geo_states.name = USStateName)
                    where CourtID not in (select ChildCourtID from appealprocess) and geo_states.abv = ?

		            order by ParentCourtID, ChildCourtID;
	        ';
			
			$courts = $this->db->exec($appealsSql, [$state, $state], 2);
			
			$courtInfo = ["state"=>$state];
			if (!empty($courts)) {
			    $mermaid = '';
				foreach ($courts as $court) {
					$mermaid = $this->getMermaidLine($court) . $mermaid;
				}
				$mermaid = "graph " . $dir . $this->mnl() . $mermaid;
				$courtInfo["mermaid"] = $mermaid;
			}
			
			// Get the statistical state info
			$courtInfo["details"] = $this->getStateDetail($state);
			
			// Shove it all into our courts object
			$courtInfo["courts"] = $courts;
			
			$arCourtInfo['us-' . $state] = $courtInfo;
		}
		return $arCourtInfo;

	}
	
	private function getMermaidLine(array $court): string {
		$ret = '';
		if ($court["ParentCourtID"] != null) {
		    $ret .= $court['ChildCourtID'] . "(" . $court['CourtName'] . ")";
		    $ret .= " --> ";
		    if ($court["ParentCourtLevelID"] == "COLR") {
				$ret .= $court['ParentCourtID'] . "(" . $court['ParentCourtName'] . ")";
		    }
		    else {
		    	$ret .= $court['ParentCourtID'] . "(" . $court['ParentCourtName'] . ")";
		    }
			$ret .= $this->mnl();
		}
		return $ret;
	}
	
	private function mnl() {
		return "\n";
	}
	
	private function getStateDetail(string $stateAbv): array {
		$sql = "select USStateName, PopulationCategoryDescription, PopulationDensityDescription, RuralDescription,
					TrialStructureDescription, AppellateCriminalStructureDescription, TrialCriminalProcessingDescription,
					DeathPenaltyDescription, CaseLoadSizeDescription, CaseLoadSizeID, PopulationCategoryID, 
					PopulationDensityID, RuralID from usstate
					inner join geo_states on (USStateName = geo_states.name)
					inner join populationcategory using (PopulationCategoryID)
					inner join populationdensity using (PopulationDensityID)
					inner join rural using (RuralID)
					inner join trialstructure using (TrialStructureID)
					inner join appellatecriminalstructure using (AppellateCriminalStructureID)
					inner join trialcriminalprocessing using (TrialCriminalProcessingID)
					inner join deathpenalty using (DeathPenaltyID)
					inner join caseloadsize using (CaseLoadSizeID)
					where geo_states.abv = ?";
		$details = $this->db->exec($sql, [$stateAbv], 1);
		if (!empty($details) > 0) {
			$details = $details[0];
			// NOTE: hard coded. Production would get these min/max vals from the table directly
			$details["CaseLoadSizePercent"] = ($details["CaseLoadSizeID"] * (1/6) * 100);
			$details["PopulationCategoryPercent"] = ($details["PopulationCategoryID"] * (1/7) * 100);
			$details["PopulationDensityPercent"] = ($details["PopulationDensityID"] * (1/7) * 100);
                        $details["RuralPopulationPercent"] = ($details["RuralID"]==-99?0:(100 - (25 * ($details["RuralID"] -1))));                        
			// $details["RuralPopulationPercent"] = ($details["RuralID"]==-99?0:($details["RuralID"] * (1/4) * 100));
			return $details;
		} else {
			return [];
		}
	}
}