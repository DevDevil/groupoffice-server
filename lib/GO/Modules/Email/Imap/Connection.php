<?php

namespace GO\Modules\Email\Imap;

use Exception;
use GO\Core\App;

/**
 * IMAP Connection
 * 
 * Connects and communicates with an IMAP server
 *
 * @link https://tools.ietf.org/html/rfc3501
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Connection {

	private $handle;
	private $authenticated = false;
	private $ssl = false;
	private $server = '';
	private $port = 143;
	private $username = '';
	private $password = '';
	private $starttls = false;
	private $auth = 'plain';
	
	private $_capability;
	
	
	/**
	 * the selected mailbox name
	 * 
	 * @var string 
	 */
	public $selectedMailbox;
	
	
	/**
	 * Set to true if the last IMAP command had an OK response.
	 * 
	 * @var boolean 
	 */
	public $lastCommandSuccessful = false;
	
	/**
	 * Set to the last reponse line.
	 * 
	 * eg. 
	 * 
	 * A3 OK Status completed
	 * 
	 * @var string 
	 */
	public $lastCommandStatus;
	
	
	public static $test;

	/**
	 * Constructor
	 * 
	 * @param string $server
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 * @param boolean $ssl
	 * @param boolean $starttls
	 * @param string $auth 'plain' or 'cram-md5'
	 */
	public function __construct($server, $port, $username, $password, $ssl = false, $starttls = false, $auth = 'plain') {
		
		if(isset(self::$test)){
			throw new Exception("Reconstruct!");
		}
		
		self::$test = true;

		$this->ssl = $ssl;
		$this->starttls = $starttls;
		$this->auth = strtolower($auth);

		$this->server = $server;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
	}

	private function getHandle() {

		if (!isset($this->handle)) {
			
			$streamContext = stream_context_create(['ssl' => [
					"verify_peer"=>false,
					"verify_peer_name"=>false
			]]);
			
			$errorno = null;
			$errorstr = null;
			$remote = $this->ssl ? 'ssl://' : '';			
			$remote .=  $this->server.":".$this->port;
			
			$this->handle = stream_socket_client($remote, $errorno, $errorstr, 10, STREAM_CLIENT_CONNECT, $streamContext);
		}

		if (!is_resource($this->handle)) {
			throw new Exception('Failed to open socket #' . $errorno . '. ' . $errorstr);
		}


		return $this->handle;
	}

	/**
	 * Connects to the IMAP server
	 * 
	 * @return boolean
	 */
	public function connect() {

		$this->getHandle();

		return true;
	}
	
	/**
	 * Disconneect from the IMAP server
	 * 
	 * @return boolean
	 */
	public function disconnect() {
		if (is_resource($this->handle)) {
			$command = "LOGOUT";
			$this->sendCommand($command);
			$this->autentcated = false;
			
			$response = $this->getResponse();
			
			fclose($this->handle);

//			foreach($this->errors as $error){
//				trigger_error("IMAP error: ".$error);
//			}

			$this->handle = null;

			return true;
		}else {
			return false;
		}
	}
	
	/**
	 * Checks if authentication was made
	 * 
	 * @return boolean
	 */
	public function isAuthenticated(){
		return $this->authenticated;
	}

	/**
	 * Authenticate to the IMAP server
	 *
	 * @return boolean
	 */
	public function authenticate() {

//		if ($this->starttls) {
//			$this->sendCommand("STARTTLS");
//			$response = $this->get_response();
//			if (!empty($response)) {
//				$end = array_pop($response);
//				if (substr($end, 0, strlen('A'.$this->command_count.' OK')) == 'A'.$this->command_count.' OK') {
//					stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
//				}
//			}
//		}
		switch (strtolower($this->auth)) {
//			case 'cram-md5':
//				$this->banner = fgets($this->handle, 1024);
//				$cram1 = 'A'.$this->command_number().' AUTHENTICATE CRAM-MD5'."\r\n";
//				fputs ($this->handle, $cram1);
//				$this->commands[trim($cram1)] = \GO\Base\Util\Date::getmicrotime();
//				$response = fgets($this->handle, 1024);
//				$this->responses[] = $response;
//				$challenge = base64_decode(substr(trim($response), 1));
//				$pass .= str_repeat(chr(0x00), (64-strlen($pass)));
//				$ipad = str_repeat(chr(0x36), 64);
//				$opad = str_repeat(chr(0x5c), 64);
//				$digest = bin2hex(pack("H*", md5(($pass ^ $opad).pack("H*", md5(($pass ^ $ipad).$challenge)))));
//				$challenge_response = base64_encode($username.' '.$digest);
//				$this->commands[trim($challenge_response)] = \GO\Base\Util\Date::getmicrotime();
//				fputs($this->handle, $challenge_response."\r\n");
//				break;
			default:

				$this->sendCommand('LOGIN "' . Utils::escape($this->username) . '" "' . Utils::escape($this->password) . '"');

				break;
		}

		$response = $this->getResponse();
		
		//returns A1 OK lastly on success
		$this->authenticated = $this->lastCommandSuccessful;
		
		
		if($this->authenticated){
			
			$lastLine = array_pop($response[0]);
		
			if(($startpos = strpos($lastLine, 'CAPABILITY'))!==false){
				App::debug("Use capability from login", "imap");					
				$endpos=  strpos($lastLine, ']', $startpos);
				if($endpos){
					$this->_capability = substr($lastLine, $startpos, $endpos-$startpos);
					
				}

			}
		}		

		return $this->authenticated;
	}	
	
	/**
	 * Get's the capabilities of the IMAP server. Useful to determine if the
	 * IMAP server supports server side sorting.
	 *
	 * @return string
	 */

	public function getCapability() {
		//Cache capability in the session so this command is not used repeatedly
	
		if(!isset($this->_capability)){			
			$this->sendCommand("CAPABILITY");
			$response = $this->getResponse();
			
			$this->_capability = implode(' ', $response);
		}		
		
		return $this->capability;
	}

	/**
	 * Check if the IMAP server has a particular capability.
	 * eg. QUOTA, ACL, LIST-EXTENDED etc.
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function hasCapability($str){
		return stripos($this->getCapability(), $str)!==false;
	}
	
	
	/**
	 * Send command to IMAP
	 * 
	 * eg. sendCommand("STATUS INBOX");
	 * 
	 * @param string $command
	 * @throws Exception
	 */
	public function sendCommand($command) {
		$handle = $this->getHandle();

		$command = 'A' . $this->commandNumber() . ' ' . $command . "\r\n";

		App::debug('> ' . $command, 'imap');
		
//		var_dump($command);

		if (!fputs($handle, $command)) {
			throw new \Exception("Lost connection to " . $this->server);
		}
	}
	
	
	/**
	 * Reads a single line from the IMAP server
	 * 
	 * @param int $length
	 * @return string
	 */
	public function readLine($length = 8192, $debug = true){
		$line = fgets($this->getHandle(), $length);

		if($debug){
			App::debug('< ' . $line, 'imap');	
		}
		
		
//		var_dump($line);
		return $line;
	}

	/**
	 * Returns text response in array
	 * 
	 * @param Streamer Optionally a Streamer object can be passed to stream it to a file or output for memory efficiency.
	 * @return array
	 */
	public function getResponse(Streamer $streamer = null) {

		$response = [];	
		
		$responses = [];
		
		$lastCommandTag = 'A' . $this->commandCount;
		$lastCommandTagLength = strlen($lastCommandTag);

		$commandEnd = false;
		
		$data = "";
		
		do {
			
			$chunk = $this->readLine();
			
			if(($commandEnd = substr($chunk, 0, $lastCommandTagLength) === $lastCommandTag)){
				
				if(!empty($data)){
					$response[] = trim($data);
				}
				
					
				$responses[] = $response;
				$response = [];
				
//				echo 'A' . $this->commandCount . ' OK';
				if(stripos($chunk, 'A' . $this->commandCount . ' OK') !== false){
					$this->lastCommandSuccessful = true;
				}else
				{
					$this->lastCommandSuccessful = false;
				}
				
				$this->lastCommandStatus = $chunk;
				
			}else
			{
				if(substr($chunk, 0, 1) == '*'){
					//untagged response

					if(!empty($data)){
						$response[] = trim($data);

						$responses[] = $response;
						$response = [];

						$data = "";
					}
				}
				
				
				$data .= $chunk;

				//check for literal {<SIZE>}
				$trimmedData = trim($data);

				if(substr($trimmedData,-1,1) == '}' && ($startpos = strrpos($trimmedData, '] {'))){			
					$response[] = trim($data);
					
					$data = "";
					
					$size = substr($trimmedData, $startpos + 3, -1);						
					$response[] = $this->getLiteralDataResponse($size, $streamer);
				}
			}
			
			
		} while ($commandEnd === false);


		return $responses;
	}
	

	/**
	 * The IMAP server can respond with some data when you fetch an attachment 
	 * for example.
	 * 
	 * This data is read into a single response. Optionally a Streamer object can
	 * be passed to stream it to a file or output for memory efficiency.
	 * 
	 * eg.:
	 * 
	 * A12 UID FETCH 13 BODY.PEEK[1.2]
     * * 13 FETCH (UID 13 BODY[1.2] {312}
	 * <html>
	 * .. more data...
	 * </html>
	 * )
	 * 
	 * @param int $size
	 * @param \GO\Modules\Email\Imap\Streamer $streamer
	 * @return string
	 */
	private function getLiteralDataResponse($size, Streamer $streamer = null) {
		
		$max = 8192 > $size ? $size : 8192;
		
		$readLength = 0;
		$data = "";
		
		App::debug('< .. DATA OMITTED FROM LOG ...', 'imap');	
		
		$leftOver = $size;
		do{
			$newMax = $max < $leftOver ? $max : $leftOver;
			
			if($newMax==1){
				//From PHP docs of
				//Reading ends when length - 1 bytes have been read, or a newline 
				//(which is included in the return value), or an EOF (whichever comes first). 
				//If no length is specified, it will keep reading from the stream until it reaches the end of the line.
				$newMax++;
			}
			
			$line = $this->readLine($newMax, false);			
			
			$readLength += strlen($line);
			
			$leftOver = $size - $readLength;
			
			if(isset($streamer)){
				$streamer->put($line);
			}else
			{			
				$data .= $line;
			}
			
		}while ($readLength < $size);			
	
		if(isset($streamer)){
			$streamer->finish();
			return true;
		}else
		{
			return $data;
		}
	}

	private $commandCount = 0;

	private function commandNumber() {
		$this->commandCount++;
		return $this->commandCount;
	}
	
	
	
	/**
	 * Get's an array with two keys. usage and limit in bytes.
	 *
	 *  @todo
	 * @return <type>
	 */
	public function getQuota() {

		if(!$this->has_capability("QUOTA"))
			return false;

		$command = "GETQUOTAROOT \"INBOX\"\r\n";

		$this->send_command($command);
		$res = $this->get_response();
		$status = $this->check_response($res);
		if($status){
			foreach($res as $response){
				if(strpos($response, 'STORAGE')!==false){
					$parts = explode(" ", $response);
					$storage_part = array_search("STORAGE", $parts);
					if ($storage_part>0){
						return array(
							'usage'=>intval($parts[$storage_part+1]),
							'limit'=>intval($parts[$storage_part+2]));
					}
				}
			}
		}
		return false;
	}

}
