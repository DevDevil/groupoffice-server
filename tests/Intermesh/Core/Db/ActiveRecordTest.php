<?php

namespace Intermesh\Core\Db;

use Intermesh\Modules\Auth\Model\Role;
use Intermesh\Modules\Auth\Model\User;
use PHPUnit_Framework_TestCase;

/**
 * The App class is a collection of static functions to access common services
 * like the configuration, reqeuest, debugger etc.
 */
class ActiveRecordTest extends PHPUnit_Framework_TestCase {

	public function testTimezone() {

		$user = new User();
		$user->modifiedAt = "2007-04-05T12:30:00+02:00";
		$this->assertEquals("2007-04-05T10:30:00Z", $user->modifiedAt);
	}

	public function testSetRelation() {

		//Set's all roles on test user.
		$user = User::find(Query::newInstance()->where(['username' => 'test']))->single();
		
		if($user) {			
			$user->deletePermanently();
		}
		
//		if (!$user) {
			$user = new User();
			$user->username = 'test';
			$user->password = $user->passwordConfirm = 'Test123!';
//		}

		$user->save();

		$success = $user->save() !== false;
		
	

		$this->assertEquals(true, $success);



		//Testing relational setters
		$roleIds = array();
		$roles = Role::find();

		foreach ($roles as $role) {
			$allRoles[] = $role;
			$roleAttributes[] = array('attributes'=>$role->getAttributes());
		}

//		//With array of primary keys
//		$user->roles = $roleIds;
//		$success = $user->save() !== false;
//
//		$this->assertEquals(true, $success);
//
//		$savedRoleIds = array();
//		foreach ($user->roles as $role) {
//			$savedRoleIds[] = $role->id;
//		}
//
//		$this->assertEquals($roleIds, $savedRoleIds);

		//do it again but with models instead of primary keys
		$user->roles = $allRoles;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);
		$this->assertEquals($user->roles->all(), $allRoles);


		//do it again but with arrays instead of primary keys
		$user->roles = $roleAttributes;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);

//		$this->assertEquals($roleIds,$allRoles $savedRoleIds);


		$roleOfUser = $user->role;
		
		$this->assertEquals(true, is_a($roleOfUser, AbstractRecord::className()));

		$user->role=$roleOfUser;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);

		$this->assertEquals($user->id, $user->role->userId);

	}

}
