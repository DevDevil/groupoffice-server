<?php

namespace Intermesh\Modules\Email\Model;

use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Email\Imap\Mailbox;

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
		
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('account', Account::className(), 'accountId'),
			$r->hasMany('messages', Message::className(), 'folderId')
		];
	}
	
	/**
	 * Get the IMAP mailbox
	 * 
	 * @return Mailbox
	 */
	public function getImapMailbox(){
		if(!isset($this->imapMailbox)){
			$this->imapMailbox = $this->account->findMailbox($this->name);
		}
		
		return $this->imapMailbox;
	}
	
	public function getHighestSyncedUid(){
		$result = $this->messages(Query::newInstance()->select('max(imapUid) AS highestSyncedUid'))->single();
		
		return (int) $result->highestSyncedUid;	
	}
	
	
	public function getMessagesCount(){
		$result = $this->messages(Query::newInstance()->select('count(*) AS count'))->single();
		
		return (int) $result->count;		
	}
	
	private function getUidsToSync(){

		$highestSyncedUid = $this->getHighestSyncedUid();
		
		$nextUid = $highestSyncedUid + 1;
		
		$nextUids = $this->getImapMailbox()->search("UID ".$nextUid.':*');
		
		if(!$nextUids){
			return [];
		}
		
		//uid search always returns the latest UID
		if(count($nextUids) == 1 && $nextUids[0] == $highestSyncedUid){
			return [];
		}
		
		$slice = array_slice($nextUids, 0, self::$maxSyncMessages);
		
		App::debug($this->name.': '.$highestSyncedUid, 'imapsync');
		
		App::debug($slice, 'imapsync');
		
		return $slice;
	}
	
	/**
	 * 
	 * @return Message[]
	 */
	public function getMessagesToSync(){
		$uids = $this->getUidsToSync();
		

		return $this->getImapMailbox()->getMessagesUnsorted($uids);

	}
	
	
}