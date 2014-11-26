<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Email\Model\Message;

class MessageController extends AbstractCrudController {


	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($messageId, $returnAttributes = ["*", "sourceUrl", "syncUrl", "isSentByCurrentUser", "body", "quote", "attachments", "toAddresses", "ccAddresses"]) {
		
		$message = Message::findByPk($messageId);
		
		if (!$message) {
			throw new NotFound();			
		}

		return $this->renderModel($message, $returnAttributes);
	}
	
}