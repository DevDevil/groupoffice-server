<?php
namespace Intermesh\Modules\Email\Model;

use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Util\String;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Modules\Email\Imap\Message as ImapMessage;
use Intermesh\Modules\Email\Model\Account;
use Intermesh\Modules\Email\Model\Attachment;
use Intermesh\Modules\Email\Model\CcAddress;
use Intermesh\Modules\Email\Model\ToAddress;
use Intermesh\Modules\Timeline\Model\Item;


//use MimeMailParser\Parser;

/**
 * The Message model
 *
 * @property int $id
 * @property int $ownerUserId
 * @propery int $threadId Each messaqe thread get's a unique thread id. This is the ID of the first message in the thread
 * @property boolean $threadDisplay The most actual message of the thread should be displayed. So IMAP sync will set this to true on the most actual message.
 * @property User $owner
 * @property string $date
 * @property string $subject
 * @property array $fromPersonal
 * @property array $fromEmail
 * @property ToAddress[] $to
 * @property CcAddress[] $cc
 * @property string $body
 * @property string $contentType
 * @property string $messageId
 * @property int $imapUid
 * @property Folder $folder;
 *
 * 
 * @property Item[] $timelineItems
 * @property Attachment[] $attachments
 * @property Message[] $references
 * @property Account $account
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Message extends AbstractRecord {
	
	private $_inlineAttachments;
	
	protected static function defineRelations(RelationFactory $r) {
		return [
//			$r->hasMany('timelineItems', Item::className(), 'imapMessageId', 'threadId'),
			$r->belongsTo('owner', User::className(), 'ownerUserId'),
			$r->belongsTo('account', Account::className(), 'accountId'),
			$r->belongsTo('folder', Folder::className(), 'folderId'),
			$r->hasMany('attachments', Attachment::className(), 'messageId'),
			$r->hasMany('toAddresses', ToAddress::className(), 'messageId'),
			$r->hasMany('ccAddresses', CcAddress::className(), 'messageId'),
			$r->hasMany('threadMessages', Message::className(), 'threadId', 'threadId'),
		];
	}
	
//	public function save() {
//		if(parent::save()){
//			
//			if($this->threadId === null){
//				$this->threadId = $this->id;
//				
//				$this->dbUpdate();
//			}
//			
//			return true;
//		}else
//		{
//			return false;
//		}
//	}
	
	
	
	private function getInlineAttachments(){
		if(!isset($this->_inlineAttachments)){
			$this->_inlineAttachments = $this->attachments(['AND','!=', ['contentId' => null]])->all();
		}
		
		return $this->_inlineAttachments;
	}
	
	private function loadBodyFromImap(){
		
		if(!isset($this->_body)){
			$imapMessage = $this->getImapMessage();
			if(!$imapMessage){
				throw new Exception("Could not get IMAP message");
			}
			$this->_body = $imapMessage->getBody();
			
			
			$this->_quote = $imapMessage->getQuote();
			$this->save();
		}	
	}
	
	/**
	 * Get the URL to the full MIME source
	 * 
	 * @return string
	 */
	public function getSourceUrl(){
		return App::router()->buildUrl('email/accounts/'.$this->accountId.'/mailboxes/'.base64_encode($this->folder->name).'/messages/'.$this->imapUid);
	}
	
	/**
	 * Get URL to sync with IMAP
	 * 
	 * @return string
	 */
	public function getSyncUrl(){
		return App::router()->buildUrl('email/sync/'.$this->accountId, ['messageId' => $this->id]);
	}
	
	/**
	 * Get's the body in HTML. Quoted replies are stripped.
	 * 
	 * @return string
	 */
	public function getBody(){		
		
		$this->loadBodyFromImap();
		
		$html = $this->_body;

		foreach($this->getInlineAttachments() as $attachment){
			$html = str_replace('cid:'.trim($attachment->contentId,'<>'), $attachment->getUrl(), $html, $count);
			$attachment->foundInBody = true;
			$attachment->save();
		}
		return $html;
	}
	
	/**
	 * Get's the quoted text if found
	 * 
	 * @return string
	 */
	public function getQuote(){
		$html = $this->_quote;

		foreach($this->getInlineAttachments() as $attachment){
			$html = str_replace('cid:'.trim($attachment->contentId,'<>'), $attachment->getUrl(), $html, $count);
			if($count && !$attachment->foundInBody){
				$attachment->foundInBody = true;
				$attachment->save();
			}
		}
		return $html;
	}
	
