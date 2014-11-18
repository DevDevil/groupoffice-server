<?php

namespace Intermesh\Modules\Email\Imap;

use Exception;
use Intermesh\Core\Model;

/**
 * Mailbox object
 * 
 * Handles all mailbox related IMAP functions
 * 
 * <code>
 * 
 * //Get root folders
 * $mailbox = new \Intermesh\Modules\Email\Imap\Mailbox($connection);
		
		$mailboxes = $mailbox->getChildren();
		
		foreach($mailboxes as $mailbox){
			$response['mailboxes'][] = [
				'name' => $mailbox->name, 
				'unseenCount' => $mailbox->getUnseenCount(), 
				'messagesCount' => $mailbox->getMessagesCount(),
				'uidnext' => $mailbox->getUidnext()
					];
		}
 * 
 * 
 * //Fetch messages
 * 
 * $mailbox = Mailbox::findByName($connection, "INBOX");
		
	$messages = $mailbox->getMessages('DATE', true,1,0);

	foreach($messages as $message){
		 $response['data'] = $message->toArray(['subject','from', 'to', 'body', 'attachments']);
 *	}
 * 
 * </code>
 *
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Mailbox extends Model {
	
	/**
	 *
	 * @var Connection 
	 */
	public $connection;
	
	/**
	 * Name of the mailbox
	 * 
	 * @var string 
	 */
	public $name;
	
	/**
	 * Reference. In most cases this is a namespace.
	 * 
	 * @var string 
	 */
	public $reference = "";	
	
	/**
	 * Mailbox delimiter
	 * 
	 * Usually "/" or "."
	 * 
	 * @var string 
	 */
	public $delimiter;	
	
	/**
	 * Array of mailbox flags
	 * 
	 * eg. NoInferiors, HasChildren
	 * 
	 * @var string 
	 */
	public $flags = [];
	
	
	/**
	 * True if this mailbox was selected using the SELECT mailbox command
	 * 
	 * @var boolean 
	 */
	public $selected = false;
	
	
	
	private $_status;

	
	/**
	 * Constructor
	 * 
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection) {
		parent::__construct();

		$this->connection = $connection;
	}
	
	public function __toString() {
		return $this->name;
	}
	
	/**
	 * Finds a mailbox by name
	 * 
	 * eg. 
	 * 
	 * $mailbox = Mailbox::findByName($conn, "INBOX");
	 * 
	 * @param string $name
	 * @param string $reference
	 * @return Mailbox|boolean
	 */
	public static function findByName(Connection $connection, $name, $reference = ""){
		
		if(!$connection->isAuthenticated()) {
			$connection->authenticate();
		}
		
		$cmd = 'LIST "'.Utils::escape(Utils::utf7Encode($reference)).'" "'.Utils::escape(Utils::utf7Encode($name)).'"';
		
		$connection->sendCommand($cmd);
		
		$response = $connection->getResponse();
				
		if($connection->lastCommandSuccessful){
			return self::createFromImapListResponse($connection, $response[0][0]);
		}else
		{
			return false;
		}
	}
	
	/**
	 * Not used directly.
	 * 
	 * When you use a find command this function is used to create mailboxes from
	 * the IMAP response.
	 * 
	 * @param Connection $connection
	 * @param array $lineParts
	 * @return Mailbox
	 */
	public static function createFromImapListResponse(Connection $connection, $responseLine){
		
		//eg. "* LIST (\HasNoChildren) "/" Trash"
		
		$lineParts = explode(' ', $responseLine);
		
		$mailbox = new Mailbox($connection);

		$mailbox->name = trim(array_pop($lineParts),' "');
		$mailbox->delimiter = trim(array_pop($lineParts),'"\'');
		
		while($part = array_pop($lineParts)) {			
			
			$flag = strtolower(trim($part, '\()'));
			if($flag == 'list' || $flag == 'lsub') {
				break;
			}					
			if(!empty($flag)){
				$mailbox->flags[] = $flag;
			}
		}
		
		return $mailbox;		
	}
	
	
	
	/**
	 * Get the mailbox status
	 * 
	 * Only messages and unseen are feteched
	 * 
	 * @return array eg. ['messages'=> 1, 'unseen' => 1]
	 * @throws Exception
	 */
	private function getStatus(){
		
		if(!isset($this->_status)){
			$cmd = 'STATUS "'.Utils::escape(Utils::utf7Encode($this->name)).'" (MESSAGES UNSEEN UIDNEXT)';

			$this->connection->sendCommand($cmd);

			$response = $this->connection->getResponse();

			if(!$this->connection->lastCommandSuccessful){
				throw new Exception("Could not fetch status from server");
			}
			
		
			$parts = explode(' ', $response[0][0]);
		
			$status = ['mailbox' => $parts[1]];
			
//			var_dump($parts);

			for($i = 3, $c = count($parts); $i < $c; $i++){

				$name = trim($parts[$i], ' ()');

				$i++;

				$value = trim($parts[$i], ' ()');

				$status[strtolower($name)] = intval($value);
			}	

			$this->_status = $status;
		}
		
		return $this->_status;
	}
	
	/**
	 * Get the number of unseen messages
	 * 
	 * @return int
	 */
	public function getUnseenCount(){
		$status = $this->getStatus();
		
		return $status['unseen'];
	}
	
	/**
	 * Get the next uid for the mailbox
	 * 
	 * @return int
	 */
	public function getUidnext(){
		$status = $this->getStatus();
		
		return $status['uidnext'];
	}
	
	/**
	 * Get the number of messages
	 * 
	 * @return int
	 */
	public function getMessagesCount(){
		$status = $this->getStatus();
		
		return $status['messages'];
	}
	
	/**
	 * Get children mailboxes	  
	 * 
	 * @param bool $listSubscribed	
	 * @return Mailbox[]
	 */
	public function getChildren($listSubscribed = true){
		
		
		$listCmd = $listSubscribed ? 'LSUB' : 'LIST';

//		if($listSubscribed && $this->has_capability("LIST-EXTENDED"))
//		$listCmd = "LIST (SUBSCRIBED)";
//			$listCmd = "LIST";
		
		$pattern = isset($this->name) ? '%' : Utils::escape(Utils::utf7Encode($this->name.$this->delimiter)).'%';

		
		if(!$this->connection->isAuthenticated()) {
			$this->connection->authenticate();
		}	
		

		$cmd = $listCmd.' "'.Utils::escape(Utils::utf7Encode($this->reference)).'" "'.$pattern.'"';
		
		$this->connection->sendCommand($cmd);
		
		$response = $this->connection->getResponse();	
//		var_dump($response);
		$mailboxes = [];
		while($responseLine = array_shift($response)){
			$mailboxes[] = self::createFromImapListResponse($this->connection, $responseLine[0]);
		}
		
		return $mailboxes;
	}
	
	
	public function threadSort(){
		
		//REFERENCES of ORDERED SUBJECT
		
		//A4 UID THREAD REFERENCES UTF-8 ALL\r\n'",
        // * THREAD ((3)(9)(13 14)(15))(2)(1)((4)(5)(7))(6)(8)(10)(11)(12)(16 (17 19)(18))\r\n'",
        //A4 OK Thread completed.\r\n'",
		
		
		if(!$this->selected) {
			$this->select();
		}
		
		$command = 'UID THREAD REFERENCES UTF-8 ALL';
		
		
		$this->connection->sendCommand($command);
		
		$response = $this->connection->getResponse();
		
		
		$str = substr($response[0][0], 9); //Take off THREAD
		
		return $this->_parseThreadResponse($str);
		
	}
	
	private $_threadUidIndex = [];
	
			
	/**
	 * Parses: 
		
		(11)(12)(16(17 19)(18))(20)
			
		Into:
			
	 * array(4) {
			[11]=>
			array(0) {
			}
			[12]=>
			array(0) {
			}
			[16]=>
			array(2) {
			  [17]=>
			  array(2) {
				[19]=>
				array(1) {
				  [22]=>
				  array(0) {
				  }
				}
				[21]=>
				array(0) {
				}
			  }
			  [18]=>
			  array(0) {
			  }
			}
			[20]=>
			array(0) {
			}
		  }
	 * 
	 * @param type $str
	 * @param $highestLevelUid Every thread group has the higherst level UID as group ID. And index of every uid to this group is set in _threadIndex
	 * @return type
	 */
	private function _parseThreadResponse($str, $highestLevelUid = false){
		
		
		$str = str_replace(array(' )', ' ) ', ')', ' (', ' ( ', '( '), array(')', ')', ')', '(', '(', '('), $str);
//		var_dump($str);
//		$str = substr($str, 8); //Take off THREAD
		
		$tokens = str_split($str);
		
		$threads = [];
		
		$level = 0;
		
		
		$closeLevels = 0;
		
		while($token = array_shift($tokens)) {
//			echo $token."\n";
			
			switch($token){	
				
				
				case ' ':
						$closeLevels++;
					
				
				case '(':					
					$level++;
					
//					echo "New level $level\n";
			
					
					if($level==1){
						$threadUid = "";
						while(($token = array_shift($tokens)) !== false && (is_numeric($token) /*|| $token ==' '*/)) {
							$threadUid .= $token;
						}
						
//						var_dump($threadUid);
						
						if(!empty($threadUid)){
						
							$threads[$threadUid]=[];
							
							
							
							$this->_threadUidIndex[$threadUid] = $highestLevelUid ? $highestLevelUid : $threadUid;
						
//						echo "New thread: ".$threadUid."\n";
						}else
						{
							//(11)(12)(16(17(19 22 23)(21))(18))(20)((24)(25))
							
							//this happens with 24 and 25. They are grouped without a parent.							
									
							$level--;
						}
						
						array_unshift($tokens, $token);
						
						$subStr = "";						
					}else
					{						
						$subStr .= $token;
					}
					
					break;
				
				case ')':
					
					$level -= $closeLevels;
					
					$closeLevels = 0;
					
					$level--;		
					
//					echo "New level $level\n";					
					
					if(isset($subStr)){
						if($level==0){
							$subStr .= $token;

							$threads[$threadUid] = $this->_parseThreadResponse($subStr, $highestLevelUid ? $highestLevelUid : $threadUid);
						}else
						{
							$subStr .= $token;
						}
					}
					
					break;
			
				
				default:
					
//					echo "Add to sub\n";
					
					$subStr .= $token;
					break;
			}
		}
		
		return $threads;
		
	}
	
	/**
	 * Get messages from this mailbox
	 * 
	 * @return Message[]
	 */
	public function getMessages($sort = 'DATE', $reverse = true, $threaded = false, $limit = 10, $offset = 0, array $returnAttributes=["uid","flags","date","from","subject","to","contentType","xPriority"], $filter='ALL'){
		$uids = $this->serverSideSort($sort, $reverse, $filter);
//		var_dump($uids);
		if(!$uids){
			return false;
		}
		
		
		if($limit>0){
			
			if(!$threaded) {
				$uids = array_slice($uids, $offset, $limit);
			}else
			{
				$threads = $this->threadSort();
				
//				var_dump($threads);
//				var_dump($this->_threadUidIndex);
				//A single UID of each unique thread
				$threadUids = [];				
				$count = 0;
				$max = $offset+$limit; //stop if we have enough.
				foreach($uids as $uid){
					$threadUid = $this->_threadUidIndex[$uid];
					if(!isset($threadUids[$threadUid])){
						$threadUids[$threadUid] = $uid;
						$count++;
						
						if($count == $max){
							break;
						}
					}				
				}
				$uids = array_slice(array_values($threadUids), 0, $limit);				
				
				
			}
		}		
		
		$responses = $this->getMessageHeaders($uids, $returnAttributes);

		$messages = [];
		
		while($response = array_shift($responses)){			
			$messages[] = Message::createFromImapResponse($this, $response[0], $response[1]);
		}
		
		
		//even though the uid list is sorted UID FETCH does not return it sorted.
		$uidIndex = array_flip($uids);
		$sorted = [];		
		while($message = array_shift($messages)){
			
			if(isset($threads)){
				$message->thread = $threads[$this->_threadUidIndex[$message->uid]];
			}
			
			$sorted[$uidIndex[$message->uid]] = $message;
		}		
		ksort($sorted);
		
		return $sorted;
	}
	
	/**
	 * Fetch a single message
	 * 
	 * @param int $uid
	 * @return Message
	 */
	public function getMessage($uid, array $returnAttributes = []){		
		
		$uid = (int) $uid;
		
		if(!$this->selected) {
			$this->select();
		}
		
		if(count($returnAttributes)) {
			$responses = $this->getMessageHeaders([$uid], $returnAttributes);

			return Message::createFromImapResponse($this, $responses[0][0], $responses[0][1]);
		}else
		{
			return new Message($this, $uid);
		}
	}
	
	/**
	 * Select this mailbox on the IMAP server
	 * 
	 * @return boolean
	 */
	private function select(){
		
		$command = 'SELECT "'.Utils::escape(Utils::utf7Encode($this->name)).'"';
		
		$this->connection->sendCommand($command);
		
		$this->connection->getResponse();
		
		$this->selected = $this->connection->lastCommandSuccessful;
		
		return $this->connection->lastCommandSuccessful;
	}
	
	
	/**
	 * Get an array of UIDS sorted by the server.
	 * 
	 * @param string $sort 'DATE", 'ARRIVAL', 'SUBJECT', 'FROM'
	 * @param boolean $reverse
	 * @param string $filter
	 * 
	 * @return array|boolean UID list ['1','2']
	 */
	private function serverSideSort($sort = 'DATE', $reverse = true, $filter="ALL") {
		
		if(!$this->selected) {
			$this->select();
		}
		
		$command = 'UID SORT ('.$sort.') UTF-8 '.$filter;
		
		$this->connection->sendCommand($command);
		
		$response = $this->connection->getResponse();
		
		if(!$this->connection->lastCommandSuccessful){
			return false;
		}

		
		$uids = [];

		while($line = array_shift($response[0])) {
			
			$vals = explode(" ", trim(str_replace('* SORT', '', $line)));		
			$uids = array_merge($uids, $vals);
			
		}
		
		if($reverse){
			$uids = array_reverse($uids);
		}
	
		return $uids;
	}
	

	
	private function _buildFetchProps(array $returnAttributes){
//		$returnAttributes = array_map([$this, '_decamelCasify'], $returnAttributes);
				
		$props = '';
		
		$availableFields = [
			'FLAGS', 
			'INTERNALDATE',
			'SIZE'
			];
		
		foreach($availableFields as $field) {
			if(in_array($field, $returnAttributes)){
				$props .= $field.' ';
			}
		}
		
		$props = str_replace('SIZE', 'RFC822.SIZE', trim($props));
		
		$availableBodyProps = ["SUBJECT", "FROM", "DATE", "CONTENT-TYPE", "X-PRIORITY", "TO", "CC", "BCC", "REPLY-TO", "DISPOSITION-NOTIFICATION-TO", "MESSAGE-ID"];
		
		$bodyProps = "";
		
		foreach($availableBodyProps as $field) {
			if(in_array($field, $returnAttributes)){
				$bodyProps .= $field.' ';
			}
		}
		
		if(!empty($bodyProps)){
			$props .= ' BODY.PEEK[HEADER.FIELDS ('.trim($bodyProps).')]';
		}
		
		
		return $props;
	}
	
	private function getMessageHeaders($uids, array $returnAttributes = ["FLAGS","SIZE","DATE","FROM","SUBJECT","TO","CC","BCC","REPLY-TO","CONTENT_TYPE","MESSAGE-ID","X-PRIORITY","DISPOSITION-NOTIFICATION-TO"]) {

		if(empty($uids)){
			return [];
		}
		
		$sortedString = implode(',', $uids);		
		
		$command = 'UID FETCH '.$sortedString.' ('.$this->_buildFetchProps($returnAttributes).')';

		$this->connection->sendCommand($command);
		$res = $this->connection->getResponse();
		
		if(!$this->connection->lastCommandSuccessful){
			throw new \Exception($this->connection->lastCommandStatus);
		}
		
		
		
		
		return $res;		
	}	
	
	
	/**
	 * @todo
	 */
	public function getAcl(){

		$mailbox = Utils::escape(Utils::utf7Encode($this->name));

		$command = 'GETACL "'.$mailbox.'"';
		$this->connection->sendCommand($command);
//		$response = $this->get_response(false, true);
//
//		$ret = array();
//
//		foreach($response as $line)
//		{
//			if($line[0]=='*' && $line[1]=='ACL' && count($line)>3){
//				for($i=3,$max=count($line);$i<$max;$i+=2){
//					$ret[]=array('identifier'=>$line[$i],'permissions'=>$line[$i+1]);
//				}
//			}
//		}
//
//		return $ret;
	}

	public function setAcl($identifier, $permissions){

//		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
//		$this->clean($mailbox, 'mailbox');
//
//		$command = "SETACL \"$mailbox\" $identifier $permissions\r\n";
//		//throw new \Exception($command);
//		$this->send_command($command);
//
//		$response = $this->get_response();
//
//		return $this->check_response($response);
	}

	public function deleteAcl($identifier){
//		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
//		$this->clean($mailbox, 'mailbox');
//
//		$command = "DELETEACL \"$mailbox\" $identifier\r\n";
//		$this->send_command($command);
//		$response = $this->get_response();
//		return $this->check_response($response);
	}
	
	public function toArray(array $attributes = array('name','delimiter','flags', 'unseencount', 'messagescount', 'uidnext')) {
		return parent::toArray($attributes);
	}
}