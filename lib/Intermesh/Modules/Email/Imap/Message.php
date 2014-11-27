<?php

namespace Intermesh\Modules\Email\Imap;

use DateTime;
use Exception;
use Intermesh\Core\App;
use Intermesh\Core\Db\Column;
use Intermesh\Core\Model;
use Intermesh\Core\Util\String;
use Intermesh\Modules\Email\Util\Recipient;
use Intermesh\Modules\Email\Util\RecipientList;

/**
 * Message object
 * 
 * Represents an IMAP message
 *
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Message extends Model {

	const XPRIORITY_HIGH = 1;
	const XPRIORITY_NORMAL = 3;
	CONST XPRIORITY_LOW = 5;

	/**
	 *
	 * @var Mailbox 
	 */
	public $mailbox;

	/**
	 * UID on IMAP server
	 * 
	 * @var String 
	 */
	public $uid;

	/**
	 * Flags
	 * 
	 * eg. \Seen \Recent $Forwarded
	 * 
	 * @var array 
	 */
	public $flags;

	/**
	 * The date it arrived on the server
	 * 
	 * @var DateTime 
	 */
	public $internaldate;

	/**
	 * Size in bytes
	 * 
	 * @var int 
	 */
	public $size;

	/**
	 * The time from the Date header field.
	 * 
	 * @var DateTime 
	 */
	public $date;

	/**
	 * The from address
	 * 
	 * @var Recipient 
	 */
	public $from;

	/**
	 * The Subject
	 * 
	 * @var string 
	 */
	public $subject;

	/**
	 * The to recipients
	 * 
	 * @var Recipient[] 
	 */
	public $to;

	/**
	 * The cc recipients
	 * 
	 * @var Recipient[] 
	 */
	public $cc;

	/**
	 * The bcc recipients
	 * 
	 * @var Recipient[] 
	 */
	public $bcc;

	/**
	 * The to recipients
	 * 
	 * @var Recipient[] 
	 */
	public $replyTo;

	/**
	 * Content type header
	 * 
	 * eg. text/plain; charset=utf-8
	 * 
	 * @var string 
	 */
	public $contentType;

	/**
	 * Message-ID header
	 * 
	 * eg. <sadasdsad@domain.com>
	 * 
	 * @var string 
	 */
	public $messageId;
	
	/**
	 * List of message ID's
	 * 
	 * @var string 
	 */
	public $references;
	
	/**
	 * In-Reply-To header
	 * 
	 * eg. <sadasdsad@domain.com>
	 * 
	 * @var string 
	 */
	public $inReplyTo;

	/**
	 * Priority header
	 * 
	 * @var string 
	 */
	public $xPriority = self::XPRIORITY_NORMAL;

	/**
	 * Array of thread UIDs,
	 * @var array 
	 */
	public $thread;

//	public $contentTransferEncoding;

	/**
	 * Send a notification to this address
	 * 
	 * @var Recipient 
	 */
	public $dispositionNotificationTo;
	private static $mimeDecodeAttributes = ['subject'];
	
	private $_structure;
	private $_body;
	private $_quote;

	public function __construct(Mailbox $mailbox, $uid) {
		parent::__construct();

		$this->mailbox = $mailbox;
		$this->uid = (int) $uid;
	}

	/**
	 * 
	 * @param Mailbox $mailbox
	 * @param array $response
	 * @return Message
	 */
	public static function createFromImapResponse(Mailbox $mailbox, $propsStr, $headerStr) {

		$attr = array_merge(
				self::_parseFetchResponse($propsStr), self::_parseHeaders($headerStr));

		$message = new Message($mailbox, $attr['uid']);

		unset($attr['uid']);

		foreach ($attr as $prop => $value) {
			if ($message->hasWritableProperty($prop)) {

				if (in_array($prop, self::$mimeDecodeAttributes)) {				
					$value = Utils::mimeHeaderDecode($value);
				}

				if ($prop == 'to' || $prop == 'cc' || $prop == 'bcc') {
					$list = new RecipientList($value);
					$value = $list->toArray();
				}


				if ($prop == 'from' || $prop == 'replyTo' || $prop == 'displayNotificationTo') {
					$list = new RecipientList($value);
					$value = isset($list[0]) ? $list[0] : null;
				}

				$message->$prop = $value;
			}else
			{
//				trigger_error("Undefined property ".$prop." found in IMAP response for fetch message");
			}
		}

		return $message;
	}
	
	private $_bodyStructureStr;
	
	
	protected function setBodyStructureStr($str){
		$this->_bodyStructureStr = $str;
	}
	
	
	protected function getBodyStructureStr() {
		
		//$this->_bodyStructureStr may also have been set by _parseFetchResponse
		if(!isset($this->_bodyStructureStr)){
		
			$conn = $this->mailbox->connection;

			if(!$this->mailbox->selected) {
				$this->mailbox->select();
			}


			$command = "UID FETCH " . $this->uid . " BODYSTRUCTURE";
			$conn->sendCommand($command);
			$response = $conn->getResponse();

			if(!isset($response[0][0])){
				throw new \Exception("No structure returned");
			}

			$this->_bodyStructureStr = $response[0][0];
		}

		return $this->_bodyStructureStr;
		
	}
	
