<?php
namespace Intermesh\Core;

/**
 * Debugger class. All entries are stored and the view can render them eventually.
 * The JSON view returns them all.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
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

		$this->entries[$section][]=['msg' => var_export($mixed, true), 'time' => $this->getMicroTime() - $this->requestStart];
	}


	/**
	 * Debug SQL statements
	 *
	 * @param string $sql
	 * @param \Intermesh\Core\Db\Query $query
	 * @param array $bindParams
	 */
	public function debugSql($sql, $bindParams=array()){


	
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
		
		
//		echo $sql;

		$this->debug($sql, 'sql');
	}
}