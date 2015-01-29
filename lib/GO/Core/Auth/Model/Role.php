<?php
namespace GO\Core\Auth\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Criteria;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;

use GO\Core\Db\SoftDeleteTrait;
use GO\Core\Modules\Model\Module;
use GO\Core\Modules\Model\ModuleRole;

/**
 * Roles are used for permissions
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 *
 * @property User $users The users in this role
 * @properry User $user If this role represents a user then this returns the user
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Role extends AbstractRecord{
	
	use SoftDeleteTrait;
	
	/**
	 * The ID of the admins role
	 */
	const adminRoleId = 1;

	/**
	 * The ID of the Everyone role
	 */
	const everyoneRoleId = 2;

	protected static function defineRelations() {		
		self::hasMany('users', User::className(),'roleId')->via(UserRole::className());
		self::hasMany('userRole', UserRole::className(),'roleId');
		self::hasMany('moduleRoles', ModuleRole::className(), 'roleId');
		self::belongsTo('user', User::className(), 'userId')->setDeleteAction(Relation::DELETE_RESTRICT);				
	}

	/**
	 * Get the administrator's role
	 *
	 * @return Role
	 */
	public static function findAminRole(){

		$role = Role::findByPk(self::adminRoleId);

		if(!$role){
			$role = new Role();
			$role->id=self::adminRoleId;
			$role->userId=1;
			$role->name='Admins';
			$role->save();
		}

		return $role;
	}

	/**
	 * Get the everyone role
	 *
	 * @return Role
	 */
	public static function findEveryoneRole(){

		$role = Role::findByPk(self::everyoneRoleId);

		if(!$role){
			$role = new Role();
			$role->id=self::everyoneRoleId;
			$role->name='Everyone';
			$role->save();
		}

		return $role;
	}
	
	/**
	 * Get roles that should automatically be added with maximum permissions.
	 * 
	 * @return Role[]
	 */
	public static function findAutoRoles(){
		return Role::find(['autoAdd' => true]);
	}
	
	private $_modulesWithPermissions;
			
	public function setModulesWithPermissions(array $modules){
		$this->_modulesWithPermissions = $modules;
	}
	
	public function save() {
		
		if(parent::save()){
			
			if(isset($this->_modulesWithPermissions)){
				foreach($this->_modulesWithPermissions as $m){
				
					$mr = ModuleRole::findByPk(['roleId' => $this->id, 'moduleId' => $m['attributes']['id']]);		

					if(!$mr){
						$mr = new ModuleRole();
						$mr->moduleId = $m['attributes']['id'];
						$mr->roleId = $this->id;
					}
					
					unset($m['attributes']['id']);
					
					$mr->setAttributes($m['attributes']);
					if($mr->useAccess || $mr->createAccess){
						if(!$mr->save()){
							throw new Exception("Could not save role: ".var_export($mr->getValidationErrors(), true));
						}
					}else
					{
						$mr->delete();
					}
					
				}
			}
			
			return true;
		}else
		{
			return false;
		}
	}
	
	public function getModulesWithPermissions(){
		
		$q = Query::newInstance()
				->select('t.*, roles.useAccess, roles.createAccess')
				->joinRelation(
						'roles', 
						false, 
						'LEFT', 
						Criteria::newInstance()->where(['roles.roleId' => $this->id])
					);
		
		$models = Module::find($q)->all();
		
		//return as booleans
		foreach($models as $model){
			$model->useAccess = (bool) $model->useAccess;		
			$model->createAccess = (bool) $model->createAccess;			
		}

		return $models;
	}
}