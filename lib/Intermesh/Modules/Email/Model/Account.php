<?php

namespace Intermesh\Modules\Email\Model;

use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Modules\Email\Imap\Connection;
use Intermesh\Modules\Email\Imap\Mailbox;

/**
 * The Account model
 *
 * @property int $id
 * @property int $ownerUserId
 * @property User $owner
 * @property string $createdAt
 * @property string $modifiedAt
 * @property string $host
 * @property int $port
 * @property string $encrytion
 * @property string $username
 * @property string $password
 * @propery string $syncedUntil
 * 
 * @property Folder $folders
 * @property Message $messages
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Account extends AbstractRecord {

	private static $_connection = [];
	private $_rootMailbox;

	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('owner', User::className(), 'ownerUserId'),
			$r->hasMany('folders', Folder::className(), 'accountId'),
			$r->hasMany('messages', Message::className(), 'accountId')
		];
	}

	/**
	 * Get the IMAP connection
	 * 
	 * @return Connection
	 */
	public function getConnection() {

		if (!isset(self::$_connection[$this->host.':'.$this->username])) {
			self::$_connection[$this->host.':'.$this->username] = new Connection(
					$this->host, $this->port, $this->username, $this->password, $this->encrytion == 'ssl', $this->encrytion == 'tls', 'plain');
		}

		return self::$_connection[$this->host.':'.$this->username];
	}

	/**
	 * Get the root mailbox
	 * 
	 * @return Mailbox
	 */
	public function getRootMailbox() {

		if (!isset($this->_rootMailbox)) {
			$this->_rootMailbox = new Mailbox($this->getConnection());
		}
		return $this->_rootMailbox;
	}

	/**
	 * Finds a mailbox by name
	 * 
	 * 
	 * @param string $mailboxName
	 * @param string $reference
	 * @return Mailbox|boolean
	 */
	public function findMailbox($mailboxName, $reference = "") {
		return Mailbox::findByName($this->getConnection(), $mailboxName, $reference);
	}
	
	private function _syncMailboxes(){
		$mailboxes = $this->getRootMailbox()->getChildren();
		foreach($mailboxes as $mailbox) {
			$folder = Folder::find(['name' => $mailbox->name, 'accountId' => $this->id])->single();
			
			if(!$folder){
				$folder = new Folder();
				$folder->accountId = $this->id;
				$folder->name = $mailbox->name;
				$folder->save();
			}
		}
	}
	
	public function resync(){
		
		App::dbConnection()->getPDO()->query('delete from emailFolder where accountId='.intval($this->id));
//		App::dbConnection()->getPDO()->query('delete from emailMessage where accountId='.intval($this->id));
		App::dbConnection()->getPDO()->query('update emailFolder set highestSyncedUid = null');
	}

	public function sync() {
		
		
//		$this->_updateThreads();
//		return;
//		
		
//		$date = new DateTime();
//			$interval = new DateInterval('P7D');
//			$interval->invert = true;
//			$date->add($interval);
//	
//			$this->syncedAt = $date->format('Y-m-d H:i:s');
		
		
		
		
		//TODO changed since:
		//http://tools.ietf.org/html/rfc4551#section-3.3.1
		
		$this->_syncMailboxes();
		
		$folders = $this->folders;

		
		$totalDbCount = 0;
		$totalImapCount = 0;
		$response = [];
		
		$startTime = time();
			

//		$mailboxes = ['INBOX', 'Sent'];

		foreach ($folders as $folder) {
			
			

//			$mailbox = $this->findMailbox($folder->name);
			
//			echo "----- ".$folder->name."<br />";
			
			
			if(!$folder->getImapMailbox()){
				continue;
			}
			
//			if(!$mailbox){
//				
//				echo $mailbox ." not found<br />";
//				continue;
//			}
			
			
			$messages = $folder->getMessagesToSync();
			
			
//			$messages = $mailbox->getMessages('ARRIVAL', false, false, 100, 0, $folder->getSyncFilter());

			while ($imapMessage = array_shift($messages)) {		
				/* @var $imapMessage \Intermesh\Modules\Email\Imap\Message */
				
				//Skip already synced messages. IMAP can only limit search results to a whole day.
//				if($imapMessage->internaldate >= $folder->syncedUntil) {
				
//					$message = Message::find(['messageId' => $imapMessage->messageId])->single();
//					if (!$message) {
						$message = new Message();
						$message->account = $this;
						$message->setFromImapMessage($imapMessage);
//					}else
//					{
	//					$message->updateFromImapMessage($imapMessage);
//						$message->setFromImapMessage($imapMessage);
						
//						echo "already synced ".$imapMessage->internaldate.'<br />';
						
//					}

					$message->folder = $folder;


					if(!$message->save()){
						var_dump($message);
						var_dump($message->getValidationErrors());
						exit();
					}

					$folder->highestSyncedUid = $imapMessage->uid;
					
//					echo "New synced until ".$imapMessage->uid.'<br />';
					
					$folder->save();
					
					if(time() > $startTime + 25){
						
						//make sure we don't reach the max_execution_time
						break;
					}
//				}
			}
			
			$res = ['mailbox' => $folder->name, 'dbCount'=>$folder->getMessagesCount(), 'imapCount'=>$folder->getImapMailbox()->getMessagesCount()];
			
			$totalDbCount += $res['dbCount'];
			$totalImapCount += $res['imapCount'];
			
			$response[] = $res;
		}
		
//		ksort($sorted);		
		
//		while($imapMessage = array_shift($sorted)) {
////			\Intermesh\Core\App::debug($imapMessage->uid.' : '.$imapMessage->messageId, 'imapsync');
//
//				
//		}
		
		if($totalDbCount >= $totalImapCount){
			$this->_updateThreads();			
		}
		
		return ['dbCount'=>$totalDbCount, 'imapCount' => $totalImapCount, $response];
	}
	
	
	private function findThreadByReferences(Message $message){
		$refs = $message->getReferences();		
			
		if(!empty($refs)){

			$q = Query::newInstance()->where(['IN', 'messageId', $refs]);
			return $orgMessage = Message::find($q)->single();	
		}else
		{
			return false;
		}
	}
	
	private function findThreadBySubject(Message $message){
		//Attempt to find by subject (poor man's threading)
		if(($pos = strpos($message->subject, ':'))){
			$orgSubject = trim(substr($message->subject, $pos+2));
			
			$addresses = [$message->fromEmail];
			
			foreach($message->toAddresses as $address){
				$addresses[] = $address->email;
			}
			
			foreach($message->ccAddresses as $address){
				$addresses[] = $address->email;
			}

			$q = Query::newInstance()
					->where(['subject' => $orgSubject])
					->andWhere(['IN','toAddresses.email', $addresses])
					->groupBy(['t.id']);				

			return Message::find($q)->single();					
		}else
		{
			return false;
		}
	}
	
	private function _updateThreads(){
		
	
		
//		$query = \Intermesh\Core\Db\Query::newInstance()
//				->where([
//					'accountId' => $this->id						
//						])
//				->andWhere('isNull(t.threadId)');
		
		
//		App::dbConnection()->getPDO()->query('update emailMessage set threadId=id where `references` IS NULL');
//		App::dbConnection()->getPDO()->query('update emailMessage set threadId=null where `references` IS NOT NULL');		
//		App::dbConnection()->getPDO()->query('update emailMessage m1 inner join emailMessage m2 on(m1.inReplyTo=m2.messageId) set m1.threadId=m2.id where m1.threadId IS NULL');

		App::dbConnection()->getPDO()->query('update emailMessage set threadId=null, threadDisplay=0');
		
		$messages = $this->messages(Query::newInstance()->orderBy(['date' => 'ASC'])->where(['threadId'=>null]));
		
		foreach($messages as $message){
			
			$orgMessage = $this->findThreadByReferences($message);
			
			if(!$orgMessage){
				$orgMessage = $this->findThreadBySubject($message);
			}

			if($orgMessage){
				
				if($orgMessage->threadId == null){
					$orgMessage->threadId = $orgMessage->id;
					$orgMessage->save();
				}
				
				$message->threadId = $orgMessage->threadId;
				$message->save();
			}
			
		}
		
		//Update threadDisplay to 1 on the most recent messages of threads
		App::dbConnection()->getPDO()->query('UPDATE emailMessage m1 INNER JOIN (SELECT threadId, MAX(`date`) maxDate FROM emailMessage GROUP BY threadId) m2 ON m1.threadId=m2.threadId AND m1.`date` = m2.maxDate set threadDisplay=1');
			
		
	}

}
