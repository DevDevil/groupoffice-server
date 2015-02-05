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
	
	private static $_permissionTypes;
	
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
	
	
	/**
	 * Get all permission types.
	 * 
	 * Permission types can be set on this class as constants prefixed with "PERMISSION_".
	 * 
	 * The types will be returned to the API in lower case and without the prefix.
	 * 
	 * eg. "PERMISSION_READ" will become "read" on the API.
	 * 
	 * For example:
	 *
	 * <code> 
	 * const PERMISSION_READ = 0;
	 * const PERMISSION_WRITE = 1;
	 * const PERMISSION_DELETE = 2;
	 * </code>
	 * 	 
	 * @return array Name as key and value as value. eg. ['read' => 0, 'write'= => 1, 'delete' => 2]
	 */
	public static function permissionTypes(){
		
		$className = get_called_class();
		
		if(!isset(self::$_permissionTypes[$className])){
			$reflectionClass = new ReflectionClass($className);

			self::$_permissionTypes[$className] = [];
			foreach($reflectionClass->getConstants() as $name => $value){
				if(substr($name, 0, 11) == 'PERMISSION_') {
					self::$_permissionTypes[$className][strtolower(substr($name,11))] = $value;
				}
			}
		}
		
		return self::$_permissionTypes[$className];
		
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
	protected function setPermissionType($value){
		
		$converted = self::permisionTypeNameToValue($value);		
	
		$this->setAttribute('permissionType', $converted);
	}
	
	public static function findByPk($pk) {
		
		$value = self::permisionTypeNameToValue($pk['permissionType']);		
		
		$pk['permissionType'] = $value;
		
		return parent::findByPk($pk);
	}
	
	/**
	 * 
	 * Convert API friendly permission type name to it's int value.
	 * 
	 * Converts "read" to PERMISSION_READ = 0 for example
	 * 
	 * {@see AbstractRole::permissionTypes()}
	 * 
	 * @param string $name
	 * @return int|false Returns false if the name does not exist.
	 */
	public static function permisionTypeNameToValue($name){
		
		$types = static::permissionTypes();
		
		if(is_int($name)){
			if(!in_array($name, $types)){
				throw new Exception("Tried to set invalid permission type '".$name."'. These are allowed: ".implode(', ', array_keys($types)));
			}
			return $name;
		}		
		
		if(!isset($types[$name])){
			throw new Exception("Tried to set invalid permission type '".$name."'. These are allowed: ".implode(', ', array_keys($types)));
		}
		
		return $types[$name];
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
		
		return parent::save();
	}
	
	
	public function delete() {
		
		if(!$this->_canManagePermission()){
			throw new Forbidden();
		}
		
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
