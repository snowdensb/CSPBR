<?php

namespace controllers;
use NCSC\Constants;

/**
 * Base controller class. All controllers should be derived 
 * indirectly from this class via
 * - ControllerPublic or
 * - ControllerSecure
 *
 * @package		controllers
 */
class ControllerBase
{

	protected $f3;
	protected $view;
	protected $db;
	protected $permissions;
	
	/**
	 * initialize controller
	 *
	 * @return void
	 */
	public function __construct()
	{
		session_start();
		$this->f3 = \Base::instance();		
		$this->view = new \Wyolution\View();
	
		\Wyolution\Logger::debug("X");
		
		if (!empty($this->f3->get('maintenance'))) {
		    $this->view->render('utils/maintenance.html','layout.html');
		    exit(0);
		}
		
		$this->db = $this->f3->get("DB");
		$this->permissions = new \NCSC\Permissions();
	}

	protected function publishStatusMessages($statusList)
	{
		$success = true;
		if (count($statusList['errorList']) > 0) {
			foreach ($statusList['errorList'] as $msg) {
				$this->view->messageError($msg);
			}
			$success = false;
		} 
		if (count($statusList['warningList']) > 0) {
			foreach ($statusList['warningList'] as $msg) {
				$this->view->messageWarning($msg);
			}
		}
		if (count($statusList['infoList']) > 0) {
			foreach ($statusList['infoList'] as $msg) {
				$this->view->messageInfo($msg);
			}
		}
		if (count($statusList['successList']) > 0) {
			foreach ($statusList['successList'] as $msg) {
				$this->view->messageSuccess($msg);
			}
		} // else, nothing to publish
		return $success;
	}
	
}
