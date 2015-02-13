<?php
namespace GO\Core\Auth\Model;

use GO\Core\ArrayConvertableInterface;
use GO\Core\Auth\Model\Permissions;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Query;


/**
 * Permissions object
 * 
 * This model represents the permissions a user has for an object. It merges
 * all permissions of the roles the user has into one object.
 * 
 * <code>
 *
 * if(!$band->permissions->has(Band::PERMISSION_READ)){
 *	throw new Forbidden();
 * }
 * 
 * </code>
 * 
 * This object will convert to an array as wel for the API to return permissions 
 * as well.
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Permissions implements ArrayConvertableInterface {

	/**
	 * The record there permission apply to
	 * @var AbstractRecord 
	 */
	private $_record;
	private $_userId;

	public function __construct($record) {
		$this->_record = $record;
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
	
	
	private function getUserId(){
		if (!isset($this->_userId)) {
			$this->_userId = User::current()->id;
		}	
		
		return $this->_userId;
	}
	

	/**
	 * 
	 */
	public function has($permissionType = null) {

		//always allow for admin
		if (User::current()->isAdmin()) {
			return true;
		}

		$userId = $this->getUserId();
			
		
		$relation = self::getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();
		
		$query = Query::newInstance()
				->joinRelation('users', false);

		$query->where([
			'permissionType' => $permissionType,
			$roleModelName::resourceKey() => $this->_record->{$this->_record->primaryKeyColumn()},
			'users.userId' => $userId
		]);

		$result = $roleModelName::find($query)->single();

		return $result !== false;
	}
	
	
	/**
	 * Check if the user has permissions to manage the permissions
	 * 
	 * The current user should be admin or stored in the column ownerUserId
	 * 
	 * @return boolean
	 */
	public function hasManagePermission() {		
		
		$user = User::findByPk($this->getUserId());
		
		if($user->isAdmin()){
			return true;
		}
		
		$resourceName = static::resource()->getName();
		
		$resource = $this->$resourceName;
		
		if($resource && $resource->getColumn('ownerUserId')) {
			if($resource->ownerUserId == $user->id) {				
				return true;
			}
		}
		
		return false;			
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
		$permissionTypes = $this->_record->permissionTypes();		

		$return = ['manage' => $this->hasManagePermission()];
		
		foreach ($permissionTypes as $name => $value) {
			$return[$name] = false;
		}
		
		$flippedTypes = array_flip($permissionTypes);

		if (!User::current()) {
			return $return;
		}
		
		$userId = $this->getUserId();

		$query = Query::newInstance()
				->joinRelation('users', false)
				->groupBy(['permissionType']);
		
		$resourceKey = $roleModelName::resource()->getKey();

		$query->where([
			$resourceKey => $this->_record->{$this->_record->primaryKeyColumn()},
			'users.userId' => $userId
		]);

		$roles = $roleModelName::find($query);

		//$enabledCount = 0;
		foreach ($roles as $role) {			
			$return[$flippedTypes[$role->permissionType]] = true;			
		}

		return $return;
	}
}
