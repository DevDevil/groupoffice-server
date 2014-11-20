<?php

namespace Intermesh\Modules\Email\Imap;

use Exception;
use Intermesh\Modules\Email\Imap\Message;

/**
 * SinglePart class
 * 
 * A single part can be the html body or an attachment part
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class SinglePart extends AbstractPart{
	
	

	
	public function __construct(Message $message, $partNumber, array $struct) {
		
		$this->message = $message;
		
		$this->partNumber = $partNumber;
		
		$atts = array('type', 'subtype', 'params', 'id', 'description', 'encoding',
						'size', 'lines', 'disposition', 'md5', 'language', 'location');
		
		
		for($i = 0, $c = count($struct); $i < $c; $i++) {
			
			if($atts[$i] == 'size'){
				$struct[$i] = intval($struct[$i]);
			}
			
			if($atts[$i] == 'subtype' && $struct[$i]=='rfc822'){
				//in this case the sub structure is also passed. We ignore this.
				/*
				 * [
                    "message",
                    "rfc822",
                    "NIL",
                    "NIL",
                    "NIL",
                    "7bit",
                    "1518",
                    [
                        "Thu, 03 Jan 2013 11:19:39 +0100",
                        "Complete your Group-Office trial installation",
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "Group-Office",
                                "NIL",
                                "mschering",
                                "intermesh.nl"
                            ]
                        ],
                        [
                            [
                                "demo demo",
                                "NIL",
                                "demo",
                                "demo.com"
                            ]
                        ],
                        "NIL",
                        "NIL",
                        "NIL",
                        "<1357208379.50e55b3b449b4@server.trials.group-office.com>"
                    ],
                    [
                        "text",
                        "plain",
                        [
                            "charset",
                            "utf-8"
                        ],
                        "NIL",
                        "NIL",
                        "quoted-printable",
                        "729",
                        "28",
                        "NIL",
                        "NIL",
                        "NIL",
                        "NIL"
                    ],
                    "44",
                    "NIL",
                    "NIL",
                    "NIL",
                    "NIL"
                ]
				 */
				$c = 6;				
			}
			
			$this->{$atts[$i]} = $this->_parseValue($struct[$i]);
		}
		
		if(isset($this->id)) {
			$this->id = trim($this->id, '<> ');
		}
	}
	
	
	
//	public function getUrl(){
//		
//	}
	
	
	private function _parseValue($v){
		if(is_array($v)){

			$value = [];
			for($n = 0, $c2 = count($v); $n < $c2; $n++){
				
				$key = $v[$n++];
				
				if(!is_string($key)){
					throw new Exception("Something wrong with the structure.");
				}
				
				$value[$key] = isset($v[$n]) ? $this->_parseValue($v[$n]) : null;					
			}
			
			return $value;

		}  else {			
			return $v != 'NIL' ? $v : null;
		}
	}
	
	/**
	 * Get's the data decoded
	 * 
	 * @return string
	 */
	public function getDataDecoded(){
		switch($this->encoding){
			case 'base64':
					return base64_decode($this->getData());
				
			case 'quoted-printable':
					return quoted_printable_decode($this->getData());
			default:
				
				return $this->getData();
		}
	}
	
	

	
	
	public function toArray(array $attributes = ['filename', 'encoding','size','partNumber', 'id']) {
		return parent::toArray($attributes);
	}
	
	
}