<?php
namespace GO\Core\Install\Controller;

use GO\Core\Controller\AbstractRESTController;
use GO\Core\Install\Model\SystemCheck;

/**
 * Perform system check
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class CheckController extends AbstractRESTController{

   protected function authenticate() {
       return true;
   }

	/**
	 * Run system tests
	 */
	public function httpGet(){

		$systemCheck = new SystemCheck();
		
		return $systemCheck->run();
	}
}