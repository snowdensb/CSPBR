<?php

namespace NCSC;

class CourtDetails extends NCSCBase {
    
	public function getCourtDetails($courtId): array {
		$sql = "
					select * from court
					inner join courtlevel using (CourtLevelID)
					inner join funding using (FundingID)
					inner join courtcourtname using (CourtID)
					inner join courtname using (CourtNameID)
					inner join paneldecision using (PanelDecisionID)
					inner join casemanagement using (CaseManagementID)
					where courtId=?;
		";
		$court = $this->db->exec($sql, [$courtId], 1);
		if (!empty($court)) {
			
			// A single record
			$court = $court[0];
			
			// Gather the case type information for the court
			$csql = "select * from courtcasetype
					 inner join casetype using (CaseTypeID)
					 where CourtID = ?
					 order by casetype.DisplayOrder";
			$caseTypes = $this->db->exec($csql, [$court["CourtID"]], 1);
			$court["casetypes"] = $caseTypes;
			
			return $court;
		} else {
			return [];
		}
	}
	
}