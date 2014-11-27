<?php
namespace Intermesh\Core;

/**
 * Debugger class. All entries are stored and the view can render them eventually.
 * The JSON view returns them all.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Debugger extends AbstractObject{

	/**
	 * Sets the debugger on or off
	 * @var boolean
	 */
	public $enabled=false;


	/**
	 * The debug entries as strings
	 * @var array
	 */
	public $entries = [];
	
	
	/**
	 * Start of the request in Milliseconds since epoch
	 * 
	 * @var int
	 */
	public $requestStart;
	
	
	public function init(){
		if($this->enabled){
			$this->requestStart = $this->getMicroTime();			
		}
	}
	
	private function getMicroTime(){
		list ($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	/**
	 * Add a debug entry. Objects will be converted to strings with var_export();
	 *
	 * @param mixed $mixed
	 * @param string $section
	 */
	public function debug($mixed, $section='general'){

		if(!isset($this->entries[$section])){
			$this->entries[$section]=array();
		}

		$this->entries[$section][] = var_export($mixed, true);
	}
	
	/**
	 * Add a message that notes the time since the request started in milliseconds
	 * 
	 * @param string $message
	 */
	public function debugTiming($message){
		$this->debug(($this->getMicroTime() - $this->requestStart).'ms '.$message, 'timing');				
	}


	/**
	 * Debug SQL statements
	 *
	 * @param string $sql
	 * @param \Intermesh\Core\Db\Query $query
	 * @param array $bindParams
	 */
	public function debugSql($sql, $bindParams = []){
	
		//sort so that :param1 does not replace :param11 first.
		arsort($bindParams);

		foreach($bindParams as $key=>$value){

			if(!isset($value)){
				$queryValue = "NULL";
			}elseif(is_numeric($value)){
				$queryValue = $value;
			}else
			{
				$queryValue = '"'.$value.'"';
			}

			$sql = preg_replace('/'.$key.'([^0-9])?/', $queryValue.'$1', $sql);
		}

		$this->debug('['.($this->getMicroTime() - $this->requestStart).'ms] '.$sql, 'sql');
	}
}