<?php

namespace controllers;
use NCSC\Constants;

/**
 * Parent controller for all controllers that do not require authentication.
 *
 * @package		controllers
 */
class ControllerPublic extends ControllerBase {

	
    /**
     * initialize controller
     *
     * @return void
     */
    public function __construct() {
		parent::__construct();
    }
}
