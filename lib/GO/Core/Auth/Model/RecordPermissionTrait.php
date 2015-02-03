<?php
namespace GO\Core\Auth\Model;

use Exception;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;
use GO\Core\Exception\Forbidden;
use PDO;

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
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait RecordPermissionTrait {
	
	private $_permission = null;
	
	public function permission() {
		if($this->_permission === null)
			$this->_permission = new Permission($this);
		return $this->_permission;
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
	 * Get an array of all the permissions that a user has.
	 * 
	 * @return array eg ['readAccess' => true, 'editAccess' => false, 'deleteAccess'=>false]
	 */
	public function getPermissions(){
//		if (!isset($userId)) {			
			
//		}
		if($this->isNew()){
			return false;
		}
		
		$relation = self::getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();
		
		$permissionColumns = $roleModelName::getPermissionColumns();
		
		$return = [];
		
		$colCount = count($permissionColumns);
		
		foreach($permissionColumns as $col){
			$return[$col->name] = false;
		}
		
		if(User::current()) {
			$userId = User::current()->id;

			$query = Query::newInstance()
					->joinRelation('users', false);

			$query->where([
				$roleModelName::resourceKey() => $this->{$this->primaryKeyColumn()},
				'users.userId' => $userId
			]);

			$roles = $roleModelName::find($query);

			$enabledCount = 0;
			foreach($roles as $role){
				foreach($return as $key => $value) {

					if(!$value && $role->{$key}){
						$return[$key] = true;
						$enabledCount++;

						if($colCount == $enabledCount){
							//All permissions enabled so we can stop here
							return $return;
						}							
					}				
				}
			}
		}
		
		return $return;
	}
	
	/**
	 * Use this function to set conditions on the Query so that only
	 * permitted models are returned.
	 * 
	 * @param Query $query The findParams object to apply the conditions to
	 * @param string $accessName The boolean column in the role model to check. Leave null to disable.
	 * @param int $userId The user to check for. Leave null for the current user
	 * 
	 * @return Query
	 */
	public static function getPermissionsQuery(Query $query = null, $accessName = null, $userId = null){
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
			"  WHERE `t`.`id` = `roles`.`".$roleModelName::resourceKey()."` AND `users`.`userId` = :userId";
		
		if (isset($accessName)) {			
			$subQuery .= " AND roles.`".$accessName."` = 1";			
		}
		
		$subQuery .= " LIMIT 0,1\n)";
		
		$query->where($subQuery)
			->addBindParameter(':userId', $userId, PDO::PARAM_INT);
		
		return $query;				
	}

}
