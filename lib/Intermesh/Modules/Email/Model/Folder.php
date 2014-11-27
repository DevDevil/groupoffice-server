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
 * @property string $syncedUntil
 * @property int $highestSyncedUid
 * 
 * @property Account $account
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
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
	
	public function getHighestSyncedUid(){
		$result = $this->messages(Query::newInstance()->select('min(imapUid) AS highestSyncedUid'))->single();
		
		
		
		return (int) $result->highestSyncedUid;	
	}
	
	
	public function getMessagesCount(){
		$result = $this->messages(Query::newInstance()->select('count(*) AS count'))->single();
		
		return (int) $result->count;		
	}
	
	private function getUidsToSync(){
		
		$allUids = $this->getImapMailbox()->search();
		
		if(!$allUids){
			return [];
		}
		
//		echo $this->highestSyncedUid."\n";
		
		$highestSyncedUid = $this->getHighestSyncedUid();
		
		
		if(!empty($highestSyncedUid)){
			while(($offset = array_search($highestSyncedUid, $allUids)) === false && $highestSyncedUid>0){
				$highestSyncedUid++;
			}
			if($offset !== false){
//				$offset--;
				if($offset === 0){
					return [];
				}
			}else
			{
				$offset = count($allUids);
			}
		}else
		{
			$offset = count($allUids);
		}
		
		App::debug("Highest uid ".$this->name.": ".$highestSyncedUid.' : '.$offset);
		
		$max = $offset > self::$maxSyncMessages ? self::$maxSyncMessages : $offset;
		
		$slice = array_slice($allUids, $offset-$max, $max);
		
		App::debug($slice);
		
		
		return $slice;
	}
	
	/**
	 * 
	 * @return Message[]
	 */
	public function getMessagesToSync(){
		$uids = $this->getUidsToSync();
		

		return array_reverse($this->getImapMailbox()->getMessagesUnsorted($uids));

	}
	
	
}