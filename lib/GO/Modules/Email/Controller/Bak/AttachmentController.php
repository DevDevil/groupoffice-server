<?php
namespace GO\Modules\Email\Controller;

use GO\Core\Controller\AbstractCrudController;
use GO\Core\Exception\NotFound;
use GO\Modules\Email\Imap\Mailbox;
use GO\Modules\Email\Model\Account;

class AttachmentController extends AbstractCrudController {



	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($accountId, $mailboxName, $uid, $partNumber) {
		
		//cache forever
		$this->cacheHeaders(null, $accountId.'-'.$mailboxName.'-'.$uid.'-'.$partNumber);
		
		header('Expires: '.date('D, d M Y H:i:s', time()+86400*30)); //30 days

		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$mailbox = Mailbox::findByName($account->connect(), $mailboxName);

		$message = $mailbox->getMessage($uid);
		
		$part = $message->getStructure()->getPart($partNumber);

		$part->output();
	}
}