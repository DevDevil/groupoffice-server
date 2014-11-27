<?php

namespace Intermesh\Modules\Auth\Model;

use Intermesh\Modules\Auth\Model\UserRole;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Column;
use Intermesh\Core\Db\RelationFactory;
use PDO;

/**
 * Abstract role link model
 * 
 * This model is for securing models (resources) based on roles.
 * 
 * See {@see Intermesh\Modules\Auth\Model\RecordPermissionTrait} for more information.
 * 
 * @see \Intermesh\Modules\Addressbook\Model\ContactRole
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractRole extends AbstractRecord {

	/**
	 * @return string The column field of the resource this role access list is for.
	 */
	static function resourceKey(){
		throw new Exception("Please implement static function resourceKey in ".get_called_class());
	}
	
	/**
	 * Get an array of permission dependencies.
	 * eg. 
	 * 
	 * <code>
	 * [
	 * 'readAccess' => [], 
	 * 'editAccess' => ['readAccess'], 
	 * 'deleteAccess' => ['readAccess', 'editAccess']
	 * ];</code>
	 * 
	 * When deleteAccess is enabled the model will automatically enable editAccess and readAccess too.
	 * 
	 * This function takes the order of columns in the database. Override it if you need another order.
	 * 
	 * @return array
	 */
	protected static function getPermissionDependencies(){
		$columns = static::getColumns();
		
		$return = [];
		$deps = [];
		foreach($columns as $column){
			if($column->pdoType === PDO::PARAM_BOOL){
				$return[$column->name] = $deps;
				$deps[] = $column->name;
			}
		}
		
		return $return;
	}
	
	/**
	 * 
	 * @return Column
	 */
	public static function getPermissionColumns(){
		$columns = static::getColumns();
		
		$return = [];
		foreach($columns as $column){
			if($column->pdoType === PDO::PARAM_BOOL){
				$return[] = $column;
			}
		}
		
		return $return;
	}
	
	private function _checkPermissionBooleans(){
		$deps = $this->getPermissionDependencies();
		
		foreach($deps as $permissionColumn => $colDeps){			
			if($this->{$permissionColumn}){
				foreach($colDeps as $colDep){
					$this->{$colDep} = true;
				}
			}
		}
	}
	
	public function save() {
		
		$this->_checkPermissionBooleans();
				
		return parent::save();
	}

	public static function primaryKeyColumn() {
		return [static::resourceKey(), 'roleId'];
	}

	protected static function defineRelations(RelationFactory $r) {
		return array(
			$r->hasMany('users', UserRole::className(), 'roleId', 'roleId')
		);
	}
}
