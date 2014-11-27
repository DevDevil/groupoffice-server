<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Email\Imap\Mailbox;
use Intermesh\Modules\Email\Model\Account;

class ImapMessageController extends AbstractCrudController {

	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param string $mailboxName Is base64_encoded to avoid problems with slashes
	 * 
	 * @return JSON Model data
	 */
	protected function actionRead($accountId, $mailboxName, $uid) {

		//cache for a month
		$this->cacheHeaders(null, "source-".$accountId."-".$mailboxName."-".$uid, new \DateTime('@'.(time()+86400*30)));

		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$mailbox = Mailbox::findByName($account->getConnection(), base64_decode($mailboxName));

		$message = $mailbox->getMessage($uid, true);
//		
//		$arr = $message->toArray();
//		
//		return $this->renderJson($arr);
		
		
		header('Content-Type: text/plain');
		header('Content-Disposition: inline; filename=source.eml');
		
		$fp = fopen('php://output','w');
		
		$message->getSource($fp);
		
//		return $this->renderJson($arr);
	}
	
	
}