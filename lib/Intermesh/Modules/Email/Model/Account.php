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

	private $_connection;
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

		if (!isset($this->_connection)) {
			$this->_connection = new Connection(
					$this->host, $this->port, $this->username, $this->password, $this->encrytion == 'ssl', $this->encrytion == 'tls', 'plain');
		}

		return $this->_connection;
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

	public function sync() {
		
		
//		$this->_updateThreads();
//		return;
		
		
//		$date = new DateTime();
//			$interval = new DateInterval('P7D');
//			$interval->invert = true;
//			$date->add($interval);
//	
//			$this->syncedAt = $date->format('Y-m-d H:i:s');
		
		
//		App::dbConnection()->getPDO()->query('delete from emailMessage');
//		App::dbConnection()->getPDO()->query('update emailFolder set syncedUntil = "2014-11-10"');
		
		
		
		$this->_syncMailboxes();
		
		$folders = $this->folders;

		
			

//		$mailboxes = ['INBOX', 'Sent'];

		foreach ($folders as $folder) {

			$mailbox = $this->findMailbox($folder->name);
			
			if(!$mailbox){
				
				echo $mailbox ." not found";
				continue;
			}


			$messages = $mailbox->getMessages('ARRIVAL', false, false, 300, 0, $folder->getSyncFilter());

//			if (!$messages) {
//				throw new Exception("Could not fetch messages");
//			}
			while ($imapMessage = array_shift($messages)) {				
				$message = Message::find(['messageId' => $imapMessage->messageId])->single();
				if (!$message) {
					$message = new Message();
					$message->account = $this;
					$message->setFromImapMessage($imapMessage);
				}else
				{
//					$message->updateFromImapMessage($imapMessage);
					$message->setFromImapMessage($imapMessage);
				}
				
				$message->folder = $folder;

				
				if(!$message->save()){
					var_dump($message);
					var_dump($message->getValidationErrors());
					exit();
				}
				
				$folder->syncedUntil = $imapMessage->internaldate;
				$folder->save();
			}
		}
		
//		ksort($sorted);		
		
//		while($imapMessage = array_shift($sorted)) {
////			\Intermesh\Core\App::debug($imapMessage->uid.' : '.$imapMessage->messageId, 'imapsync');
//
//				
//		}
		
		$this->_updateThreads();
	}
	
	private function _updateThreads(){
		
//		$query = \Intermesh\Core\Db\Query::newInstance()
//				->where([
//					'accountId' => $this->id						
//						])
//				->andWhere('isNull(t.threadId)');
		
		
		App::dbConnection()->getPDO()->query('update emailMessage set threadId=id where `references` IS NULL');
		App::dbConnection()->getPDO()->query('update emailMessage set threadId=null where `references` IS NOT NULL');		
//		App::dbConnection()->getPDO()->query('update emailMessage m1 inner join emailMessage m2 on(m1.inReplyTo=m2.messageId) set m1.threadId=m2.id where m1.threadId IS NULL');

		
		$messages = $this->messages(Query::newInstance()->orderBy(['date' => 'ASC'])->where(['threadId'=>null]));
		
		foreach($messages as $message){
			
			$refs = $message->getReferences();
			
			$q = Query::newInstance()->where(['IN', 'messageId', $refs]);
			$orgMessage = Message::find($q)->single();			
			
			

			if($orgMessage){
				
				if($orgMessage->threadId == null){
					$orgMessage->threadId = $orgMessage->id;
					$orgMessage->save();
				}
				
				$message->threadId = $orgMessage->threadId;
				$message->save();
			}
			
		}
		
		App::dbConnection()->getPDO()->query('update emailMessage set threadId=id where threadId IS NULL');
	}

}
