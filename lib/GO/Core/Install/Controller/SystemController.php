<?php
namespace GO\Core\Install\Controller;

use GO\Core\Controller\AbstractController;
use GO\Core\Install\Model\System;
use GO\Core\Install\Model\SystemCheck;

/**
 * Perform system update
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class SystemController extends AbstractController {	

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
	public function actionInstall() {
		
		$system = new System();
		
		$success = $system->install();
	
		return $this->renderJson(['success' => $success]);
	}
	
	/**
	 * Run system tests
	 */
	public function actionCheck(){

		$systemCheck = new SystemCheck();
		
		return $this->renderJson($systemCheck->run());
	}
	
	/**
	 * Run system tests
	 */
	public function actionUpgrade() {
		
		$system = new System();
		
		$system->upgrade();
	
		return $this->renderJson([]);
	}
}
