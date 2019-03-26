<?php


namespace NCSC;


class ModelBase
{
	protected $f3;
	protected $db;

	protected $statusMessages;
	
	public function __construct()
	{
		$this->f3 = \Base::instance();
		$this->db = $this->f3->get("DB");

		// We use this structure to pass info back to the controller or
		// whoever called us. Up to caller to figure out how to use the
		// info.
		$this->statusMessageList = self::getCleanStatusList();
	}
	
	public static function getCleanStatusList() {
		$statusList = array(
			'errorList' => array(),
			'warningList' => array(),
			'infoList' => array(),
			'successList' => array()
		);
		return $statusList;
	}
	
	// This seems to come up in every app. Use of this function on input
	// values allows user to type/paste in dollar amounts with $ or commas
	// which we can then process as plain numbers.
	public static function scrubToValidAmount($amount)
	{
		if ($amount !== null) {
			$amount = preg_replace("/[^0-9\-\.]/", '', $amount);
		}
		return $amount;
	}
	public function getStatusMessages() {
		return $this->statusMessageList;
	}
	
	protected function addError($msg)
	{
		$this->statusMessageList['errorList'][] = $msg;
	}
	protected function addWarning($msg)
	{
		$this->statusMessageList['warningList'][] = $msg;
	}
	protected function addInfo($msg)
	{
		$this->statusMessageList['infoList'][] = $msg;
	}
	protected function addSuccess($msg)
	{
		$this->statusMessageList['successList'][] = $msg;
	}
	
	protected function haveErrors()
	{
		if (count($this->statusMessageList['errorList']) > 0) {
			return true;
		} else {
			return false;
		}
	
	}
}