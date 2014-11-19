<?php

namespace Intermesh\Modules\Email\Imap;

use DateTime;
use DOMDocument;
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
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
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
	private static $mimeDecodeAttributes = ['to', 'replyTo', 'cc', 'bcc', 'dispositionNotificationTo', 'subject'];
	private $_structure;

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

			if (property_exists($message, $prop)) {

				if (in_array($prop, self::$mimeDecodeAttributes)) {
					$value = Utils::mimeHeaderDecode($value);
				}

				if ($prop == 'to' || $prop == 'cc' || $prop == 'bcc') {
					$list = new RecipientList($value);
					$value = $list->toArray();
				}


				if ($prop == 'from' || $prop == 'replyTo' || $prop == 'displayNotificationTo') {
					$list = new RecipientList($value);
					$value = $list[0];
				}

				$message->$prop = $value;
			}
		}

		return $message;
	}

	private static function _parseFetchResponse($response) {

//		echo $response;

		$attr = [];

		$start = strpos($response, 'UID');
		$end = strpos($response, 'BODY');

		$line = substr($response, $start, $end - $start - 1);

		$arr = str_getcsv($line, ' ');

		for ($i = 0, $c = count($arr); $i < $c; $i++) {
			$name = String::lowerCamelCasify($arr[$i]);

			$value = $arr[$i + 1];

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

		return $attr;
	}

	/**
	 * Get's the message structure object
	 * 
	 * @return Structure
	 */
	public function getStructure() {

		if (!isset($this->_structure)) {
			$this->_structure = new Structure($this);
		}

		return $this->_structure;
	}

	private $_body;
	private $_quote;

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

			$this->_body = String::normalizeCrlf($this->_body, "\n");

			if ($part->subtype == 'plain' && $asHtml) {
				$this->_body = $this->_stripQuote(true);
				$this->_body = nl2br($this->_body);
				$this->_quote = nl2br($this->_quote);
			}

			if ($part->subtype == 'html') {
				$this->_body = $this->_stripQuote();

				$doc = new DOMDocument();
				@$doc->loadHTML($this->_body);
				$this->_body = $doc->saveHTML();

				$this->_body = String::sanitizeHtml($this->_body);
			}

			$this->_body = String::convertLinks($this->_body);
		}
		
		return $this->_body;
	}
	

	private function _stripQuote($plainText = false) {
//		
//		var_dump($body);
		
		$startPos = false;
				
		if($plainText){
			$startPos = strpos($this->_body, "\n>");
		}
		
		if(!$startPos){

			if (isset($this->from->personal)) {

				$parts = explode(' ', $this->from->personal);

				while ($part = array_pop($parts)) {
					
//					echo $part;
					
					$startPos = strpos($this->_body, $part);
					
//					var_dump($startPos);

					if ($startPos) {
						$startPos += strlen($part);
						break;
					}
				}
			}
		}


		


		if (!$startPos) {
			$startPos = strpos($this->_body, "<blockquote");

			if (!$startPos) {
				preg_match_all('/>[^:\n]+:[a-z0-9\._\-+\&]+@[a-z0-9\.\-_]+\b/i', $this->_body, $matches, PREG_OFFSET_CAPTURE);

				//				var_dump($matches);

				if (isset($matches[0][0])) {
					$startPos = strrpos($this->_body, '<', $matches[0][0][1] - strlen($this->_body));

					//					$body = substr($body, 0, $startPos).' HIER '.substr($body, $startPos);
					//					
					//					$startPos = false;
					////					var_dump($startPos);
				}
			}
		}
		

		if (!$startPos) {
			$this->_quote = "";
			return $this->_body;
		} else {
			$this->_quote = substr($this->_body, $startPos);
			
			$doc = new DOMDocument();
			@$doc->loadHTML($this->_quote);
			$this->_quote = $doc->saveHTML();
			$this->_quote = String::sanitizeHtml($this->_quote);
			
			return substr($this->_body, 0, $startPos);
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

		$attachments = [];

		if ($this->getStructure()->parts[0]->subtype == 'alternative') {
			return [];
		}

		if (count($this->getStructure()->parts) == 1 && $this->getStructure()->parts[0]->type == 'multipart') {
			$parts = $this->getStructure()->parts[0]->parts;
		} else {
			$parts = $this->getStructure()->parts;
		}

		foreach ($parts as $part) {
			if ($part->partNumber != "1" && $part->type != "multipart") {
				$attachments[] = $part;
			}
		}

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

}
