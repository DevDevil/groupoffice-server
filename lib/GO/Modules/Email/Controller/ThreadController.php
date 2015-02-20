<?php
namespace GO\Modules\Email\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\Email\Model\Message;
use GO\Modules\Email\Model\Thread;

class ThreadController extends AbstractController {



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
	protected function actionStore($accountId, $folderId, $orderColumn = 'date', $orderDirection = 'DESC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = ['*','from']) {

//		$folder = Folder::find(['accountId' => $accountId, 'name' => 'INBOX'])->single();
		
		$accounts = Thread::find(Query::newInstance()
								->select('t.*, count(*) AS messageCount')
								->orderBy([$orderColumn => $orderDirection])
								->limit($limit)
								->offset($offset)
								->search($searchQuery, array('messages.subject', 'messages._body'))								
								->joinRelation('messages', false)
								->where(['messages.folderId'=>$folderId])
								->groupBy(['t.id'])
				
				
//								->joinRaw('INNER JOIN (SELECT threadId, MAX(`date`) maxDate FROM emailMessage WHERE folderId=:folderId GROUP BY threadId) m ON t.threadId=m.threadId AND t.`date` = m.maxDate')
//								->addBindParameter(':folderId', $folderId)
//								->groupBy(['threadId'])
		);

		$store = new Store($accounts);
		$store->setReturnAttributes($returnAttributes);
		$store->format('messageCount', function($model){return intval($model->messageCount);});

		return $this->renderStore($store);
	}
	
	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($threadId, $limit = 10, $offset = 0, $returnAttributes = ["*", "sourceUrl", "syncUrl", "body", "quote", "attachments", "to", "cc", "from"]) {
		
		$accounts = Message::find(Query::newInstance()
								->orderBy(['date' => 'DESC'])
								->limit($limit)
								->offset($offset)
								->where(['threadId' => $threadId])							
				);
		
		//$message = $accounts->single();		
		//var_dump($message->toArray());
								

		$store = new Store($accounts);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	
	
	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $threadId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($threadId, $returnAttributes = []) {

		$thread = Thread::findByPk($threadId);		

		if (!$thread) {
			throw new NotFound();
		}

		$thread->setAttributes(App::request()->payload['data']);
		$thread->save();
		
		return $this->renderModel($thread, $returnAttributes);
	}
	
}