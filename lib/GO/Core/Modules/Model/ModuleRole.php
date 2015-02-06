<?php
namespace GO\Core\Modules\Model;

use GO\Core\Auth\Model\AbstractRole;
use ReflectionClass;

/**
 * The table that holds permissions for roles to modules
 * 
 * @property int $roleId
 * @property int $moduleId
 * @property int $permissionType
 */
class ModuleRole extends AbstractRole{	
	
	
	
	protected static function defineResource() {
		return self::belongsTo('module', Module::className(), 'moduleId');	
	}
	
//	private static $_permissionTypes;
//	
//	
//	/**
//	 * Get all permission types.
//	 * 
//	 * Permission types can be set on this class as constants prefixed with "PERMISSION_".
//	 * 
//	 * The types will be returned to the API in lower case and without the prefix.
//	 * 
//	 * eg. "PERMISSION_READ" will become "read" on the API.
//	 * 
//	 * For example:
//	 *
//	 * <code> 
//	 * const PERMISSION_READ = 0;
//	 * const PERMISSION_WRITE = 1;
//	 * const PERMISSION_DELETE = 2;
//	 * </code>
//	 * 	 
//	 * @return array Name as key and value as value. eg. ['read' => 0, 'write'= => 1, 'delete' => 2]
//	 */
//	public static function permissionTypes(){
//		
//		$className = get_called_class();
//		
//		if(!isset(static::$_permissionTypes[$className])){
//			static::$_permissionTypes[$className] = parent::permissionTypes();		
//			
//			$reflectionClass = new ReflectionClass(static::resource()->getRelatedModelName());
//			foreach($reflectionClass->getConstants() as $name => $value){
//				if(substr($name, 0, 11) == 'PERMISSION_') {
//					static::$_permissionTypes[$className][strtolower(substr($name,11))] = $value;
//				}
//			}
//		}
//		
//		return static::$_permissionTypes[$className];
//		
//	}
}