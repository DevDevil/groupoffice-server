<?php
namespace Intermesh\Modules\Email\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractCrudController;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Email\Imap\Mailbox;
use Intermesh\Modules\Email\Imap\Message;
use Intermesh\Modules\Email\Model\Account;

class MessageController extends AbstractCrudController {

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
	protected function actionStore($accountId, $mailboxName, $threaded = true, $orderColumn = 'DATE', $orderDirection = 'DESC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = ['uid', 'subject', 'from', 'to', "answered", "forwarded", "seen", 'xPriority', 'date','thread']) {

		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$mailbox = $account->findMailbox($mailboxName);

//		$mailbox->threadSort();
		


		$response['results'] = [];

		$messages = $mailbox->getMessages($orderColumn, $orderDirection == 'DESC',$threaded, $limit, $offset, $this->_returnAttributesToImapProps($returnAttributes), "ALL");

		while ($message = array_shift($messages)) {
			$response['results'][] = $message->toArray($returnAttributes);
		}
		
		return $this->renderJson($response);
	}

	private function _decamelCasify($str) {
		return strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $str));
	}

	private function _returnAttributesToImapProps($returnAttributes) {
		$returnAttributes = array_map([$this, '_decamelCasify'], $returnAttributes);

		if (in_array('ANSWERED', $returnAttributes) || in_array('SEEN', $returnAttributes) || in_array('FORWARDED', $returnAttributes)) {
			$returnAttributes[] = 'FLAGS';
		}
		return $returnAttributes;
	}

	/**
	 * GET a list of accounts or fetch a single account
	 *
	 * 
	 * @param int $accountId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($accountId, $mailboxName, $uid, $returnAttributes = ["uid", "answered", "forwarded", "seen", "size", "date", "from", "subject", "to", "cc", "bcc", "replyTo", "contentType", "messageId", "xPriority", "dispositionNotificationTo", "body", "attachments"]) {

		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$mailbox = Mailbox::findByName($account->getConnection(), $mailboxName);

		$message = $mailbox->getMessage($uid, $this->_returnAttributesToImapProps($returnAttributes));

		$data = $this->renderModel($message, $returnAttributes);
		$data = $this->_setAttachmentUrls($message, $data);
		
		return $data;
	}
	
	private function _setAttachmentUrls(Message $message, $json){
		if(isset($json['data']['attributes']['attachments'])){
			for($i=0, $c = count($json['data']['attributes']['attachments']);$i < $c; $i++) {
				$json['data']['attributes']['attachments'][$i]['attributes']['url'] = 
						App::router()->buildUrl($this->router->route.'/attachments/'.$json['data']['attributes']['attachments'][$i]['attributes']['partNumber']);
			}
		}
		
		if(isset($json['data']['attributes']['body'])){
			preg_match_all('/cid:([^"\' ]+)/',$json['data']['attributes']['body'], $matches);
			
			foreach($matches[1] as $cid){
				$part = $message->getStructure()->findParts(['id' => $cid]);
				
				if(isset($part[0])) {
					$url = App::router()->buildUrl($this->router->route.'/attachments/'.$part[0]->partNumber);
					
					$json['data']['attributes']['body'] = str_replace('cid:'.$cid, $url, $json['data']['attributes']['body']);
				}
			}
		}
		
		return $json;
	}
}