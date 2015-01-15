<?php

namespace GO\Core\Db;

/**
 * Enable soft delete
 * 
 * Add this trait to your AbstractRecord based model to enable soft delete. You 
 * need to add a 'deleted' boolean column to your model too to make it work. 
 * Add an index to it too. Example:
 * 
 * <code>
 * ALTER TABLE `contactsContact` ADD `deleted` BOOLEAN NOT NULL DEFAULT FALSE AFTER `id` ,
 * ADD INDEX ( `deleted` ) ;
 * </code>
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait SoftDeleteTrait {
	
	public function delete(){
		
		$this->deleteCheckRestrictions();
		
		$this->deleted = true;
		
		return $this->save() !== false;
	}
	
	
	/**
	 * Delete the record for real
	 * 
	 * @return bool
	 */
	public function deletePermanently(){
		return parent::delete();
	}
	
	public function findWithDeleted($query = null){
		return parent::find($query);
	}
	
	public static function find($query = null) {
		
		if(!isset($query)){
			$query = new Query();
		}
		if(is_array($query)){
			$query = Query::newInstance()->where($query);
		}
		
		$query->andWhere(['!=',['deleted' => true]]);
		
		return parent::find($query);
	}	
}
