<?php

namespace GO\Modules\Email\Model;

use GO\Core\App;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Query;

use GO\Modules\Email\Imap\Mailbox;

/**
 * The Folder model
 *
 * @property int $id
 * @property int $accountId
 * @property string $name

 * @property int $highestSyncedUid
 * @property int $highestModSeq
 * 
 * @property Account $account
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Folder extends AbstractRecord {
	
	
	public static $maxSyncMessages = 500;
	
	private $imapMailbox;
		
	protected static function defineRelations() {	
		self::belongsTo('account', Account::className(), 'accountId');
		self::hasMany('messages', Message::className(), 'folderId');
	}
	
	public function save() {
		
		switch($this->name){
			
			case 'INBOX':
				$this->sortOrder = 0;
				break;
			
			case 'Drafts':
				$this->sortOrder = 1;
				break;
			
			case 'Sent':
				$this->sortOrder = 2;
				break;
			
			case 'Trash':
				$this->sortOrder = 3;
				break;
			
			case 'Spam':
				$this->sortOrder = 4;
				break;
	
		}
		
		return parent::save();
	}
	
	
	/**
	 * Get the IMAP mailbox
	 * 
	 * @return Mailbox
	 */
	public function imapMailbox(){
		if(!isset($this->imapMailbox)){
			$this->imapMailbox = $this->account->findMailbox($this->name);
		}
		
		return $this->imapMailbox;
	}
	
	/**
	 * Get the highest UID present in the database
	 * 
	 * @return int
	 */
	public function getHighestSyncedUid(){
		$result = $this->messages(Query::newInstance()->select('max(imapUid) AS highestSyncedUid'))->single();
		
		return (int) $result->highestSyncedUid;	
	}
	
	/**
	 * 
	 * 
	 * @return int
	 */
	public function messagesCount(){
		return (int) $this->messages(Query::newInstance()->select('count(*) AS count')->setFetchMode(\PDO::FETCH_COLUMN, 0))->single();		
	}
	
	
	public function getUnseenCount(){
		return (int) $this->messages(
					Query::newInstance()
					->select('count(*) AS count')
					->where(['!=',['seen' => true]])
					->setFetchMode(\PDO::FETCH_COLUMN, 0)
				)->single();		
	}
	
	
	public $syncComplete = false;
	
	private function uidsToSync(){
		
		if($this->imapMailbox()->noSelect){
			$this->syncComplete = true;
			return [];
		}

		$highestSyncedUid = $this->getHighestSyncedUid();
		
		$nextUid = $highestSyncedUid + 1;
		
		$nextUids = $this->imapMailbox()->search("UID ".$nextUid.':*');
		
		//uid search always returns the latest UID
		if(empty($nextUids) || (count($nextUids) == 1 && $nextUids[0] == $highestSyncedUid)){
			
			
			//do extra check on all uid's
			
			
			
//			$dbUids = $this->allUidsFromDb();
//			$imapUids = $this->imapMailbox()->search();
//
//			$nextUids = array_diff($imapUids, $dbUids);
////			
//			if(empty($nextUids)){
				$this->syncComplete = true;
				return [];
//			}  else {
//				App::debug("Fetched missing UID's: ".var_export($nextUids, true));
//			}
		}
		
//		sort($nextUids);
		
		if(count($nextUids) > self::$maxSyncMessages){
		
			$slice = array_slice($nextUids, 0, self::$maxSyncMessages);

			return $slice;
			
		}  else {
			$this->syncComplete = true;
			
			return $nextUids;
		}		
		
	}
	
	/**
	 * 
	 * @return Message[]
	 */
	public function messagesToSync(){
		$uids = $this->uidsToSync();		

		return $this->imapMailbox()->getMessagesUnsorted($uids);
	}
	
	/**
	 * Get all IMAP UID's in the database
	 * 
	 * @return array
	 */
	public function allUidsFromDb(){
		return $this->messages(Query::newInstance()->select('imapUid')->setFetchMode(\PDO::FETCH_COLUMN, 0))->all();
	}
	
	
	
	
	
}