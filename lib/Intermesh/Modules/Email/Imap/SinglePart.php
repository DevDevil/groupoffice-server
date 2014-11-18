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

	/**
	 * Content ID
	 * 
	 * @var string 
	 */
	public $id;
	
	public $description;
	
	/**
	 * Encoding type
	 * 
	 * eg. base64 or quoted-printable
	 * 
	 * @var string 
	 */
	public $encoding;
	
	/**
	 * Size in bytes
	 * 
	 * @var int 
	 */
	public $size;
	
	public $lines;
	
	public $md5;
	
	/**
	 * Disposition
	 * 
	 * eg.
	 * 
	 * ['attachment' => ['filename' => 'Doc.pdf']]
	 * 
	 * @var array 
	 */
	public $disposition;
	
	public $language;
	
	public $location;
	

	
	public function __construct(Message $message, $partNumber, array $struct) {
		
		$this->message = $message;
		
		$this->partNumber = $partNumber;
		
		$atts = array('type', 'subtype', 'params', 'id', 'description', 'encoding',
						'size', 'lines', 'disposition', 'md5', 'language', 'location');
		
		for($i = 0, $c = count($struct); $i < $c; $i++) {
			
			if($atts[$i] == 'size'){
				$struct[$i] = intval($struct[$i]);
			}
			
			$this->{$atts[$i]} = $this->_parseValue($struct[$i]);
		}
		
		if(isset($this->id)) {
			$this->id = trim($this->id, '<> ');
		}
	}
	
	/**
	 * Get the filename
	 * 
	 * Uses the part name or content disposition
	 * 
	 * @return boolean
	 */
	public function getFilename(){
		if(!empty($this->params['name'])){
			return Utils::mimeHeaderDecode($this->params['name']);
		}else if(isset($this->disposition)){
			$props = array_shift($this->disposition);
			
			if($props['filename']){
				return Utils::mimeHeaderDecode($props['filename']);
			}
		}
		
		return false;
	}
	
//	public function getUrl(){
//		
//	}
	
	
	private function _parseValue($v){
		if(is_array($v)){

			$value = [];
			for($n = 0, $c2 = count($v); $n < $c2; $n++){
				$value[$v[$n++]] = $this->_parseValue($v[$n]);					
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
	
	
	/**
	 * Stream data to a file pointer
	 * 
	 * @param $filePointer If none is given the browser output will be used
	 */
	public function output($filePointer = null){
		
		
		if(!isset($filePointer)){
			
			$sendHeaders = true;
		
			$filePointer = fopen("php://output",'w');
		}else
		{
			$sendHeaders = false;
		}
		
		if(!is_resource($filePointer)){
			throw new Exception("Invalid file pointer given");
		}
		
		if($sendHeaders){
			header('Content-Type: '.$this->type.'/'.$this->subtype);
			header('Content-Disposition: inline; filename='.$this->getFilename());
		}		
		
		$streamer = new Streamer($filePointer, $this->encoding);
		
		$this->getData(true, $streamer);
	}	
	
	
	public function toArray(array $attributes = ['filename', 'encoding','size','partNumber', 'id']) {
		return parent::toArray($attributes);
	}
	
	
}