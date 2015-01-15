<?php

namespace GO\Modules\Email\Imap;

use Exception;
use GO\Core\AbstractModel;

/**
 * Mailbox object
 * 
 * Handles all mailbox related IMAP functions
 * 
 * <code>
 * 
 * //Get root folders
 * $mailbox = new \GO\Modules\Email\Imap\Mailbox($connection);
		
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
 * @property boolean $noSelect Mailbox is virtual and can't be selected
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Mailbox extends AbstractModel {
	
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
	 * They are all lower case and with a leading slash eg.:
	 * 
	 * noinferiors, haschildren, noselect, hasnochildren
	 * 
	 * @var string 
	 */
	public $flags = [];
	
	
	
	private $_unseen;
	
	private $_highestModSeq;
	
	private $_uidValidity;
	
	private $_messagesCount;
	
	private $_recent;
	
	private $_uidNext;
	
	
	/**
	 * True if this mailbox was selected using the SELECT mailbox command
	 * 
	 * @var boolean 
	 */
	public function getSelected(){
		return $this->connection->selectedMailbox == $this->name;
	}
			

	
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
			
			if(!isset($response[0][0])){
				return false;
			}
			
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
	 * @param string $responseLine
	 * @return Mailbox
	 */
	public static function createFromImapListResponse(Connection $connection, $responseLine){
		
		//eg. "* LIST (\HasNoChildren) "/" Trash"
		// * LSUB () "." "Ongewenste e-mail"
		
		$lineParts = str_getcsv($responseLine,' ', '"');
		
//		var_dump($lineParts);
		
		$mailbox = new Mailbox($connection);
		
		$mailbox->name = array_pop($lineParts);
		$mailbox->delimiter = array_pop($lineParts);
		
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
	private function getStatus($props = ['MESSAGES','UIDNEXT','UNSEEN']){
		
//		if(!isset($this->_status)){
			
			if($this->selected){
				$this->unselect();
			}
			
			$cmd = 'STATUS "'.Utils::escape(Utils::utf7Encode($this->name)).'" ('.implode(' ',$props).')';

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

		
//		}
		
		return $status;
	}

	
	
	/**
	 * Get the UID validity
	 * 
	 * {@see http://tools.ietf.org/html/rfc4551}
	 * 
	 * @return int
	 */
	public function getUidValidity(){
		if(!isset($this->_uidValidity)){
//			throw new \Exception("TODO implement examine");
			$this->select();
		}
		
		return $this->_uidValidity;
	}
	
	/**
	 * Get number of recent messages
	 * 
	 * 
	 * @return int
	 */
	public function getRecent(){
		if(!isset($this->_recent)){
//			throw new \Exception("TODO implement examine");
			$this->select();
		}
		
		return $this->_recent;
	}
	
	
	/**
	 * Get the highest mod sequence
	 * 
	 * {@see http://tools.ietf.org/html/rfc4551}
	 * 
	 * @return int
	 */
	public function getHighestModSeq(){
		if(!isset($this->_highestModSeq)){
//			throw new \Exception("TODO implement examine");
			$this->select();
		}
		
		return $this->_highestModSeq;
	}
	
	
	
	/**
	 * Get the number of unseen messages
	 * 
	 * @return int
	 */
	public function getUnseenCount(){
		
		if(!isset($this->_unseen)){
			$status = $this->getStatus(['UNSEEN']);
			
			$this->_unseen = $status['UNSEEN'];
		}
		
		return $this->_unseen;
	}
	
	/**
	 * Get the next uid for the mailbox
	 * 
	 * @return int
	 */
	public function getUidnext(){
		if(!isset($this->_uidNext)){
			$status = $this->getStatus(['UIDNEXT']);
			
			$this->_unseen = $status['UIDNEXT'];
		}
		
		return $this->_uidNext;
	}
	
	
	
	/**
	 * Get the number of messages
	 * 
	 * @return int
	 */
	public function getMessagesCount(){
		
		
		
		if(!isset($this->_messagesCount)){
			
			if(!$this->noSelect){
				$status = $this->getStatus(['MESSAGES']);

				$this->_messagesCount = $status['MESSAGES'];
			}  else {
			
			}$this->_messagesCount = 0;
		}
		
		return $this->_messagesCount;
	}
	
	public function getNoSelect(){
		return in_array('noselect', $this->flags);
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
	private function _parseThreadResponse($str, $recursive=false, $highestLevelUid = false){
		
		
		$str = str_replace(array(' )', ' ) ', ')', ' (', ' ( ', '( '), array(')', ')', ')', '(', '(', '('), $str);
//		var_dump($str);
//		$str = substr($str, 8); //Take off THREAD
		
		$tokens = str_split($str);
		
//		var_dump($tokens);
		
		$threads = [];
		
		$level = 0;
		
		
		$closeLevels = 0;
		
		while($token = array_shift($tokens)) {
			echo $token."\n";
			
			switch($token){	
				
				
				case ' ':
						$closeLevels++;
					
				
				case '(':					
					$level++;
					
//					echo "New level $level\n";
			
					
					if($level==1){
						$threadUid = "";
						while(($token = array_shift($tokens)) !== false && (is_numeric($token) /*|| $token ==' '*/)) {
							
//							echo 'T:'.$token.'-';
							
							$threadUid .= $token;
						}
						
//						var_dump($threadUid);
						
						if(!empty($threadUid)){
						
							$threads[$threadUid]=[];
							
							
							
							$this->_threadUidIndex[$threadUid] = $highestLevelUid ? $highestLevelUid : $threadUid;
//							$this->_threadUidIndex[$threadUid]
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
					if($level == -1){
						//happens with extra ()
						//((1)(9)(10(11)(12))(13)(14))(2)(3)(4)((5)(6(32)(33)))((7)(8))(34)(15 16 17(18 19(21 25 26 27(28)(31))(24))(20))(22)(23)((29)(30))
						//happens on (2) here.
						$level = 0;
					}
					
//					echo "New level $level\n";					
					
					if(isset($subStr)){
						if($level==0){
							$subStr .= $token;
							
							if(!$recursive) {
								
//								var_dump($subStr);
								preg_match_all('/\d+/', $subStr, $matches);

								$threads[$threadUid] = array_merge([$threadUid], $matches[0]);
								foreach($threads[$threadUid] as $uid){
									$this->_threadUidIndex[$uid] = $threadUid;
								}
								
								rsort($threads[$threadUid]);
								
							}else{
								$threads[$threadUid] = $this->_parseThreadResponse($subStr, $highestLevelUid ? $highestLevelUid : $threadUid);
							}	

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
	public function getMessages($sort = 'DATE', $reverse = true, $threaded = false, $limit = 10, $offset = 0, $filter='ALL', array $returnAttributes=[]){
		$uids = $this->serverSideSort($sort, $reverse, $filter);
//		var_dump($uids);
		
		if(!count($uids)){
			return array();
		}
		
		//even though the uid list is sorted UID FETCH does not return it sorted.
		$uidIndex = array_flip($uids);
		
		
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
//		
//		var_dump(count($uids));
//		exit();
		
		
		
		$messages = $this->getMessagesUnsorted($uids, $returnAttributes);		
		
		
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
	 * Get message objects without sorting
	 * 
	 * @param array|string $uidSequence array of uids or sequence like 1:*
	 * @param array $returnAttributes
	 * @param int $changedSinceModSeq Modsequence to get only changed messages
	 * @param int $changedSinceModSeq Modsequence to get only changed messages
	 * @return Message[]
	 */
	public function getMessagesUnsorted($uidSequence, array $returnAttributes = [], $changedSinceModSeq = null){
		
		if(empty($uidSequence)){
			return [];
		}
		
		if(is_array($uidSequence)){		
			$uidSequence = implode(',', $uidSequence);	
		}
		
		$responses = $this->getMessageHeaders($uidSequence, $returnAttributes, $changedSinceModSeq);

		$messages = [];
		
		while($response = array_shift($responses)){	
			
			if(substr($response[0], 0,4) == '* OK'){
				//not a message. but when changedSince is given it may reply with:
				//* OK [HIGHESTMODSEQ 1] Highest
			}else
			{				
				$messages[] = Message::createFromImapResponse($this, $response[0], isset($response[1]) ? $response[1] : "");
			}
		}
		
		return $messages;
	}
	
//	private function sortUids($uidIndex, $uidsToSort){
//		
//		usort($uidsToSort, function($a, $b) use($uidIndex){
//			return $uidIndex[$b] - $uidIndex[$a];
//		});
//		
//		return $uidsToSort;
//		
//	}
	
	
	/**
	 * Fetch a single message
	 * 
	 * @param int $uid
	 * @return Message|boolean
	 */
	public function getMessage($uid, $noFetchProps = false, array $returnAttributes = []){		
		$uid = (int) $uid;
				
		$this->select();		
		
		if(!$noFetchProps) {
			$responses = $this->getMessageHeaders($uid, $returnAttributes);
			
			if(!$this->connection->lastCommandSuccessful){
				throw new \Exception($this->connection->lastCommandStatus);
			}
			
			if(empty($responses[0][0])){
				return false;
			}

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
	public function select(){
		if($this->selected) {
			return true;
		}
		
		
		$command = 'SELECT "'.Utils::escape(Utils::utf7Encode($this->name)).'"';
		
		$this->connection->sendCommand($command);
		
		$responses = $this->connection->getResponse();
		
		if($this->connection->lastCommandSuccessful){
			foreach($responses as $response){
				//* 13477 EXISTS
				if(preg_match('/\* ([0-9]+) EXISTS/',$response[0], $matches)){
					$this->_messagesCount = (int) $matches[1];
				}
				elseif(preg_match('/\* ([0-9]+) RECENT/',$response[0], $matches)){
					$this->_recent = (int) $matches[1];
				}
	//			elseif(preg_match('/\* OK \[UNSEEN ([0-9]+)\]/',$response[0], $matches)){
	//				//"* OK [UNSEEN 13338] First unseen."
	//				$this->_status['unseen'] = $matches[1];
	//			}
				elseif(preg_match('/\* OK \[UIDVALIDITY ([0-9]+)\]/',$response[0], $matches)){
					//"* OK [UNSEEN 13338] First unseen."
					$this->_uidValidity = (int) $matches[1];
				}
				elseif(preg_match('/\* OK \[UIDNEXT ([0-9]+)\]/',$response[0], $matches)){
					//"* OK [UNSEEN 13338] First unseen."
					$this->_uidNext = (int) $matches[1];
				}
				elseif(preg_match('/\* OK \[HIGHESTMODSEQ ([0-9]+)\]/',$response[0], $matches)){
					//"* OK [UNSEEN 13338] First unseen."
					$this->_highestModSeq = (int) $matches[1];
				}
			}	
		
			$this->connection->selectedMailbox = $this->name;
		}
		
		return $this->connection->lastCommandSuccessful;
	}
	
	
	
	
	
	
	/**
	 * Select this mailbox on the IMAP server
	 * 
	 * @return boolean
	 */
	public function unselect(){
		
		$command = 'UNSELECT';
		
		$this->connection->sendCommand($command);
		
		$this->connection->getResponse();
		
		if($this->connection->lastCommandSuccessful){
			$this->connection->selectedMailbox = null;
		}
		
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
			$str =  trim(str_replace('* SORT', '', $line));
			if(!empty($str)) {
				$vals = explode(" ", $str);		
				$uids = array_merge($uids, $vals);
			}
			
		}
		
		if($reverse){
			$uids = array_reverse($uids);
		}
	
		return $uids;
	}
	
	
	/**
	 * Search the mailbox for UID's.
	 * 
	 * @param string $filter
	 * @return boolean
	 */
	public function search($filter = "ALL"){
		
		
		if(!$this->selected) {
			$this->select();
		}
		
		$command = 'UID SEARCH '.$filter;
		
		
		$this->connection->sendCommand($command);
		
		$response = $this->connection->getResponse();
		
		if(!$this->connection->lastCommandSuccessful){
			return false;
		}

		
		$uids = [];

		while($line = array_shift($response[0])) {
			$str =  trim(str_replace('* SEARCH', '', $line));
			if(!empty($str)) {
				$vals = explode(" ", $str);		
				$uids = array_merge($uids, $vals);
			}
			
		}
		
		return $uids;
		
	}
	

	
	private function _buildFetchProps(array $returnAttributes = []){
//		$returnAttributes = array_map([$this, '_decamelCasify'], $returnAttributes);
				
		$props = '';
		
		$availableFields = [
			'FLAGS', 
			'INTERNALDATE',
			'SIZE',
			'BODYSTRUCTURE' //Important that this one comes last for parsing in Message model
			];
		
		foreach($availableFields as $field) {
			if(empty($returnAttributes) || in_array($field, $returnAttributes)){
				$props .= $field.' ';
			}
		}
		
		$props = str_replace('SIZE', 'RFC822.SIZE', trim($props));
		
		$availableBodyProps = ["SUBJECT", "FROM", "DATE", "CONTENT-TYPE", "X-PRIORITY", "TO", "CC", "BCC", "REPLY-TO", "DISPOSITION-NOTIFICATION-TO", "MESSAGE-ID", "REFERENCES", "IN-REPLY-TO"];
		
		$bodyProps = "";
		
		foreach($availableBodyProps as $field) {
			if(empty($returnAttributes) || in_array($field, $returnAttributes)){
				$bodyProps .= $field.' ';
			}
		}
		
		if(!empty($bodyProps)){
			$props .= ' BODY.PEEK[HEADER.FIELDS ('.trim($bodyProps).')]';
		}
		
		
		return $props;
	}
	
	private function getMessageHeaders($uidSequence, array $returnAttributes = [], $changedSinceModSeq = null) {
		
		if(!$this->selected){
			$this->select();
		}
		
					
		$command = 'UID FETCH '.$uidSequence.' ('.$this->_buildFetchProps($returnAttributes).')';
		
		if(isset($changedSinceModSeq)){
			$command .= ' (CHANGEDSINCE '.(int) $changedSinceModSeq.')';
		}
		
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
	
	
	/**
	 * Set or clear flags on messages in this mailbox 
	 * 
	 * Flags can be:
	 *
	 * \Seen
	 * \Answered
	 * \Flagged
	 * \Deleted
	 * $Forwarded
	 * other custom flags
	 *
	 * @param string $uidSequence UID's eg: 1:* OR 1:5 OR 1,2,3
	 * @param array $flags
	 * @param boolean $clear
	 * @return boolean
	 */
	public function setFlags($uidSequence, array $flags, $clear = false) {
		
		$this->select();
		
		if(is_array($uidSequence)){		
			$uidSequence = implode(',', $uidSequence);	
		}
		
		$sign = $clear ? '-' : '+';
		
		$command = "UID STORE ".$uidSequence." ".$sign."FLAGS.SILENT (";
		
		$command .= implode(' ', $flags);
		
		$command .= ")";
		
		$this->connection->sendCommand($command);
		
		return $this->connection->lastCommandSuccessful;
	}
	
	/**
	 * Expunge the mailbox
	 * 
	 * Will delete all messages in the mailbox with the \Deleted flag set.
	 * 
	 * @return boolean
	 */
	public function expunge(){
		$this->connection->sendCommand("EXPUNGE");
		return $this->connection->lastCommandSuccessful;
	}
}