//	public function checkReadPermission(){
//		$timelineItems = $this->timelineItems;
//		
//		foreach($timelineItems as $item){
//		
//			if($item->contact->checkPermission('readAccess')){
//				return true;
//			}
//		}
//		
//		return false;
//	}
	
	/**
	 * Checks if the current user is the sender by matching the from email address.
	 * 
	 * @return boolean
	 */
	public function getIsSentByCurrentUser(){
		return User::current()->contact->emailAddresses(Query::newInstance()->where(['email' => $this->fromEmail]))->single() != false;
	}

	
	/**
	 * Get's a small part in plain text of the body
	 * 
	 * @param int $length
	 * @return string
	 */
	public function getExcerpt($length = 70){
		
		if(isset($this->_body)){
			$text = str_replace('>','> ', $this->getBody());
			
			
			$text = strip_tags($text);		
			
			$text = html_entity_decode($text);			
			
			$text = trim(preg_replace('/[\s]+/u',' ', $text));			
			$text = String::cutString($text, 0, $length);			
		}else
		{
			$text = null;
		}
		
		return $text;
		
	}
	
//	public function getCurrentUserIsAuthor(){
//		
//		
//			//var_dump($addresses);
//		
//			$contact = User::current()->contact;
//
//			$email = $contact->emailAddresses(['email' => $this->from['address']])->single();
//			
//			return $email !== false;		
//		
//		
//		
//	}
	
//	private static function collectEmailAddresses(Parser $parser){
//		
//		$emailAddresses = [];
//		
//		$headers = ['to','cc','from'];
//		
//		foreach($headers as $header){
//		
//			$headerValue = $parser->getHeader($header);
//			if(!empty($headerValue)){
//				$a = mailparse_rfc822_parse_addresses($headerValue);
//
//				foreach($a as $address){
//					$emailAddresses[] = $address['address'];
//				}
//			}
//		}
//		
//		return $emailAddresses;
//		
//	}
	
