<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Criteria;
use Intermesh\Core\Db\Query;
use Intermesh\Modules\Email\Model\Folder;
use Intermesh\Modules\Email\Model\Message;

class ThreadController extends AbstractCrudController {



	/**
	 * Fetch accounts
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($accountId, $folderId, $orderColumn = 'date', $orderDirection = 'DESC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = ['id', 'threadId', 'threadFrom', 'subject','excerpt','date', 'seen','answered','hasAttachments','forwarded']) {

//		$folder = Folder::find(['accountId' => $accountId, 'name' => 'INBOX'])->single();
		
		$accounts = Message::find(Query::newInstance()
								->orderBy([$orderColumn => $orderDirection])
								->limit($limit)
								->offset($offset)
								->search($searchQuery, array('t.subject', 't._body'))								
								->where(['folderId'=>$folderId])
								->joinRaw('INNER JOIN (SELECT threadId, MAX(`date`) maxDate FROM emailMessage WHERE folderId=:folderId GROUP BY threadId) m ON t.threadId=m.threadId AND t.`date` = m.maxDate')
								->addBindParameter(':folderId', $folderId)
								->groupBy(['threadId'])
		);

		$store = new Store($accounts);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($threadId, $limit = 10, $offset = 0, $returnAttributes = ["*", "sourceUrl", "syncUrl", "isSentByCurrentUser", "body", "quote", "attachments", "toAddresses", "ccAddresses"]) {
		
		$accounts = Message::find(Query::newInstance()
								->orderBy(['date' => 'DESC'])
								->limit($limit)
								->offset($offset)
								->where(['threadId' => $threadId])
								
								
				);
								

		$store = new Store($accounts);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
}