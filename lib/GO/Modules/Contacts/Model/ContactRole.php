<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * @property int $contactId
 * @property int $roleId
 * @property int $permissionType
 */
class ContactRole extends AbstractRole{	
	
	/**
	 * Allow read access to the role
	 */
	const PERMISSION_READ = 0;
	
	/**
	 * Allow write access to the role
	 */
	const PERMISSION_WRITE = 1;	
	
	/**
	 * Allow delete access to the role
	 */
	const PERMISSION_DELETE = 2;
	
	
	protected static function defineResource() {
		return static::belongsTo('contact', Contact::className(), 'contactId');
	}
}