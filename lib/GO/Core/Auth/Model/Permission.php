<?php
namespace GO\Core\Auth\Model;

use Exception;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;
use PDO;

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
class Permission {
	
	/**
	 * The record there permission apply to
	 * @var AbstractRecord 
	 */
	private $_record;
	
	public function __construct($record) {
		$this->_record = $record;
	}
	
	public function __get($name) {
		return $this->check($name);
	}
	
	/**
	 * Returns true if the user is allowed to create new instances of this model.
	 * By default this returns true if the user has createAccess on the module. 
	 * Override this function if you need other permissions.
	 * 
	 * @return bool
	 */
	protected function canCreate($userId){
		//retun module permission
		
		$module = $this->_record->getModule();	
		return $module::model()->check('createAccess', $userId);	
	}
	
	/**
	 * Use this function to set conditions on the findParams so that only
	 * authorized resource models are returned.
	 * 
	 * @param int $resourceKey The primary key of the resource model
	 * @param string $accessName The boolean column in the role model to check. Leave null to disable.
	 * @param int $userId The user to check for. Leave null for the current user
	 */
	public function check($accessName = null, $userId = null) {
		
	
		
		//always allow for admin
		if(User::current()->isAdmin()) {
			return true;
		}
		
		if (!isset($userId)) {
			$userId = User::current()->id;
		}		
		
		if($this->_record->isNew()){
			return $this->canCreate($userId);
		}

		$relation = self::getRolesRelation();
		$roleModelName = $relation->getRelatedModelName();

		$query = Query::newInstance()
				->joinRelation('users', false);

		$query->where([
			$accessName => true,
			$roleModelName::resourceKey() => $this->_record->{$this->_record->primaryKeyColumn()},
			'users.userId' => $userId
		]);
				
		$result = $roleModelName::find($query)->single();

		return $result !== false;
	}
	

	
	/**
	 * Check if the current logged in user may manage permissions
	 * 
	 * @return bool
	 */
	public function canManage(){
		
		if(!User::current()){
			return false;
		}
		return isset($this->_record->ownerUserId) ? $this->_record->ownerUserId == User::current()->id : User::current()->id == 1;
	}
	
}
