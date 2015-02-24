<?php

namespace GO\Core\Db;

use GO\Core\Auth\Model\User;
use GO\Modules\Contacts\Model\Contact;
use PHPUnit_Framework_TestCase;

/**
 * The App class is a collection of static functions to access common services
 * like the configuration, reqeuest, debugger etc.
 */
class AbstractCRUDRecordTest extends PHPUnit_Framework_TestCase {

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
		
		return $user;
	}
	
	public function testPermissions(){
		
		//login as admin
		$admin = User::findByPk(1);
		$admin->setCurrent();		
		$current = User::current();		
		$this->assertEquals($admin->id, $current->id);
		
		
		$user = $this->_testCreateUser();
		
		$contact = new Contact();
		$contact->firstName = 'Test';
		$contact->lastName = date('YmdGis');
		$contact->save();
		
		$contactId = $contact->id;
		
		$user->setCurrent();
		
		$this->setExpectedException("GO\Core\Exception\Forbidden");
		
		//Should throw forbidden because user "test" can't read.
		$contact = Contact::findByPk($contactId);
		
		
		$admin->setCurrent();		
		$user->delete();
		
	}
}