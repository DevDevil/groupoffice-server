<?php
namespace GO\Modules\Email\Controller;

use GO\Core\Controller\AbstractRESTController;
use GO\Modules\Email\Model\Account;



/**
 * The controller for accounts. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class SyncController extends AbstractRESTController {
	protected function httpGet($accountId, $messageId=null, $resync=null){
		
		if(isset($messageId)){
			$message = \GO\Modules\Email\Model\Message::findByPk($messageId);
			$response['success'] = $message->sync();
			
			var_dump($message->getBody());
			
			
		}else
		{
			$account = Account::findByPk($accountId);
			
			if(!empty($resync)){
				$account->resync();
			}

			$response = $account->sync();
		}
		
		return $this->renderJson($response);
	}
}