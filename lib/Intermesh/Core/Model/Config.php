<?php

namespace Intermesh\Core\Model;

use Intermesh\Core\Db\AbstractRecord;

/**
 * Config model
 * 
 * Model to store system wide configuration values
 * 
 * @property mixed $value;
 * @property string $name;
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Config extends AbstractRecord {

	public static function tableName() {
		return 'coreConfig';
	}
	
	public static function primaryKeyColumn() {
		return "name";
	}
	
	
	public function setValue($value){
		$this->_value = serialize($value);
	}
	
	public function getValue(){
		return unserialize($this->_value);
	}

	

}
