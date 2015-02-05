<?php

namespace GO\Core\Auth\Model;

use GO\Core\ArrayConvertableInterface;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Query;
use GO\Core\Modules\Model\Module;
use GO\Modules\Contacts\Model\ContactRole;

/**
 * Permission
 * 
 * This class is for securing models (resources) based on roles.
 * 
 * <p>For example if you want to create an access control list on the Contact
 * model you have to create an ContactRole model that extends {@see AbstractRole}.</p>
 * 
 * <p>The Contact model must have 'ownerUserId' column and a 'roles' relation defined like this:</p>
 * 
 * <code>
 * public static function defineRelations(){
 * 	return array(
 * 		...
 * 
 *      $r->belongsTo('owner', User::className(), 'ownerUserId'),
 * 		$r->hasMany('roles', self::className(), ContactRole::className(), 'contactId')				
 *
 * 		...  
 *
 * 	);
 * 	}
 * </code>
 * 
 * <p>Then you can use the {@see check)} function to check access and 
 * on select queries you can use the {@see findPermitted()} function to find permitted records only.</p>
 * 
 * <p>Examples:</p>
 * <code>
 * 
 * $contact = Contact::findByPk($id);
 * 
 * if(!$contact->permission->check('editAccess')){
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
class Permission implements ArrayConvertableInterface {

	/**
	 * The record there permission apply to
	 * @var AbstractRecord 
	 */
	private $_record;
	private $_userId;

	public function __construct($record) {
		$this->_record = $record;
	}

	public function __get($name) {
		return $this->check($name);
	}

	/**
	 * Change the user to check permissions for.
	 * 
	 * @param int $userId
	 * @return Permission
	 */
	public function forUser($userId) {
		$this->_userId = $userId;

		return $this;
	}
	

	/**
	 * Use this function to set conditions on the findParams so that only
	 * authorized resource models are returned.
	 * 
	 * @param int $resourceKey The primary key of the resource model
	 * @param string $accessName The boolean column in the role model to check. Leave null to disable.
	 * @param int $userId The user to check for. Leave null for the current user
	 */
	public function check($permissionType = null, $userId = null) {

		//always allow for admin
		if (User::current()->isAdmin()) {
			return true;
		}

		if (!isset($userId)) {
			$userId = User::current()->id;
		}		
		
		$relation = self::getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();
		$permissionTypeInt = $roleModelName::permisionTypeNameToValue($permissionType);

		$query = Query::newInstance()
				->joinRelation('users', false);

		$query->where([
			'permissionType' => $permissionTypeInt,
			$roleModelName::resourceKey() => $this->_record->{$this->_record->primaryKeyColumn()},
			'users.userId' => $userId
		]);

		$result = $roleModelName::find($query)->single();

		return $result !== false;
	}

	

	/**
	 * Get an array of all the permissions that a user has.
	 * 
	 * @return array eg ['readAccess' => true, 'editAccess' => false, 'deleteAccess'=>false]
	 */
	public function toArray(array $attributes = []) {

		if ($this->_record->isNew()) {
			return false;
		}
		
		
		$relation = $this->_record->getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();

		$permissionTypes = $roleModelName::permissionTypes();
		

		$return = [];

		//$colCount = count($permissionColumns);

		foreach ($permissionTypes as $name => $value) {
			$return[$name] = false;
		}
		
		$flippedTypes = array_flip($permissionTypes);

		if (!User::current()) {
			return $return;
		}
		
		$userId = User::current()->id;

		$query = Query::newInstance()
				->joinRelation('users', false);
		
		$resourceKey = $roleModelName::resource()->getKey();

		$query->where([
			$resourceKey => $this->_record->{$this->_record->primaryKeyColumn()},
			'users.userId' => $userId
		]);

		$roles = $roleModelName::find($query);

		//$enabledCount = 0;
		foreach ($roles as $role) {
			
			$return[$flippedTypes[$role->permissionType]] = true;
			
//			foreach ($return as $key => $value) {
//
//				if (!$value && $role->{$key}) {
//					$return[$key] = true;
//					$enabledCount++;
//
//					if ($colCount == $enabledCount) {
//						//All permissions enabled so we can stop here
//						return $return;
//					}
//				}
//			}
			
			
		}

		return $return;
	}
}
