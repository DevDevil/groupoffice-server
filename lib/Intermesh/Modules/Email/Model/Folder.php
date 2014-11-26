<?php

namespace Intermesh\Modules\Email\Model;

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
 * @property string $syncedUntil
 * @property int $highestSyncedUid
 * 
 * @property Account $account
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Folder extends AbstractRecord {
	
	
	public static $maxSyncMessages = 100;
	
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
	
	
	public function getMessagesCount(){
		$result = $this->messages(Query::newInstance()->select('count(*) AS count'))->single();
		
		return $result->count;		
	}
	
	private function getUidsToSync(){
		
		$allUids = $this->getImapMailbox()->search();
		
		if(!$allUids){
			return [];
		}
		
		if(!empty($this->highestSyncedUid)){
			while(!($offset = array_search($this->highestSyncedUid, $allUids)) && $this->highestSyncedUid>0){
				$this->highestSyncedUid--;
			}
		}else
		{
			$offset = 0;
		}
		
		return array_slice($allUids, $offset, self::$maxSyncMessages);
	}
	
	/**
	 * 
	 * @return \Intermesh\Modules\Email\Imap\Message[]
	 */
	public function getMessagesToSync(){
		$uids = $this->getUidsToSync();
		

		return $this->getImapMailbox()->getMessagesUnsorted($uids);

	}
	
	
}