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
	public function getMessagesCount(){
		return (int) $this->messages(Query::newInstance()->select('count(*) AS count')->setFetchMode(\PDO::FETCH_COLUMN, 0))->single();		
	}
	
	public $syncComplete = false;
	
	private function uidsToSync(){

		$highestSyncedUid = $this->getHighestSyncedUid();
		
		$nextUid = $highestSyncedUid + 1;
		
		$nextUids = $this->imapMailbox()->search("UID ".$nextUid.':*');
		
		//uid search always returns the latest UID
		if(empty($nextUids) || (count($nextUids) == 1 && $nextUids[0] == $highestSyncedUid)){
			
			
			//do extra check on all uid's
//			$dbUids = $this->allUidsFromDb();
//			$imapUids = $this->imapMailbox()->search();
//
//			$nextUids = array_diff($dbUids, $imapUids);
//			
//			if(empty($nextUids)){
				$this->syncComplete = true;
				return [];
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