//	private function _extractBodyStructure($line){
//		$startpos = strpos($line, "BODYSTRUCTURE");
//		
//		if($startpos){
//			//We know BODY[ comes next or end. because this is defined in Mailbox::_buildFetchProps
//			$endpos = strpos($line, 'BODY[');
//			
//			if(!$endpos){
//				$endpos = strlen($line)-$startpos;
//			}
//			
//			$this->_bodyStructureStr = substr($line, $startpos, $endpos);
//		}
//	}

	private static function _parseFetchResponse($response) {

		$attr = [];

		$start = strpos($response, 'UID');
		$end = strpos($response, 'BODY'); //match BODY[HEADER.FIELDS or BODYSTRUCTURE because they need different parsing
		
		if(strpos($response, 'BODYSTRUCTURE')){
			$attr['bodyStructureStr'] = $response;
		}

		$line = trim(substr($response, $start, $end - $start - 1));
		

		$arr = str_getcsv($line, ' ');


		for ($i = 0, $c = count($arr); $i < $c; $i++) {
			$name = String::lowerCamelCasify($arr[$i]);
			
			$valueIndex = $i + 1;
//			if(!isset($arr[$valueIndex])){
//				$value = null;
//				
//				var_dump($arr);
//			}else
//			{
				$value = $arr[$valueIndex];
//			}
			
			

			if ($name == 'rfc822.size') {
				$name = 'size';

				$value = intval($value);
			}

			if (substr($value, 0, 1) == '(') {
				$values = [];

				do {

					$i++;

					$values[] = trim($arr[$i], '()');
				} while (substr($arr[$i], -1, 1) != ')' && $i < $c);


				$attr[$name] = $values;
			} else {

				$attr[$name] = $value;

				$i++;
			}
		}

		return $attr;
	}

	private static function _parseHeaders($headers) {

		$attr = [];

		$headers = str_replace("\r", "", trim($headers));
		$headers = preg_replace("/\n[\s]+/", "", $headers);
		
		if(!empty($headers)){

			$lines = explode("\n", $headers);

			foreach ($lines as $line) {
				$parts = explode(':', $line);

				$name = String::lowerCamelCasify(array_shift($parts));

				$attr[$name] = trim(implode(':', $parts));
			}

			if (isset($attr['date'])) {

				//sometimes headers contain some extra stuff between ()
				//
				//Still needed?
				//
				//$message['date']=preg_replace('/\([^\)]*\)/','', $message['date']);

				$attr['date'] = date(Column::DATETIME_API_FORMAT, strtotime($attr['date']));
			}

			if (isset($attr['internaldate'])) {
				$attr['internaldate'] = date(Column::DATETIME_API_FORMAT, strtotime($attr['internaldate']));
			}


			if (isset($attr['xPriority'])) {
				$attr['xPriority'] = intval($attr['xPriority']);
			}
		}

		return $attr;
	}

	/**
	 * Get's the message structure object
	 * 
	 * @return Structure
	 */
	public function getStructure() {

		if (!isset($this->_structure)) {
			$this->_structure = new Structure($this, $this->bodyStructureStr);
		}

		return $this->_structure;
	}

	/**
	 * Get the full MIME source
	 * 
	 * @param resource $filePointer
	 * @return string
	 * @throws Exception
	 */
	public function getSource($filePointer = null){		
		
		if(isset($filePointer)){
			
			if(!is_resource($filePointer)){
				throw new Exception("Invalid file pointer given");
			}
			
			$streamer = new Streamer($filePointer);
		}else
		{
			$streamer = null;
		}
		
		$conn = $this->mailbox->connection;
		

		$command = "UID FETCH " . $this->uid . " BODY.PEEK[HEADER]";
		$conn->sendCommand($command);
		$response = $conn->getResponse($streamer);
		
		$str = $response[0][1];
		
		$command = "UID FETCH " . $this->uid . " BODY.PEEK[TEXT]";
		$conn->sendCommand($command);
		$response = $conn->getResponse($streamer);
		
		$str .= $response[0][1];
		
		return $str;		
	}

	/**
	 * Returns body in HTML
	 * 
	 * @return string
	 */
	public function getBody($asHtml = true) {

		if (!isset($this->_body)) {
			$props = ['type' => 'text', 'subtype' => $asHtml ? 'html' : 'plain'];

			$parts = $this->getStructure()->findParts($props);


			if (empty($parts) && $asHtml) {
				$parts = $this->getStructure()->findParts(['type' => 'text', 'subtype' => 'plain']);
			}

			if (empty($parts)) {
				return false;
			}

			$part = array_shift($parts);

			$this->_body = $part->getDataDecoded();
			$this->_body = String::cleanUtf8($this->_body, isset($part->params['charset']) ? $part->params['charset'] : null);

			$this->_body = String::normalizeCrlf($this->_body, "\n");

			if ($part->subtype == 'plain' && $asHtml) {
				$this->_body = $this->_stripQuote(true);
				$this->_body = String::textToHtml($this->_body);
				$this->_quote = String::textToHtml($this->_quote);
			}
			
			if ($part->subtype == 'html') {
			 	
				$this->_body = String::sanitizeHtml($this->_body);
				$this->_body = String::convertLinks($this->_body);
				$this->_body = $this->_stripQuote();

//				$doc = new DOMDocument();
//				@$doc->loadHTML($this->_body);
//				$this->_body = $doc->saveHTML();

				
			}
			
			
		}
		
		return $this->_body;
	}
	
	
	private function _findQuoteByGreatherThan(){
		$pos = strpos($this->_body, "\n>");
		if($pos){
			App::debug('Stripped quote by greather than','stripquote');
		}
		return $pos;
	}
	
	private function _findQuoteByFromName() {
//		var_dump($this);
		if (isset($this->from->personal)) {

			$parts = explode(' ', $this->from->personal);

			while ($part = array_pop($parts)) {

//					echo $part;
				
//				\Intermesh\Core\App::debug($part,'findquote');

				$startPos = strpos($this->_body, $part);

//					var_dump($startPos);

				if ($startPos) {
					
					App::debug('Stripped quote by from name: '.$part,'stripquote');
					
					$startPos += strlen($part);

					return $startPos;
				}
			}
		}

		return false;
	}
	
	private function _findQuoteByBlockQuote(){
		$pos = strpos($this->_body, "<blockquote");
		
		if($pos){
			App::debug('Stripped quote by blockquote','stripquote');
					
		}
		
		return $pos;
	}
	
	private $_bodyLines;
	
	private function _splitBodyLines($html) {
		
		if(!isset($this->_bodyLines)) {
			$br = '|BR|';

			$html = preg_replace([
				'/<\/p>/i', // <P>
				'/<\/div>/i', // <div>
				'/<br[^>]*>/i',
					], [
				$br . "</p>",
				$br . "</div>",
				$br . "<br />",
					], $this->_body);

			$this->_bodyLines = explode($br, $html);
		}
		return $this->_bodyLines;
	}

	/**
	 * eg
	 * 
	 * Van: Merijn Schering [mailto:mschering@intermesh.nl] 
		Verzonden: donderdag 20 november 2014 16:40
		Aan: Someone
		Onderwerp: Subject
	 * 
	 * @return int|boolean
	 */
	private function _findQuoteByHeaderBlock(){
		
		
		$lines = $this->_splitBodyLines($this->_body);
		
		$pos = 0;
		
		for($i=0,$c=count($lines);$i<$c;$i++) {		
			
			$plain = strip_tags($lines[$i]);

			//Match:
			//ABC: email@domain.com
			if(preg_match('/[a-z]+:[a-z0-9\._\-+\&]+@[a-z0-9\.\-_]+/i',$plain, $matches)){
				App::debug('Stripped quote by HeaderBlock '.var_export($matches, true),'stripquote');
				
				return $pos;
			}		
			
			$pos += String::length($lines[$i]);

		}
		return false;
	}
	
	private function _findQuoteByDashes(){
		
		$lines = $this->_splitBodyLines($this->_body);
		
//		var_dump($lines);
		
		$pos = 0;
		
		for($i=0,$c=count($lines);$i<$c;$i++) {		
			
			$plain = strip_tags($lines[$i]);

			//Match:
			//ABC: email@domain.com
			if(preg_match('/---.*---/',$plain, $matches)){
				App::debug('Stripped quote by dashes '.var_export($matches, true).' '.$lines[$i],'stripquote');
				
				return $pos;
			}		
			
			$pos += String::length($lines[$i]);

		}
		return false;
		
	}

	private function _stripQuote($plainText = false) {
//		
//		var_dump($body);
		
		$positions = [];
				
		$plainTextQuoteStartPos = $plainText ? $this->_findQuoteByGreatherThan() : false;
		if($plainTextQuoteStartPos)
		{
			$positions[] = $plainTextQuoteStartPos;
		}
		
		
		
//		if(!$startPos){
//			$startPos = $this->_findQuoteByFromName();
//
//		}

//		if(!$startPos){
		$blockQuoteStartPos = $this->_findQuoteByBlockQuote();
		if($blockQuoteStartPos)
		{
			$positions[] = $blockQuoteStartPos;
		}
		
//		if(!$startPos){
		$headerBlockStartPos = $this->_findQuoteByHeaderBlock();
		if($headerBlockStartPos)
		{
			$positions[] = $headerBlockStartPos;
		}
		
		$dashesStartPos = $this->_findQuoteByDashes();
		if($dashesStartPos)
		{
			$positions[] = $dashesStartPos;
		}
		
		$startPos = !empty($positions) ? min($positions) : false;
		

		if (!$startPos) {
			$this->_quote = "";
			return $this->_body;
		} else {
			$this->_quote = mb_substr($this->_body, $startPos);			
			return mb_substr($this->_body, 0, $startPos);
		}
	}

	public function getQuote() {
		
		if(!isset($this->_quote)){			
		
			$this->getBody();
		}

		return $this->_quote;
	}
	
	

	/**
	 * Get attachment parts
	 * 
	 * @return SinglePart
	 */
	public function getAttachments() {

		$attachments = $this->getStructure()->findPartsBy(function($part){
			return !empty($part->getFilename());
		});

//		if ($this->getStructure()->parts[0]->subtype == 'alternative') {
//			return [];
//		}
//
//		if (count($this->getStructure()->parts) == 1 && $this->getStructure()->parts[0]->type == 'multipart') {
//			$parts = $this->getStructure()->parts[0]->parts;
//		} else {
//			$parts = $this->getStructure()->parts;
//		}
//
//		foreach ($parts as $part) {
//			if ($part->partNumber != "1" && $part->type != "multipart") {
//				$attachments[] = $part;
//			}
//		}

		return $attachments;
	}

	/**
	 * True if message is answered
	 * 
	 * @return boolean
	 */
	public function getAnswered() {
		return in_array('\\Answered', $this->flags);
	}

	/**
	 * True if message is forwarded
	 * 
	 * @return boolean
	 */
	public function getForwarded() {
		return in_array('$Forwarded', $this->flags);
	}

	/**
	 * True if message is viewed
	 * 
	 * @return boolean
	 */
	public function getSeen() {
		return in_array('\Seen', $this->flags);
	}
	
	
	
	public function toArray(array $attributes = ['*', 'attachments']) {
		return parent::toArray($attributes);
	}

}
