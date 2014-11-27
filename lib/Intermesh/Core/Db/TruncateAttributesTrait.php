<?php
namespace Intermesh\Core\Db;

use Intermesh\Core\Util\String;

/**
 * Use this trait in an AbstractRecord if you want attributes to be truncated automatically.
 * This can be useful on importing and you don't want it to fail on such errors.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait TruncateAttributesTrait {
	public function setAttribute($name, $value){
		
		if(is_string($value)){
			$value = String::cutString($value, $this->getColumn($name)->length, false, "");
		}
		
		parent::setAttribute($name, $value);
	}
}