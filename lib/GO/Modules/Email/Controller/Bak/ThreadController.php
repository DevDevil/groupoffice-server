<?php
namespace GO\Modules\Email\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Exception\NotFound;
use GO\Modules\Email\Imap\Mailbox;
use GO\Modules\Email\Imap\Message;
use GO\Modules\Email\Model\Account;

class ThreadController extends AbstractCrudController {


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
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($accountId, $mailboxName, $uids, $returnAttributes = [ "uid", "answered", "forwarded", "seen", "size", "date", "from", "subject", "to", "cc", "bcc", "replyTo", "contentType", "messageId", "xPriority", "dispositionNotificationTo", "body", "quote", "attachments"]) {
		
		$uids = explode(',', $uids);
		
		$response['results']=[];

		$account = Account::findByPk($accountId);

		if (!$account) {
			throw new NotFound();
		}

		$mailbox = Mailbox::findByName($account->connect(), 'virtual/'.$mailboxName);
		
		foreach($uids as $uid){

			$message = $mailbox->getMessage($uid, false, $this->_returnAttributesToImapProps($returnAttributes));

			$data = $this->renderModel($message, $returnAttributes);
			$data = $this->_setAttachmentUrls($message, $data);
			
			$response['results'][]=$data['data'];
		}
		
		return $this->renderJson($response);
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
		
		
		if(isset($json['data']['attributes']['quote'])){
			preg_match_all('/cid:([^"\' ]+)/',$json['data']['attributes']['quote'], $matches);
			
			foreach($matches[1] as $cid){
				$part = $message->getStructure()->findParts(['id' => $cid]);
				
				if(isset($part[0])) {
					$url = App::router()->buildUrl($this->router->route.'/attachments/'.$part[0]->partNumber);
					
					$json['data']['attributes']['quote'] = str_replace('cid:'.$cid, $url, $json['data']['attributes']['quote']);
				}
			}
		}
		
		return $json;
	}
}