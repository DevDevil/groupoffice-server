<?php

namespace Intermesh\Modules\Email\Model;

use Exception;
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
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
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

		if (!isset(self::$_connection[$this->host . ':' . $this->username])) {
			self::$_connection[$this->host . ':' . $this->username] = new Connection(
					$this->host, $this->port, $this->username, $this->password, $this->encrytion == 'ssl', $this->encrytion == 'tls', 'plain');
		}

		if (!self::$_connection[$this->host . ':' . $this->username]->isAuthenticated() && !self::$_connection[$this->host . ':' . $this->username]->authenticate()) {
			throw new Exception("Could not authenticate to host " . $this->host);
		}

		return self::$_connection[$this->host . ':' . $this->username];
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

	/**
	 * 
	 * @return \Intermesh\Modules\Email\Model\Folder
	 */
	private function _syncMailboxes() {

		$folders = [];
		$mailboxes = $this->getRootMailbox()->getChildren();
		foreach ($mailboxes as $mailbox) {
			$folder = Folder::find(['name' => $mailbox->name, 'accountId' => $this->id])->single();

			if (!$folder) {
				$folder = new Folder();
				$folder->accountId = $this->id;
				$folder->name = $mailbox->name;
			}
			if (!isset($folder->highestModSeq)) {
				$folder->highestModSeq = $mailbox->getHighestModSeq();
			}
			if (!isset($folder->uidValidity)) {
				$folder->uidValidity = $mailbox->getUidValidity();
			} else {
				if ($folder->uidValidity != $mailbox->getUidValidity()) {
					//UID's not valid anymore! Set all uid's to null.					
					App::dbConnection()->getPDO()->query('update emailMessage set imapUid=null, folderId=null where folderId=' . $folder->id);

					App::debug('UID\'s not valid anymore for folder: ' . $folder->name, 'imapsync');
				}

				$folder->uidValidity = $mailbox->getUidValidity();
			}

			$folder->save();

			$folders[] = $folder;

			$this->syncResponse['imapCount'] += $mailbox->getMessagesCount();
		}
		
		foreach($this->folders as $folder){
			if(!in_array($folder->name, $mailboxes)){
				$folder->delete();
			}
		}

		return $folders;
	}

	public function resync() {

		App::dbConnection()->getPDO()->query('delete from emailFolder where accountId=' . intval($this->id));
//		App::dbConnection()->getPDO()->query('delete from emailMessage where accountId='.intval($this->id));
//		App::dbConnection()->getPDO()->query('update emailFolder set highestSyncedUid = null');
	}
	
	public function getMessagesCount(){
		return (int) $this->messages(Query::newInstance()->select('count(*) AS count')->setFetchMode(\PDO::FETCH_COLUMN, 0))->single();		
	}
	
	private $syncResponse;
	private $syncStartTime;

	public function sync() {

		//http://tools.ietf.org/html/rfc4551#section-3.3.1
		//http://tools.ietf.org/html/rfc4549

		App::debugger()->debugTiming("Start sync");

		$this->syncStartTime = time();

		$this->syncResponse = ['stage' => 'new', 'dbCount' => 0, 'imapCount' => 0];

		$folders = $this->_syncMailboxes();

		App::debugger()->debugTiming("Mailboxes synced");

		if($this->_syncGetNew($folders)){
			if($this->_syncUpdate($folders)){
				if($this->_syncDelete($folders)){
					App::debugger()->debugTiming("Start thread index");
					$this->_updateThreads();
					App::debugger()->debugTiming("End thread index");
				}
			}
		}		
		
		$this->syncResponse['dbCount'] = $this->getMessagesCount();

		return $this->syncResponse;
	}

	/**
	 * 
	 * @param Folder[] $folders
	 * @return boolean True if all new messages were fetched
	 * @throws Exception
	 */
	private function _syncGetNew(array $folders) {
		
		$complete = true;

		foreach ($folders as $folder) {

			if (!$folder->getImapMailbox()) {
				continue;
			}			

			$messages = $folder->getMessagesToSync();

			App::debugger()->debugTiming(count($messages) . " messages fetched");


			if (!empty($messages)) {
				

				while ($imapMessage = array_shift($messages)) {
					/* @var $imapMessage \Intermesh\Modules\Email\Imap\Message */

					$message = Message::find(['messageId' => $imapMessage->messageId, 'accountId' => $this->id])->single();
					if (!$message) {
						$message = new Message();
						$message->account = $this;
						$message->setFromImapMessage($imapMessage);
					} else {
						$message->updateFromImapMessage($imapMessage);
					}

					$message->folder = $folder;

					if (!$message->save()) {
						throw new Exception(var_export($message->getValidationErrors(), true));
					}

					if (time() > $this->syncStartTime + 10) {

						//make sure we don't reach the max_execution_time
						return false;
					}
				}
			}
			
			if(!$folder->syncComplete){
				$complete = false;
			}
		}
		
		return $complete;
	}

	private function _syncDelete(array $folders) {

		foreach ($folders as $folder) {
			if (!$folder->getImapMailbox()) {
				continue;
			}

			$dbUids = $folder->getAllUidsFromDb();
			$imapUids = $folder->getImapMailbox()->search();

			$diff = array_diff($dbUids, $imapUids);

			foreach ($diff as $uidToDelete) {
				$message = Message::find(['folderId' => $folder->id, 'imapUid' => $uidToDelete])->single();
				$message->delete();
				
				
				if (time() > $this->syncStartTime + 10) {

					//make sure we don't reach the max_execution_time
					return false;
				}
			}
		}
		
		return true;
	}

	/**
	 * 
	 * @param Folder[] $folders
	 */
	private function _syncUpdate(array $folders) {
		foreach ($folders as $folder) {

			if (!$folder->getImapMailbox()) {
				continue;
			}

			$messages = $folder->getImapMailbox()->getMessagesUnsorted('1:*', ['FLAGS', 'MESSAGE-ID'], $folder->highestModSeq);

			foreach ($messages as $imapMessage) {
				$message = Message::find(['messageId' => $imapMessage->messageId])->single();

				$message->updateFromImapMessage($imapMessage);

				if (!$message->save()) {
					throw new Exception(var_export($message->getValidationErrors(), true));
				}
				
				
				if (time() > $this->syncStartTime + 10) {

					//make sure we don't reach the max_execution_time
					return false;
				}
			}

			//update highest mod sequence
			$folder->highestModSeq = $folder->getImapMailbox()->getHighestModSeq();
			$folder->save();
		}
		
		return true;
	}

	private function findThreadByReferences(Message $message) {
		$refs = $message->getReferences();

		if (!empty($refs)) {

			$q = Query::newInstance()
					->where(['IN', 'messageId', $refs])
					->andWhere(['accountId' => $this->id]);
			return $orgMessage = Message::find($q)->single();
		} else {
			return false;
		}
	}

	private function findThreadBySubject(Message $message) {
		//Attempt to find by subject (poor man's threading)
		if (($pos = strpos($message->subject, ':'))) {
			$orgSubject = trim(substr($message->subject, $pos + 2));

			$addresses = [$message->fromEmail];

			foreach ($message->toAddresses as $address) {
				$addresses[] = $address->email;
			}

			foreach ($message->ccAddresses as $address) {
				$addresses[] = $address->email;
			}

			$q = Query::newInstance()
					->where(['subject' => $orgSubject])
					->andWhere(['accountId' => $this->id])
					->andWhere(['IN', 'toAddresses.email', $addresses])
					->groupBy(['t.id']);

			return Message::find($q)->single();
		} else {
			return false;
		}
	}

	private function _updateThreads() {

		//REFERENCES ON FORWARD???
//		$query = \Intermesh\Core\Db\Query::newInstance()
//				->where([
//					'accountId' => $this->id						
//						])
//				->andWhere('isNull(t.threadId)');
//		App::dbConnection()->getPDO()->query('update emailMessage set threadId=id where `references` IS NULL');
//		App::dbConnection()->getPDO()->query('update emailMessage set threadId=null where `references` IS NOT NULL');		
//		App::dbConnection()->getPDO()->query('update emailMessage m1 inner join emailMessage m2 on(m1.inReplyTo=m2.messageId) set m1.threadId=m2.id where m1.threadId IS NULL');
//		App::dbConnection()->getPDO()->query('update emailMessage set threadId=null, threadDisplay=0');

		$messages = $this->messages(Query::newInstance()->orderBy(['date' => 'ASC'])->where(['threadId' => null]));

		foreach ($messages as $message) {

			$orgMessage = $this->findThreadByReferences($message);

//			if(!$orgMessage){
//				$orgMessage = $this->findThreadBySubject($message);
//			}

			if ($orgMessage) {

				if ($orgMessage->threadId == null) {
					$orgMessage->threadId = $orgMessage->id;
					$orgMessage->save();
				}

				$message->threadId = $orgMessage->threadId;
				$message->save();
			}
		}

		App::dbConnection()->getPDO()->query('update emailMessage set threadId=id where `threadId` IS NULL');
	}

}
