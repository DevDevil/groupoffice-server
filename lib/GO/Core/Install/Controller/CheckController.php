<?php
namespace GO\Core\Install\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractRESTController;
use PDOException;

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

		$systemCheck = new \GO\Core\Install\Model\SystemCheck();
		
		return $systemCheck->run();
	}

	private function _check($testName, $function){
		$html = '<p>'.$testName.': <span style="';

		$result = $function();

		if($result===true){
			$html .= 'color:green">OK';
		}else
		{
			$html .= 'color:red">ERROR: '.$result;
		}

		$html .= '</span></p>';

		echo $html;
	}
}