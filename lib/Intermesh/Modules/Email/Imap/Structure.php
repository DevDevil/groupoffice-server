<?php

namespace Intermesh\Modules\Email\Imap;

/**
 * Message body structure
 * 
 * Reads the structure and turns it into SinglePart and MultiPart objects
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class Structure {

	/**
	 * The IMAP message it belongs to
	 * 
	 * @var Message 
	 */
	private $message;
	
	/**
	 * The parts of the structure.
	 * 
	 * Parts can have sub parts too.
	 * 
	 * @var AbstractPart[] 
	 */
	public $parts;
	
	public function __construct(Message $message) {
		$this->message = $message;	
		
		$struct = $this->getStructure();	
				
		if(is_array($struct[0])){
			$this->parts[] = new MultiPart($message,"", $struct);
		}else
		{
			$this->parts[] = new SinglePart($message, "1", $struct);
		}
		
	}

	private function parseStructure($structStr) {

		$structStr = substr(trim($structStr), 1, -1);

		//makes parsing easier
		$structStr = str_replace(')(', ') (', $structStr);

//		var_dump($structStr);

		$array = str_split($structStr, 1);

		$tokens = [];

		$inQuotes = false;

		$subLevel = 0;

		$buffer = '';

		for ($i = 0, $c = count($array); $i < $c; $i++) {

			$char = $array[$i];

			switch ($char) {
				case '"':
					
					if($subLevel == 0) {
						if (!$inQuotes) {
							$inQuotes = true;
						} else {
							$inQuotes = false;
						
						}					
					}else
					{
						$buffer .= $char;
					}
					
					
					
					break;

				case '(':
					if(!$inQuotes){
						$subLevel++;
					}
					$buffer .= $char;
					break;

				case ')':
					if(!$inQuotes){
						$subLevel--;
					}

//						if($subLevel > 0) {
					$buffer .= $char;
//						}

					break;

				case ' ':
					if ($subLevel == 0 && !$inQuotes) {
						$tokens[] = $buffer;
						$buffer = "";
					} else {
						$buffer .= $char;
					}
					break;

				default:
					$buffer .= $char;
					break;
			}
		}

		$tokens[] = $buffer;
		$buffer = "";

		for ($i = 0, $c = count($tokens); $i < $c; $i++) {
			if (substr($tokens[$i], 0, 1) == '(') {
				$tokens[$i] = $this->parseStructure($tokens[$i]);
			}
		}


		return $tokens;
	}

	private function getStructure() {
		
		$conn = $this->message->mailbox->connection;
		
		if(!$this->message->mailbox->selected) {
			$this->message->mailbox->select();
		}

	
		$struct = array();
		$command = "UID FETCH " . $this->message->uid . " BODYSTRUCTURE";
		$conn->sendCommand($command);
		$response = $conn->getResponse();

		$structStr = $response[0][0];

		$startpos = strpos($structStr, "BODYSTRUCTURE");

		$struct = $this->parseStructure(substr($structStr, $startpos + 14, -1));


		return $struct;
		
	}
	
	/**
	 * Check if the message has an alternative html body
	 * 
	 * @param array $parts Used internally for recursion
	 * @return boolean
	 */
	public function hasAlternativeBody($parts = null){		
		
		if(!isset($parts)){
			$parts = $this->parts;
		}	

		foreach($parts as $part){
			if($part instanceof SinglePart){
				return false;
			}else
			{
				if($part->type == 'alternative'){
					return true;
				}else
				{
					return $this->hasAlternativeBody($part->parts);
				}
			}
		}	
	}
	
	/**
	 * Get part by number
	 * 
	 * @param string $partNumber
	 * @return AbstractPart|boolean
	 */
	public function getPart($partNumber){
		$parts = $this->findParts(['partNumber' => $partNumber]);
		
		if(!isset($parts[0])){
			return false;
		}  else {
			return $parts[0];
		}
	}
	
	/**
	 * Find parts by type
	 * 
	 * @param array $props Key value of part properties that must match
	 * @param array $parts
	 * @return SinglePart[]
	 */
	public function findParts(array $props, $parts = null) {	
		
		$results  = [];
		
		if(!isset($parts)){
			$parts = $this->parts;		
		}
		
		foreach($parts as $part){
			
			$match = true;
			foreach($props as $name => $value){
				if(!isset($part->$name) || $part->$name != $value){
					$match = false;
					break;
				}
			}
			
			
			if($match){
				$results[] = $part;
			}
			
			if($part instanceof MultiPart){
				$results = array_merge($results, $this->findParts($props, $part->parts));				
			}
		}
		return $results;
	}

}
