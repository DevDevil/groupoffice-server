<?php
namespace GO\Modules\Email\Controller;

use GO\Core\Controller\AbstractCrudController;
use GO\Core\Exception\NotFound;
use GO\Modules\Email\Imap\Mailbox;
use GO\Modules\Email\Model\Account;

class MailboxController extends AbstractCrudController{
	
	/**
	 * Fetch accounts
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($accountId, $orderColumn = 'host', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {

		$account = Account::findByPk($accountId);		

		if (!$account) {
			throw new NotFound();			
		}
		
		$response['results'] = [];
		
		$mailboxes = $account->rootMailbox()->getChildren();
		
		while($mailbox = array_shift($mailboxes)){
			$response['results'][] = $mailbox->toArray();
		}
		
		

		return $this->renderJson($response);
	}

	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($accountId, $mailboxName){
	
		$account = Account::findByPk($accountId);		

		if (!$account) {
			throw new NotFound();			
		}
		
		$mailbox = $account->findMailbox($mailboxName);

		return $this->renderModel($mailbox);
		
	}
}