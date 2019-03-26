<?php


namespace NCSC;


class Permissions
{
    protected $f3;
    protected $db;
    
    public function __construct()
    {
        $this->f3 = \Base::instance();
        $this->db = $this->f3->get("DB");
    }
   
    public function getAccessLevel():int {
        return intval($this->f3->get('SESSION.user.accessLevel'));
    }
    public function getAccessLevelDesc():string {
        return $this->f3->get('SESSION.user.accessLevelDesc');
    }
    public function resetAccessLevel() {
        // reset credentials to default
        $this->f3->set('SESSION.user.accessLevel',$this->f3->get('SESSION.user.defaultAccess.accessLevel'));
        $this->f3->set('SESSION.user.accessLevelDesc',$this->f3->get('SESSION.user.defaultAccess.accessLevelDesc'));
        $this->f3->set('SESSION.user.accessLevelLabel',$this->f3->get('SESSION.user.defaultAccess.accessLevelLabel'));
    }
    public function getUserId():int {
        return intval($this->f3->get('SESSION.user.userId'));
    }
    public function hasAdminAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return \NCSC\Constants::USER_TYPE_ADMIN & $accessLevel;
    }
    public function isAdmin():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_ADMIN_NAME == $accessLevel;
    }
    public function hasAccountManagerAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) || 
            (\NCSC\Constants::USER_TYPE_ACCOUNT_MANAGER & $accessLevel);
    }
    public function isAccountManager():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_ACCOUNT_MANAGER_NAME == $accessLevel;
    }
    public function hasProductionManagerAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) || 
            $this->hasAccountManagerAccess($accessLevel) ||
            (\NCSC\Constants::USER_TYPE_PRODUCTION_MANAGER & $accessLevel);
    }
    public function isProductionManager():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_PRODUCTION_MANAGER_NAME == $accessLevel;
    }
    public function hasGrantWriterAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) ||
            $this->hasAccountManagerAccess($accessLevel) ||
            $this->hasProductionManagerAccess($accessLevel) ||
            (\NCSC\Constants::USER_TYPE_GRANT_WRITER & $accessLevel);
    }
    public function isGrantWriter():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_GRANT_WRITER_NAME == $accessLevel;
    }
    public function hasQaAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) ||
            $this->hasAccountManagerAccess($accessLevel) ||
            $this->hasProductionManagerAccess($accessLevel) ||
            (\NCSC\Constants::USER_TYPE_QA & $accessLevel);
    }
    public function isQa():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_QA_NAME == $accessLevel;
    }
    public function hasAccountingAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) ||
            $this->hasAccountManagerAccess($accessLevel) ||
            $this->hasProductionManagerAccess($accessLevel) ||
            (\NCSC\Constants::USER_TYPE_ACCOUNTING & $accessLevel);
    }
    public function isAccounting():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_ACCOUNTING_NAME == $accessLevel;
    }
    public function hasClient(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) ||
            $this->hasAccountManagerAccess($accessLevel) ||
            $this->hasProductionManagerAccess($accessLevel) ||
            $this->hasQAWriter($accessLevel) ||
            $this->hasAccountingAccess($accessLevel) ||
            (\NCSC\Constants::USER_TYPE_CLIENT & $accessLevel);
    }
    public function isClient():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_CLIENT_NAME == $accessLevel;
    }
    public function hasObserverAccess(int $accessLevel = 0):bool {
        $accessLevel = ($accessLevel == 0) ? $this->getAccessLevel() : $accessLevel;
        return $this->hasAdminAccess($accessLevel) ||
        $this->hasAccountManagerAccess($accessLevel) ||
        $this->hasProductionManagerAccess($accessLevel) ||
        $this->hasQAWriter($accessLevel) ||
        $this->hasAccountingAccess($accessLevel) ||
        (\NCSC\Constants::USER_TYPE_OBSERVER & $accessLevel);
    }
    public function isObserver():bool {
        $accessLevel = $this->getAccessLevelDesc();
        return \NCSC\Constants::USER_TYPE_OBSERVER_NAME == $accessLevel;
    }
    public function userTypeList():array {
    	return $this->hasAdminAccess()?\NCSC\Constants::USER_TYPE_LIST:[];
	}
	
	public function hasAccessToClient(int $clientId):bool {
	    if ($this->hasAdminAccess()) {
	        return true;
	    }
	    $clientProjectUserMapper = new \DB\SQL\Mapper($this->db,'clientprojectuser');
	    return $clientProjectUserMapper->count(['users_id = ? and client_id = ?',$this->getUserId(),$clientId]) > 0;
	}
	
	public function hasAccessToProject(int $projectId):bool {
	    if ($this->hasAdminAccess()) {
	        return true;
	    }
	    $clientProjectUserMapper = new \DB\SQL\Mapper($this->db,'clientprojectuser');
	    if (empty($projectId)) {
	       return $clientProjectUserMapper->count(['users_id = ? and (project_id = 0)',$this->getUserId()]) > 0;
	    }
	    else {
	        return $clientProjectUserMapper->count(['users_id = ? and (project_id = 0 or project_id = ?)',$this->getUserId(),$projectId]) > 0;
	    }
	}
	
	/**
	 * If fileId is provided and is not zero, then fileVersionId will be ignored.
	 * 
	 * @param int $fileVersionId
	 * @param int $fileId
	 * @return bool
	 */
	public function hasAccessToFile(int $fileVersionId, int $fileId = 0):bool {
	    
	    if ($fileId == 0) {
    	    $fileVersionMapper = new \DB\SQL\Mapper($this->db,'fileversion');
    	    $fileVersionMapper->load(['id = ?',$fileVersionId]);
    	    if ($fileVersionMapper->dry()) {
    	        return false;
    	    }
    	    $fileId = $fileVersionMapper->file_id;
	    }

	    $clientId = 0;
	    $projectId = 0;
	    
	    // a user can have access to a file even if file is not assigned to project.  This
	    // happens when a file is a client asset.  So, we check to see if file is a client asset 
	    // first.  If it is, then we check to see if user has access to the client (i.e. is working
	    // a project that is assigned to the client).  If not, then we use the project ID.
	    $clientAsset = false;
	    $fileMapper = new \DB\SQL\Mapper($this->db,'file');
	    $fileMapper->load(['id = ?',$fileId]);
	    if (!$fileMapper->dry()) {
	        $clientId = $fileMapper->client_id;
	        $clientAsset = $fileMapper->type == 'Client Asset';
	    }
	    else {
	        \Wyolution\Logger::debug('Unable to find file:' . $fileId);
	        return false;
	    }
	    
	    if (!$clientAsset) {
    	    $fileProjectMapper = new \DB\SQL\Mapper($this->db,'fileproject');
    	    $fileProjectMapper->load(['file_id = ?',$fileId]);
    	    if ($fileProjectMapper->dry()) {
    	        \Wyolution\Logger::debug('Unable to find project file:' . $fileId);
    	        return false;
    	    }
    	    else {
    	        $projectId = $fileProjectMapper->project_id;
    	    }
	    }
	    
	    if ($projectId != 0) {
	        \Wyolution\Logger::debug('File Check project:' . $projectId);
	        return $this->hasAccessToProject($projectId);
	    }
	    elseif ($clientId != 0) {
	        \Wyolution\Logger::debug('File Check client:' . $clientId);
	        return $this->hasAccessToClient($clientId);
	    }
	    
	    return false;
	}
	
	/**
	 * Will default to grants steps if a valid projectType is not input.
	 * 
	 * @param int $currentStep
	 * @param string $projectType
	 * @return bool
	 */
	public function isClientStep(int $currentStep, string $projectType = ''):bool {
	    $projects = new \NCSC\Projects();
	    $steps = $projects->getStepsByRole($projectType);
	    if (in_array($currentStep,$steps['CLIENT'])) {
	        return true;
	    }
	    else {
	        return false;
	    }
	}
	
}