//	private static function findContacts(Parser $parser, $ownerUserId){
//		$emailAddresses = self::collectEmailAddresses($parser);	
//		
//		
//		$query = Query::newInstance()
//				->joinRelation('emailAddresses')
//				->groupBy(['t.id'])
//				->where(['IN', 'emailAddresses.email', $emailAddresses]);
//				
//				
//		return Contact::findPermitted($query, 'readAccess', $ownerUserId);
//		
//	}
	
	private $_imapMessage;
	
	/**
	 * Get the IMAP message
	 * 
	
	 * @return ImapMessage | boolean
	 */
	public function getImapMessage($noFetchProps=false){
		
		if(!isset($this->_imapMessage)){
			$mailbox = $this->account->findMailbox($this->folder->name);		

			if(!$mailbox){
				throw new Exception("Mailbox ".$this->folder->name." doesn't exist anymore!");
			}

			$m = $mailbox->getMessage($this->imapUid, $noFetchProps);
			
			if(!$noFetchProps){
				$this->_imapMessage = $m;
			}  else {
				return $m;
			}
		}
		
		return $this->_imapMessage;
	}
	
	/**
	 * Updates the flags from the IMAP message
	 * 
	 * @param ImapMessage $imapMessage
	 */
	public function updateFromImapMessage(ImapMessage $imapMessage){

		$this->imapUid = $imapMessage->uid;
		$this->answered = $imapMessage->getAnswered();
		$this->forwarded = $imapMessage->getForwarded();
		$this->seen = $imapMessage->getSeen();
	}
	
	/**
	 * Syncs all attributes with the IMAP message
	 * 
	 */
	public function sync(){
		$imapMessage = $this->getImapMessage();
		$this->setFromImapMessage($imapMessage);
		$this->save();
		
		return true;
		
//		$this->getBody();
	}
	
	
	public function getThreadFrom(){
		$messages = $this->threadMessages(Query::newInstance()->select('t.fromPersonal, t.fromEmail')->groupBy(['fromEmail']))->all();
		
//		$total = count($messages);
//		
//		$max = 3;
		
		$str = '';
		
		foreach($messages as $message){
			$names = !empty($message->fromPersonal) ? $message->fromPersonal : $message->fromEmail;
			
			if($message->fromEmail == $this->account->fromEmail) {
				$name = 'me';
			}else
			{
				$parts = explode(' ', $names);
				$name = array_shift($parts);
			}
			
			$str .= $name.', ';
		}
		
		return rtrim($str, ' ,');
	}
	
	/**
	 * Set's all attributes from an IMAP message
	 * 
	 * @param ImapMessage $imapMessage
	 */
	public function setFromImapMessage(ImapMessage $imapMessage){
		
		
		$this->date = $imapMessage->date;
		$this->subject = $imapMessage->subject;
		$this->fromPersonal = $imapMessage->from->personal;
		$this->fromEmail = $imapMessage->from->email;
		
		if(!$this->getIsNew()){
			foreach($this->toAddresses as $a){
				$a->delete();
			}
			
			foreach($this->ccAddresses as $a){
				$a->delete();
			}
		}
		
		if(isset($imapMessage->to)) {
			$toAddresses=[];
			foreach($imapMessage->to as $to){
				$toAddresses[] = ToAddress::newInstance()->setAttributes(['personal'=>$to->personal, 'email'=>$to->email]);
			}
			$this->toAddresses = $toAddresses;
		}
		
		
		if(isset($imapMessage->cc)) {
			$ccAddresses=[];
			foreach($imapMessage->cc as $to){
				$ccAddresses[] = CcAddress::newInstance()->setAttributes(['personal'=>$to->personal, 'email'=>$to->email]);
			}
			$this->ccAddresses = $ccAddresses;
		}

		$this->messageId = $imapMessage->messageId;
		$this->inReplyTo = $imapMessage->inReplyTo;
		
		if(!empty($imapMessage->references)){
			$this->references = str_replace(' ',',',$imapMessage->references);
		}
		
		$this->imapUid = $imapMessage->uid;
//		$this->imapMailbox = (string) $imapMessage->mailbox;
		
//		$threadId = self::findThreadId($imapMessage->inReplyTo);
		
		//When null is set then the threadId automatically sets to $this->id in the save() function
//		$this->threadId = $threadId;

		$this->_body = null; // $imapMessage->getBody();
		$this->_quote = null; //$imapMessage->getQuote();
		
		$this->seen = $imapMessage->getSeen();
		$this->answered = $imapMessage->getAnswered();
		$this->forwarded = $imapMessage->getForwarded();
		
		

		$imapAttachments = $imapMessage->getAttachments();
		
		$attachments = [];

		foreach ($imapAttachments as $attachment) {
			
			if($this->getIsNew() || !($a = Attachment::find(['messageId' => $this->id, 'imapPartNumber' => $attachment->partNumber])->single())){
				$a = new Attachment();
				$a->imapPartNumber = $attachment->partNumber;
				$a->message = $this;				
			}
			$a->filename = $attachment->getFilename();

			if(empty($a->filename)){
				$a->filename = 'unnamed';
			}

			$a->contentType = $attachment->type.'/'.$attachment->subtype;
			//$a->inline = $attachment->disposition == 'inline';				
			$a->contentId = $attachment->id;

			if(empty($a->contentId) ){
				$this->hasAttachments = true;
			}

			$attachments[] = $a;			
			
		}
		
		$this->attachments = $attachments;
		
//		$this->hasAttachments = count($attachments);
	}
	
	public function getReferences(){
		$refs = empty($this->references) ? [] : explode(',', $this->references);
		
		return array_map('trim', $refs);
	}
	
	
