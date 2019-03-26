<?php

namespace controllers;

/**
 * Parent controller for all controllers that require user to be authenticated before invoking
 *
 * @package		controllers
 * 
 */
class ControllerSecure  extends ControllerBase {

	protected $auditInfo;
	protected $doAudit;
	
    /**
     * initialize controller
     *
     * @return void
     */
    public function __construct() {
		parent::__construct();
		
		\Wyolution\Logger::debug("Class: " . get_called_class());
		
		$this->auditInfo = array();
		$this->doAudit = false;
	}

	/** 
     * Check to make sure user is logged in and do any other 
	 * necessary work prior to running controller.
     */
    function beforeRoute()
	{
		\Wyolution\Logger::debug('ControllerSecure::beforeRoute');
		$userIsLoggedIn = $this->f3->get('SESSION.userIsLoggedIn');
		if (empty($userIsLoggedIn) || ($userIsLoggedIn != '1')) {
			// Now that we have remembered logins, we need to remember the user's 
			// target route here
			$destinationRoute = $this->f3->get('PATH');
			$this->f3->set('SESSION.destinationRoute', $destinationRoute);
			$this->f3->reroute('/login');
		}
		
		// We want to record audit info on changes to the db, so
		// on POST calls
		if ($this->f3->get('VERB') == 'POST') {
			$this->gatherAuditInfo();
		}
    }

	/**
	 * Write out audit log if user changed any db records
	 * (or even tried to and failed)
	 */
	function afterRoute() {
		//\Wyolution\Logger::debug('ControllerSecure::afterRoute');
		if ($this->doAudit) {
			$resultStatus = $this->view->getResultStatus();
			if ($resultStatus !== 'success') {
				$this->auditInfo['messages'] = $this->f3->get('SESSION.appMessages');
			}
			$this->auditInfo['resultStatus'] = $resultStatus;
			$info = json_encode($this->auditInfo);
			\Wyolution\Audit::log($info);
		}
    }
	
	private function gatherAuditInfo() {
		$this->auditInfo['verb'] = $this->f3->get('VERB');
		$this->auditInfo['params'] = $this->f3->get('PARAMS');
		$post = $this->f3->get('POST');
		if (isset($post['user'])) {
			if (isset($post['user']['password'])) {
				unset($post['user']['password']);
			}
			if (isset($post['user']['passwordConfirm'])) {
				unset($post['user']['passwordConfirm']);
			}
		}
		$this->auditInfo['post'] = $post;
		$this->doAudit = true;
	}

	protected function initItem($fieldList)
	{
		$item = [];
		foreach ($fieldList as $field) {
			$item[$field] = '';
		}
		return $item;
	}
	/**
	 * @param statusList
	   @return string
	 */
	protected function formatStatusHtml($statusList):string {
		$html = '';
		if (isset($statusList['errorList']) && count($statusList['errorList']) > 0) {
			$html .= '<div class="alert alert-danger"><ul>';
			foreach ($statusList['errorList'] as $msg) {
				$html .= "<li>$msg</li>";
			}
			$html .= '</ul></div>';
		} 
		if (isset($statusList['warningList']) && count($statusList['warningList']) > 0) {
			$html .= '<div class="alert alert-info"><ul>';
			foreach ($statusList['warningList'] as $msg) {
				$html .= "<li>$msg</li>";
			}
			$html .= '</ul></div>';
		}
		if (isset($statusList['infoList']) && count($statusList['infoList']) > 0) {
			$html .= '<div class="alert alert-warning"><ul>';
			foreach ($statusList['infoList'] as $msg) {
				$html .= "<li>$msg</li>";
			}
			$html .= '</ul></div>';
		}
		if (isset($statusList['successList']) && count($statusList['successList']) > 0) {
			$html .= '<div class="alert alert-success"><ul>';
			foreach ($statusList['successList'] as $msg) {
				$html .= "<li>$msg</li>";
			}
			$html .= '</ul></div>';
		}
		return $html;
	}
}