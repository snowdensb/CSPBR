<?php

namespace NCSC;

class States extends NCSCBase {
    
    public function getAppellateCriminalStructure(bool $filter = false):array {
        $results = $this->getTableData(\NCSC\Constants::APPELLATECRIMINALSTRUCTURE);
        return $results;
    }
    public function getCaseloadSize():array {
        $results = $this->getTableData(\NCSC\Constants::CASELOADSIZE);
        return $results;
    }
    public function getCaseTypes():array {
        $results = $this->getTableData(\NCSC\Constants::CASETYPE);
        return $results;
    }
    public function getCourtLevel():array {
        $results = $this->getTableData(\NCSC\Constants::COURTLEVEL);
        return $results;
    }
    public function getCSPAggregation():array {
        $results = $this->getTableData(\NCSC\Constants::CSPAGG);
        return $results;
    }
    public function getDeathPenalty():array {
        $results = $this->getTableData(\NCSC\Constants::DEATHPENALTY);
        return $results;
    }
    public function getFundingSource():array {
        $results = $this->getTableData(\NCSC\Constants::FUNDING);
        return $results;
    }
    public function getNeighborState():array {
        $results = $this->getTableData(\NCSC\Constants::USSTATENEIGHBOR);
        return $results;
    }
    public function getPopulation():array {
        $results = $this->getTableData(\NCSC\Constants::POPULATIONCATEGORY);
        return $results;
    }
    public function getPopulationDensity():array {
        $results = $this->getTableData(\NCSC\Constants::POPULATIONDENSITY);
        return $results;
    }
    public function getRural():array {
        $results = $this->getTableData(\NCSC\Constants::RURAL);
        return $results;
    }
    public function getTrialStructure():array {
        $results = $this->getTableData(\NCSC\Constants::TRIALSTRUCTURE);
        return $results;
    }
    
    public function getFilters():array {
        $results = [];
        foreach(\NCSC\Constants::TABLE_NAMES as $tableName => $tableInfo) {
            if ($tableName == \NCSC\Constants::CSPAGG) {
                $tableData = $this->getTableData(\NCSC\Constants::COURTLEVEL,$tableInfo['order']);
                $tableInfo['desc'] = 'CourtLevelDescription';
                $tableInfo['id'] = 'CourtLevelID';
            }
            else {
                $tableData = $this->getTableData($tableName,$tableInfo['order']);
            }
            $temp = [];
            foreach($tableData as $row) {
                $temp[trim($row[$tableInfo['id']])] =$row[$tableInfo['desc']];
            }
            $results[$tableName] = $temp;
        }
        return $results;
    }
    
    public function getStates(array $filter):array {
        $tables = \NCSC\Constants::TABLE_NAMES;
        $where = '';
        $params = [];
        $courtIds = [];
        
        // clean filter
        foreach ($filter as $table => $idValues) {
            if (empty(trim(implode('',$idValues)))) {
                unset($filter[$table]);
            }
        }
        
        // gather courtIds if case types defined in filter
        foreach ($filter as $table => $idValues) {
            if (in_array($table, \NCSC\Constants::COURT_TABLES)) {
                $where .= '(';
                foreach ($idValues as $value) {
                    if ($table == \NCSC\Constants::CASETYPE) {
                        // extra space after OR is required so -5 below works
                        $where .=  'b.'.$tables[$table]['id'] . ' = ? OR  ';
                    }
                    else {
                        $where .=  'a.'.$tables[$table]['id'] . ' = ? AND ';
                    }
                    $params[] = $value;
                }
                $where = substr($where,0,-5) . ') AND ';
            }
        }
        if (!empty($where)) {
            $sql = "select distinct a.CourtID from court as a left join courtcasetype as b on b.CourtID = a.CourtID where $where 1=1";
            \Wyolution\Logger::debug($sql . "\n" . print_r($params,true));
            $courts = $this->db->exec($sql, $params, 1);
            \Wyolution\Logger::debug('Courts:' . print_r($courts,true));
            foreach ($courts as $row) {
                $courtIds[] = $row['CourtID'];
            }
        }
        \Wyolution\Logger::debug('Court IDs:' . print_r($courtIds,true));
        
        // filter states
        $params = [];
        $where = '';
        foreach ($filter as $table => $idValues) {
            if (!in_array($table, \NCSC\Constants::COURT_TABLES)) {
                $where .= '(';
                foreach ($idValues as $value) {
                    if ($table == \NCSC\Constants::USSTATENEIGHBOR) {
                        // extra space after OR is required so -5 below works
                        $where .=  'c.'.$tables[$table]['id'] . ' = ? OR  ';
                    }
                    else {
                        $where .=  'a.'.$tables[$table]['id'] . ' = ? AND ';
                    }
                    $params[] = $value;
                }
                $where = substr($where,0,-5) . ') AND ';
            }
        }
        if (!empty($courtIds)) {
            $where .= 'b.CourtID in (' . implode(',',$courtIds) . ') AND ';
        }
        
        $sql = "select distinct a.*, geo_states.abv as state from usstate as a inner join geo_states on (a.USStateName = geo_states.name) left join usstatecourt as b on b.USStateID = a.USStateID left join neighbor as c on c.USStateID = a.USStateID where $where 1=1 order by DisplayOrder";
        \Wyolution\Logger::debug($sql . "\n" . print_r($params,true));

        $states = $this->db->exec($sql, $params, 1);
        return $states;
    }
    
