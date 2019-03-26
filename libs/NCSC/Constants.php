<?php

namespace NCSC;

class Constants {
	const USER_TYPE_LIST = [
	    'OBSERVER' => 128,
	    'CLIENT'=>1,
		'ACCOUNTING'=>2,
		'QA'=>4,
		'PROJECT_MANAGER'=>8,
		'PRODUCTION_MANAGER'=>16,
		'ACCOUNT_MANAGER'=>32,
		'ADMINISTRATOR'=>64
	];
	const USER_TYPE_LIST_PROJECTS = [
	    'Observer',
        'Accounting',
        'QC',
        'Project Manager',
        'Production Manager'
	];
	const USER_TYPE_LABEL_VALUE = [
	    'Observer' => 'OBSERVER',
	    'Client' => 'CLIENT',
        'Accounting' => 'ACCOUNTING',
        'QC' => 'QA',
        'Project Manager' => 'PROJECT_MANAGER',
        'Production Manager' => 'PRODUCTION_MANAGER',
        'Account Manager' => 'ACCOUNT_MANAGER',
        'Administrator' => 'ADMINISTRATOR'
	];
	const USER_TYPE_VALUE_LABEL = [
	    'OBSERVER' => 'Observer',
	    'CLIENT' => 'Client',
        'ACCOUNTING' => 'Accounting',
        'QA' => 'QC',
        'PROJECT_MANAGER' => 'Project Manager',
        'PRODUCTION_MANAGER' => 'Production Manager',
        'ACCOUNT_MANAGER' => 'Account Manager',
        'ADMINISTRATOR' => 'Administrator',
	];
	const USER_TYPE_CLIENT = 1;
	const USER_TYPE_ACCOUNTING = 2;
	const USER_TYPE_QA = 4;
	const USER_TYPE_PROJECT_MANAGER = 8;
	const USER_TYPE_PRODUCTION_MANAGER = 16;
	const USER_TYPE_ACCOUNT_MANAGER = 32;
	const USER_TYPE_ADMIN = 64;
	const USER_TYPE_OBSERVER = 128;
	
	const USER_TYPE_CLIENT_NAME = 'CLIENT';
	const USER_TYPE_ACCOUNTING_NAME = 'ACCOUNTING';
	const USER_TYPE_QA_NAME = 'QA';
	const USER_TYPE_PROJECT_MANAGER_NAME = 'PROJECT_MANAGER';
	const USER_TYPE_PRODUCTION_MANAGER_NAME = 'PRODUCTION_MANAGER';
	const USER_TYPE_ACCOUNT_MANAGER_NAME = 'ACCOUNT_MANAGER';
	const USER_TYPE_ADMIN_NAME = 'ADMINISTRATOR';
	const USER_TYPE_OBSERVER_NAME = 'OBSERVER';
	
	const APPELLATECRIMINALSTRUCTURE = 'appellatecriminalstructure';
	const CASELOADSIZE = 'caseloadsize';
	const CASETYPE = 'casetype';
	const COURTLEVEL = 'courtlevel';
	const CSPAGG = 'cspagg';
	const DEATHPENALTY = 'deathpenalty';
	const FUNDING = 'funding';
	const USSTATENEIGHBOR = 'usstateneighbor';
	const POPULATIONCATEGORY = 'populationcategory';
	const POPULATIONDENSITY = 'populationdensity';
	const RURAL = 'rural';
	const TRIALSTRUCTURE = 'trialstructure';
	
