<?php

namespace GO\Core\Db;

use GO\Core\Auth\Model\Role;
use GO\Core\Auth\Model\User;
use GO\Modules\Contacts\Model\Contact;
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
	
	
	public function testAll(){
		$this->_testCreateUser();
		$this->_testHasMany();
		$this->_testSetHasOne();
		$this->_testSetBelongsTo();
		$this->_testIsNew();
		$this->_testDelete();
	}
	
	
	private function _testCreateUser(){
		//Set's all roles on test user.
		$user = User::find(['username' => 'test'])->single();
		
		if($user) {			
			$user->deletePermanently();
		}
		
//		if (!$user) {
			$user = new User();
			$user->username = 'test';
			$user->password = 'Test123!';
//		}

		$user->save();

		$success = $user->save() !== false;
		
		$this->assertEquals(true, $success);
	}
	
	

	private function _testHasMany() {		

		$user = User::find(['username' => 'test'])->single();

		//Testing relational setters		
		$roles = Role::find();		

		foreach ($roles as $role) {
			$allRoles[] = $role;
			$roleAttributes[] = $role->getAttributes();
		}


		//do it again but with models instead of primary keys
		$user->roles = $allRoles;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);
		

		$this->assertEquals(count($user->roles->all()), count($allRoles));


		//do it again but with arrays instead of primary keys
		$user->roles = $roleAttributes;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);
		
		$this->assertEquals(count($user->roles->all()), count($allRoles));


		
	}
	

	private function _testSetHasOne() {
		
		$user = User::find(['username' => 'test'])->single();

				
		$roleOfUser = $user->role;
		
		$this->assertEquals(true, is_a($roleOfUser, AbstractRecord::className()));

		$user->role = $roleOfUser;
		$success = $user->save() !== false;

		$this->assertEquals(true, $success);

		$this->assertEquals($user->id, $user->role->userId);

	}
	

	private function _testSetBelongsTo(){
		
		$user = User::find(['username' => 'test'])->single();
		
		//Create contact for test user
		$contactAttr = ['firstName' => 'Test', 'lastName'=>'User'];		
		$contact = Contact::find($contactAttr)->single();
		if($contact){
			$contact->deletePermanently();
		}
		
		$contact = new Contact();
		$contact->setAttributes($contactAttr);	
		
		
		$contact->user = $user;
		
		$success = $contact->save() !== false;

		$this->assertEquals(true, $success);		
	}
	
	private function _testIsNew(){
		$user = new User();
		
		$this->assertEquals(true, $user->isNew());
		
		$user = User::find()->single();
		
		$this->assertEquals(false, $user->isNew());
	}
	
	
	private function _testDelete(){
		$user = User::find(['username' => 'test'])->single();
		
		$success = $user->delete();
		
		$this->assertEquals(true, $success);
		//soft delete
		$this->assertEquals(true, $user->deleted);
		
		$success = $user->deletePermanently();
		$this->assertEquals(true, $success);
		
		$user = User::find(['username' => 'test'])->single();
		
		$this->assertEquals(false, $user);
		
	}
	

}
