<?php

namespace controllers;


class Docs extends ControllerPublic {
	
	public function releaseNotes() {
		$this->view->render('release.notes.html');
	}
	
	public function help() {
	    $this->view->render('help.html');
	}
	
	
}