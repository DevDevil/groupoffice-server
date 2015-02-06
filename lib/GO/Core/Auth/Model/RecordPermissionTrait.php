<?php
namespace GO\Core\Auth\Model;

use Exception;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;
use GO\Modules\Contacts\Model\ContactRole;
use PDO;
use ReflectionClass;

/**
 * RecordPermissionTrait
 * 
 * This trait is for securing models (resources) based on roles.
 * 
 * <p>For example if you want to create an access control list on the Contact
 * model you have to create an ContactRole model that extends {@see AbstractRole}.</p>
 * 
 * <p>The Contact model must have 'ownerUserId' column and a 'roles' relation defined like this:</p>
 * 
 * <code>
 * public static function defineRelations(){
 * 		...
 * 
 *      self::belongsTo('owner', User::className(), 'ownerUserId'),
 * 		self::hasMany('roles', self::className(), ContactRole::className(), 'contactId')				
 *
 * 		...  
 * 	}
 * </code>
 * 
 * <p>Then you can use the {@see checkPermission()} function to check access and 
 * on select queries you can use the {@see findPermitted()} function to find permitted records only.</p>
 * 
 * <p>Examples:</p>
 * <code>
 * 
 * $contact = Contact::findByPk($id);
 * 
 * if(!$contact->checkPermission('editAccess')){
 *   throw new Forbidden();
 * }
 * 
 * 
 * $permittedContacts = Contact::findPermitted($query);
 * 
 * </code>
 *
 * 
 * @see ContactRole
 * 
 * @property $permissions {@see RecordPermissionTrait::getPermissions()}
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait RecordPermissionTrait {
	
	private $_permission;
	
	/**
	 * Get's a permissions object to check access
	 * 
	 * @return Permission
	 */
	public function getPermissions() {
		if(!isset($this->_permission)) {
			$this->_permission = new Permission($this);
		}
		return $this->_permission;
	}	
	
	
	private static $_permissionTypes;
	
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
	public function permissionTypes(){
		
		$className = $this->className();
		
		if(!isset(self::$_permissionTypes[$className])){
			$reflectionClass = new ReflectionClass($this);

			self::$_permissionTypes[$className] = [];
			foreach($reflectionClass->getConstants() as $name => $value){
				if(substr($name, 0, 11) == 'PERMISSION_') {
					self::$_permissionTypes[$className][strtolower(substr($name,11))] = $value;
				}
			}
		}
		
		return self::$_permissionTypes[$className];
		
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
	public function permisionTypeNameToValue($name){
		
		$types = $this->permissionTypes();
		
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
	 * Use this function to set conditions on the Query so that only
	 * permitted models are returned.
	 * 
	 * @param Query $query The findParams object to apply the conditions to
	 * @param string $accessName The boolean column in the role model to check. Leave null to disable.
	 * @param int $userId The user to check for. Leave null for the current user
	 * 
	 * @return static[]
	 */
	public static function findPermitted(Query $query = null, $accessName = null, $userId = null) {
		
		$permissionQuery = self::getPermissionsQuery($query, $accessName, $userId);

		return static::find($permissionQuery);
	}
	
	/**
	 * 
	 * @return Relation
	 * @throws Exception
	 */
	public static function getRolesRelation(){
		$relation = self::getRelation('roles');
		
		if(!$relation){
			throw new Exception('"'.$this->className().'" must have a "roles" relation is it uses RecordPermissionTrait');
		}
		
		return $relation;
	}
	

	/**
	 * Use this function to set conditions on the Query so that only
	 * permitted models are returned.
	 * 
	 * @param Query $query The findParams object to apply the conditions to
	 * @param string $permissionType The boolean column in the role model to check. Leave null to disable.
	 * @param int $userId The user to check for. Leave null for the current user
	 * 
	 * @return Query
	 */
	public static function getPermissionsQuery(Query $query = null, $permissionType = null, $userId = null){
		if(!isset($query)){
			$query = new Query();
		}
		
		$relation = self::getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();

		
		
		//Check roles with subquery as it was faster in my test with 10.000 contacts
		
		/*
		  SELECT SQL_NO_CACHE t.`id`, t.`firstName`, t.`middleName`, t.`lastName`, t.`photoFilePath` FROM `addressbookContact` t
		  INNER JOIN `addressbookContactRole` roles ON `t`.`id` = `roles`.`contactId`
		  INNER JOIN `authUserRole` users ON `roles`.`roleId` = `users`.`roleId`
		  WHERE `users`.`userId` = 1 GROUP BY `t`.`id` ORDER BY `firstName` ASC


		  //SEEMS FASTER:

		  SELECT SQL_NO_CACHE t.`id`, t.`firstName`, t.`middleName`, t.`lastName`, t.`photoFilePath` FROM `addressbookContact` t
		  WHERE EXISTS (
		  SELECT roles.roleId FROM  `addressbookContactRole` roles
		  INNER JOIN `authUserRole` users ON `roles`.`roleId` = `users`.`roleId`
		  WHERE `t`.`id` = `roles`.`contactId`  AND`users`.`userId` = 1 LIMIT 0,1
		  )
		  ORDER BY `firstName` ASC
		 */

		if (!isset($userId)) {
			$userId = User::current()->id;
		}
		
		$subQuery = "EXISTS (\n".
			"  SELECT roles.roleId FROM  `".$roleModelName::tableName()."` roles\n".
			"  INNER JOIN `authUserRole` users ON `roles`.`roleId` = `users`.`roleId`\n".
			"  WHERE `t`.`id` = `roles`.`".$roleModelName::resource()->getKey()."` AND `users`.`userId` = :userId";
		
		if (isset($permissionType)) {			
			$subQuery .= " AND roles.permissionType = :permissionType";			
			$query->addBindParameter(':permissionType', $permissionType);
		}
		
		$subQuery .= " LIMIT 0,1\n)";
		
		$query->where($subQuery)
			->addBindParameter(':userId', $userId, PDO::PARAM_INT);
		
		return $query;				
	}

}
