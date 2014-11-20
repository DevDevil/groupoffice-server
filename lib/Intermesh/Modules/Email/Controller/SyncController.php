<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Modules\Email\Model\Account;



/**
 * The controller for accounts. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class SyncController extends AbstractRESTController {
	protected function httpGet($accountId, $messageId=null){
		
		if(isset($messageId)){
			$message = \Intermesh\Modules\Email\Model\Message::findByPk($messageId);
			$message->sync();
		}else
		{
			$account = Account::findByPk($accountId);

			$account->sync();
		}
		
		return $this->renderJson();
	}
}