    public function getStateCategoryData() {
        $sql = '
            select h.abv, a.USStateName,
            b.PopulationCategoryDescription as populationcategory, b.PopulationCategoryID as populationcategoryID,
            c.PopulationDensityDescription as populationdensity, c.PopulationDensityID as populationdensityID, 
            d.AppellateCriminalStructureDescription as appellatecriminalstructure, d.AppellateCriminalStructureID as appellatecriminalstructureID, 
            e.CaseloadSizeDescription as caseloadsize, e.CaseloadSizeID as caseloadsizeID,
            f.DeathPenaltyDescription as deathpenalty, f.DeathPenaltyID as deathpenaltyID,
            g.RuralDescription as rural, g.RuralID as ruralID
            from usstate as a
            left join populationcategory as b on b.PopulationCategoryID = a.PopulationCategoryID
            left join populationdensity as c on c.PopulationDensityID = a.PopulationDensityID
            left join appellatecriminalstructure as d on d.AppellateCriminalStructureID = a.AppellateCriminalStructureID
            left join caseloadsize as e on e.CaseloadSizeID = a.CaseloadSizeID
            left join deathpenalty as f on f.DeathPenaltyID = a.DeathPenaltyID
            left join rural as g on g.RuralID = a.RuralID
            left join geo_states as h on h.name = a.USStateName
            where h.abv is not null;
        ';
        $data = $this->db->exec($sql, [], 1);
        return $data;
    }
    
    public function getCategoryTotals() {
        $sql = "
            select 'populationcategory' as category, count(PopulationCategoryID) as total from populationcategory
            union
            select 'populationdensity' as category, count(PopulationDensityID) as total from populationdensity
            union
            select 'appellatecriminalstructure' as category, count(AppellateCriminalStructureID) as total from appellatecriminalstructure
            union
            select 'caseloadsize' as category, count(CaseloadSizeID) as total from caseloadsize
            union
            select 'deathpenalty' as category, count(DeathPenaltyID) as total from deathpenalty
            union
            select 'rural' as category, count(RuralID) as total from rural;
        ";
        $data = $this->db->exec($sql, [], 1);
        return $data;
    }
    
    public function getCourtCaseType() {
        $sql = "
            select distinct concat('US-',f.abv) as abv, b.CourtID, b.CourtName, c.CaseTypeID, c.CaseTypeDescription from courtcasetype as a
            left join court as b on b.CourtID = a.CourtID
            left join casetype as c on c.CaseTypeID = a.CaseTypeId
            left join usstatecourt as d on d.CourtID = a.CourtID
            left join usstate as e on e.USStateID = d.USStateID
            left join geo_states as f on f.`name` = e.USStateName
            where e.USStateName IS NOT NULL
            order by e.USStateName, c.CaseTypeDescription;
        ";
        $data = $this->db->exec($sql, [], 1);
        return $data;
    }
    
    private function getTableData(string $tableName,string $order = 'DisplayOrder'):array {
        $dataMapper = new \DB\SQL\Mapper($this->db,$tableName);
        $data = $dataMapper->find([],['order' => "$order ASC"]);
        $results = [];
        foreach ($data as $row) {
            $results[] = $row->cast();
        }
        return $results;
    }
}