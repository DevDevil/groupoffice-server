<?php

namespace GO\Core\Auth\Model;

use Exception;
use GO\Core\Auth\Model\UserRole;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Relation;
use GO\Core\Exception\Forbidden;
use ReflectionClass;

/**
 * Abstract role link model
 * 
 * This model is for securing models (resources) based on roles.
 * 
 * See {@see GO\Core\Auth\Model\RecordPermissionTrait} for more information.
 * 
 * @see \GO\Modules\Addressbook\Model\ContactRole
 * 
 * @property int $roleId The role ID peorpery
 * @property string $permissionType {@see AbstractRole::permissionTypes()}
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractRole extends AbstractRecord {
	
	
	
	/**
	 *
	 * @var Relation	
	 */
	private static $_resource;

	/**
	 * @return Relation Returns a belongsTo relation that points to the resource these permissions are for.
	 */
	protected static function defineResource(){
		throw new Exception("Please implement static function resource() in ".get_called_class());
	}
	
	
	/**
	 * Get's the belongsTo relation that points to the model to protect.
	 * 
	 * eg. BandRole::resource() returns relation to Band.
	 * 	
	 * @return Relation
	 * @throws Exception
	 */
	public static function resource(){
		if(!isset(self::$_resource)){			
			//populate relations
			static::defineRelations();			
		}
		
		return self::$_resource;
	}
	
	

	
//	/**
//	 * Get an array of permission dependencies.
//	 * eg. 
//	 * 
//	 * <code>
//	 * [
//	 * 'readAccess' => [], 
//	 * 'editAccess' => ['readAccess'], 
//	 * 'deleteAccess' => ['readAccess', 'editAccess']
//	 * ];</code>
//	 * 
//	 * When deleteAccess is enabled the model will automatically enable editAccess and readAccess too.
//	 * 
//	 * This function takes the order of columns in the database. Override it if you need another order.
//	 * 
//	 * @return array
//	 */
//	protected static function getPermissionDependencies(){
//		$columns = static::getColumns();
//		
//		$return = [];
//		$deps = [];
//		foreach($columns as $column){
//			if($column->pdoType === PDO::PARAM_BOOL){
//				$return[$column->name] = $deps;
//				$deps[] = $column->name;
//			}
//		}
//		
//		return $return;
//	}
//	
//	/**
//	 * 
//	 * @return Column
//	 */
//	public static function getPermissionColumns(){
//		$columns = static::getColumns();
//		
//		$return = [];
//		foreach($columns as $column){
//			if($column->pdoType === PDO::PARAM_BOOL){
//				$return[] = $column;
//			}
//		}
//		
//		return $return;
//	}
//	
//	private function _checkPermissionBooleans(){
//		$deps = $this->getPermissionDependencies();
//		
//		foreach($deps as $permissionColumn => $colDeps){			
//			if($this->{$permissionColumn}){
//				foreach($colDeps as $colDep){
//					$this->{$colDep} = true;
//				}
//			}
//		}
//	}
//	
//	
	
	/**
	 * Setter to convert permissionType from string "read" to PERMISSION_READ => 0 integer.
	 * 
	 * @param string $value
	 */
//	protected function setPermissionType($value){
//		
//		$converted = self::permisionTypeNameToValue($value);		
//	
//		$this->setAttribute('permissionType', $converted);
//	}
	
	
	private function _convertPermissionType(){
		if(is_string($this->permissionType)){
			$this->permissionType = $this->{$this->resource()->getName()}->permisionTypeNameToValue($this->permissionType);		
		}
	}
	
	
	
	public static function findByPk($pk) {
		
		
		$resourceClassName = static::resource()->getRelatedModelName();
		$resourceKey = static::resource()->getKey();		
		$resource = $resourceClassName::findByPk($pk[$resourceKey]);
		
		$value = $resource->permisionTypeNameToValue($pk['permissionType']);		
		
		$pk['permissionType'] = $value;
		
		return parent::findByPk($pk);
	}
	
	
	
	/**
	 * Check if the current logged in user may manage permissions
	 * 
	 * The current user should be admin or stored in the column ownerUserId
	 * 
	 * @return boolean
	 */
	private function _canManagePermission(){
		if(!User::current()) {
			return false;
		}
		
		if(User::current()->isAdmin()){
			return true;
		}
		
		$resourceName = static::resource()->getName();
		
		$resource = $this->$resourceName;
		
		if($resource && $resource->getColumn('ownerUserId')) {
			if($resource->ownerUserId == User::current()->userId) {				
				return true;
			}
		}
		
		return false;			
	}
	
	public function save() {
		
		if(!$this->_canManagePermission()){
			throw new Forbidden();
		}
		
		$this->_convertPermissionType();
		
		return parent::save();
	}
	
	
	public function delete() {
		
		if(!$this->_canManagePermission()){
			throw new Forbidden();
		}
		
		
		$this->_convertPermissionType();
		
		return parent::delete();
	}
	
	public static function primaryKeyColumn() {		
		return [static::resource()->getKey(), 'roleId', 'permissionType'];
	}

	protected static function defineRelations() {	
		
		static::hasMany('users', UserRole::className(), 'roleId', 'roleId');
		
		self::$_resource = static::defineResource();

		if(!self::$_resource->isA(Relation::TYPE_BELONGS_TO)){
			throw new \Exception(static::className()."::resource() should return a belongsTo relation pointing to the resource to protect.");
		}
	}
}