//	public static function createFromMime($mimeStr, $ownerUserId){
//		
//		
//		$parser = new Parser();
//		$parser->setText($mimeStr);
//		
//		$threadId = self::findThreadId($parser);
//		
//		if(!$threadId){
//			$contacts = self::findContacts($parser, $ownerUserId);
//
//			if(!$contacts->getRowCount()){
//
//				echo 'No contacts';
//				return false;
//			}
//		}
//		
//		
//		
//		$messageId = $parser->getHeader('message-id');
//		
//		$message = Message::find(['messageId' => $messageId])->single();
//		if(!$message){
//			$message = new Message();
//		}
//		
//		$message->ownerUserId = $ownerUserId;
//		
//		$date = $parser->getHeader('date');
//		
//		$dateTime = new DateTime($date);
//		$dateTime->setTimezone(new DateTimeZone("Etc/GMT"));
//		
//		$message->date = $dateTime->format('Y-m-d H:i');
//		$message->subject = self::mimeHeaderDecode($parser->getHeader('subject'));
//		
//		$message->to = self::mimeHeaderDecode($parser->getHeader('to'));
//		$message->cc = self::mimeHeaderDecode($parser->getHeader('cc'));
//		$message->from = self::mimeHeaderDecode($parser->getHeader('from'));
//		$message->messageId = $messageId;
//		
//		$message->threadId = $threadId;
//		
//		$html = $parser->getMessageBody('html');
//		
//		if($html){
//			$message->body = String::sanitizeHtml($html);
//		}else
//		{
//			$message->body = $parser->getMessageBody('text');
//			$message->contentType = 'text/plain';
//		}
//		if(!$message->save()){
//			var_dump($message->getValidationErrors());
//			return false;
//		}
//		
//		if(!$threadId) {
//			foreach($contacts as $contact){
//				if(!Item::find(['contactId' => $contact->id, 'imapMessageId' => $message->id])->single()){
//					$timeLineItem = new Item();
//					$timeLineItem->contactId = $contact->id;
//					$timeLineItem->imapMessageId = $message->id;
//					$timeLineItem->createdAt = $message->date;
//					if(!$timeLineItem->save()){
//						var_dump($timeLineItem->getValidationErrors());
//					}			
//				}
//			}
//		}
//		
//		
////		exit();
//		$attachments = $parser->getAttachments();
////		
////		// Write attachments to disk
//		foreach ($attachments as $attachment) {
//			
//			if(!Attachment::find(['messageId' => $message->id, 'filename' => $attachment->getFilename()])->single()){
//				$a = new Attachment();
//				$a->message = $message;
//				$a->filename = $attachment->getFilename();
//				$a->contentType = $attachment->content_type;
//				$a->inline = $attachment->content_disposition == 'inline';
//				
//				$headers = $attachment->getHeaders();
//				
//				if(isset($headers['content-id'])){
//					$a->contentId = $headers['content-id'];
//				}
//
//
//				$file = \Intermesh\Core\Fs\File::tempFile();
//				$attachment->saveAttachment($file->getFolder()->getPath(), $file->getName());
//
//				$a->setFile($file);
//
//				$a->save();
//			}
//
//		}
//		
//		
//		return $message;
//
//	}
//	
//	
//	
//	public static function mimeHeaderDecode($string, $defaultCharset='UTF-8') {
//		/*
//		 * (=?ISO-8859-1?Q?a?= =?ISO-8859-1?Q?b?=)     (ab)
//		 *  White space between adjacent 'encoded-word's is not displayed.
//		 *
//		 *  http://www.faqs.org/rfcs/rfc2047.html
//		 */
//		$string = preg_replace("/\?=[\s]*=\?/","?==?", $string);
//
//		if (preg_match_all("/(=\?[^\?]+\?(q|b)\?[^\?]+\?=)/i", $string, $matches)) {
//			foreach ($matches[1] as $v) {
//				$fld = substr($v, 2, -2);
//				$charset = strtolower(substr($fld, 0, strpos($fld, '?')));
//				$fld = substr($fld, (strlen($charset) + 1));
//				$encoding = $fld{0};
//				$fld = substr($fld, (strpos($fld, '?') + 1));
//				$fld = str_replace('_', '=20', $fld);
//				if (strtoupper($encoding) == 'B') {
//					$fld = base64_decode($fld);
//				}
//				elseif (strtoupper($encoding) == 'Q') {
//					$fld = quoted_printable_decode($fld);
//				}
//				$fld = String::cleanUtf8($fld, $charset);
//
//				$string = str_replace($v, $fld, $string);
//			}
//		}	elseif(($pos = strpos($string, "''")) && $pos < 64){ //check pos for not being to great
//			//eg. iso-8859-1''%66%6F%73%73%2D%69%74%2D%73%6D%61%6C%6C%2E%67%69%66
//			$charset = substr($string,0, $pos);
//			
////			throw new \Exception($charset.' : '.substr($string, $pos+2));
//			$string = rawurldecode(substr($string, $pos+2));
//
//			$string = String::cleanUtf8($string, $charset);
//		}else
//		{			
//			$string = String::cleanUtf8($string, $defaultCharset);
//		}
//		
//		return str_replace(array('\\\\', '\\(', '\\)'), array('\\','(', ')'), $string);
//	}
}

