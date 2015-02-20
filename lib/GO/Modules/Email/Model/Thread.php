<?php

namespace GO\Modules\Email\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Query;


/**
 * The Folder model
 *
 * @property int $id
 * @property int $folderId
 * @property string $subject
 * @property string $date
 * 
 * @property boolean $seen
 * @property boolean $answered
 * @property boolean $flagged
 * @property boolean $forwarded
 * 
 * @property Folder $folder
 * @property Message[] $messages
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Thread extends AbstractRecord {	
	
	/**
	 * Set to true if you want to apply changes to all messages in the thread.
	 * 
	 * @var boolean 
	 */
	public $saveToMessages = false;
	
	protected static function defineRelations() {		
		self::belongsTo('account', Account::className(), 'accountId');
		self::belongsTo('folder', Folder::className(), 'folderId');
		self::hasMany('messages', Message::className(), 'threadId')->setQuery(Query::newInstance()->orderBy(['date'=>'DESC']));
	}
	
	/**
	 * Get all addresses that have sent a message in this thread
	 * 
	 * @return Address[]
	 */
	public function getFrom(){

		$q = Query::newInstance()
				->joinRelation('message', false)
				//->select('t.id, t.personal, t.email')
				->where(['message.threadId' => $this->id])
				->groupBy(['t.email']);

		return Address::find($q);
	}
	
	/**
	 * Get the latest message from the thread
	 * 
	 * @return Message
	 */
	private function latestMessage(){
		return $this->messages->single();
	}
	
	/**
	 * Sync thread
	 * 
	 * Set's the subject, answered, date, excerpt properties from the latest 
	 * message. Set's the hasAttachment, seen and flagged value if any of the 
	 * messages in this thread has this property set.
	 * 
	 * @return self|false
	 */
	public function sync(){
		
		$latest = $this->latestMessage();
		
		$this->subject = $latest->subject;
		$this->answered = $latest->answered;
		$this->date = $latest->date;
		
		$latest->getBody();
		
		$this->excerpt = $latest->getExcerpt();		
		
		$q = Query::newInstance()
				->select('max(hasAttachments) AS hasAttachments, min(seen) AS seen, max(flagged) AS flagged')
				->setFetchMode(\PDO::FETCH_ASSOC)
				->where(['threadId'=>$this->id]);
		
		$result = Message::find($q)->single();
		
		$this->setAttributes($result);
		
		return $this->save();
	}
	
	public function save(){
		
		if($this->saveToMessages){
			foreach($this->messages as $message){
				$message->saveToImap = true;
				$message->answered = $this->answered;
				$message->flagged = $this->flagged;
				$message->seen = $this->seen;
				if(!$message->save()){
					throw new \Exception("Could not save message of thread");
				}
			}
		}		
		
		return parent::save();
	}

}