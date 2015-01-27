<?php
namespace GO\Core\Install\Controller;


use GO\Core\Controller\AbstractRESTController;
use GO\Core\Install\Model\System;

/**
 * Perform system update
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class InstallController extends AbstractRESTController {	

	/**
	 * Authenticate the current user
	 * 
	 * Override this for special use cases.
	 * 
	 * @return boolean
	 */
	protected function authenticate(){
		return true;
	}

	/**
	 * Run system tests
	 */
	public function httpGet() {
		
		$system = new System();
		
		$success = $system->install();
	
		return $this->renderJson(['success' => $success]);
	}
}