	const TABLE_NAMES = [
        'usstateneighbor' => ['label' => 'State Neighbor', 'icon' => 'users', 'id' => 'USStateNeighborID', 'desc' => 'USStateName', 'order' => 'USStateName'],
        'casetype' => ['label' => 'Case Type', 'icon' => 'briefcase', 'id' => 'CaseTypeID', 'desc' => 'CaseTypeDescription', 'order' => 'CaseTypeDescription'],
        'appellatecriminalstructure' => ['label' => 'Appellate Structure', 'icon' => 'graduation-cap', 'id' => 'AppellateCriminalStructureID', 'desc' => 'AppellateCriminalStructureDescription', 'order' => 'DisplayOrder'],
        'caseloadsize' => ['label' => 'Case Load Size', 'icon' => 'balance-scale', 'id' => 'CaseloadSizeID', 'desc' => 'CaseloadSizeDescription', 'order' => 'DisplayOrder'],
        'courtlevel' => ['label' => 'Court Level', 'icon' => 'gavel', 'id' => 'CourtLevelID', 'desc' => 'CourtLevelDescription', 'order' => 'DisplayOrder'],
        'cspagg' => ['label' => 'CSP Agg', 'icon' => 'asterisk', 'id' => 'CSPAggID', 'desc' => 'CSPAggDescription', 'order' => 'DisplayOrder'],
        'deathpenalty' => ['label' => 'Death Penalty', 'icon' => 'bolt', 'id' => 'DeathPenaltyID', 'desc' => 'DeathPenaltyDescription', 'order' => 'DisplayOrder'],
        'funding' => ['label' => 'Funding', 'icon' => 'usd', 'id' => 'FundingID', 'desc' => 'FundingDescription', 'order' => 'DisplayOrder'],
        'populationcategory' => ['label' => 'Population Category', 'icon' => 'street-view', 'id' => 'PopulationCategoryID', 'desc' => 'PopulationCategoryDescription', 'order' => 'DisplayOrder'],
        'populationdensity' => ['label' => 'Population Density', 'icon' => 'th', 'id' => 'PopulationDensityID', 'desc' => 'PopulationDensityDescription', 'order' => 'DisplayOrder'],
	    'rural' => ['label' => 'Rural', 'icon' => 'tree', 'id' => 'RuralID', 'desc' => 'RuralDescription', 'order' => 'DisplayOrder'],
        'trialstructure' => ['label' => 'Trial Structure', 'icon' => 'barcode', 'id' => 'TrialStructureID', 'desc' => 'TrialStructureDescription', 'order' => 'DisplayOrder']
    ];
	
	const COURT_TABLES = ['casetype','courtlevel','cspagg','funding'];
	
	const CASE_TYPES_BY_COURT = [
        '52' => 'Administrative Agency Appeals',
        '34' => 'Administrative Agency-Other',
        '19' => 'Adoption',
        '38' => 'Advisory Opinion',
        '41' => 'Bar Admission',
        '37' => 'Certified Questions',
        '12' => 'Civil Appeals from LJ',
        '46' => 'Civil Protections Orders',
        '33' => 'Civil-Other',
        '2'  => 'Contract',
        '23' => 'Criminal Appeals',
        '31' => 'Criminal-Other',
        '16' => 'Custody',
        '30' => 'Death Penalty',
        '14' => 'Dissolution',
        '53' => 'Employment',
        '47' => 'Estate',
        '32' => 'Family',
        '21' => 'Felony',
        '5'  => 'Guardianship',
        '25' => 'Juvenile',
        '6'  => 'Landlord/Tenant',
        '8'  => 'Mental Health',
        '10' => 'Miscellaneous Civil',
        '20' => 'Misdemeanor',
        '27' => 'Mortgage Foreclosure',
        '49' => 'Non-Domestic Restraining Orders',
        '28' => 'Ordinance Violations',
        '29' => 'Parking',
        '15' => 'Paternity',
        '22' => 'Preliminary Hearings',
        '4'  => 'Probate/Estate',
        '45' => 'Probate/Wills/Interstate',
        '3'  => 'Real Property',
        '55' => 'Revenue (Tax)',
        '9'  => 'Small Claims',
        '24' => 'Status Offenses',
        '17' => 'Support',
        '48' => 'Tax',
        '1'  => 'Tort',
        '26' => 'Traffic Infractions',
        '18' => 'Visitation',
        '11' => 'Writ'
    ];	        

}