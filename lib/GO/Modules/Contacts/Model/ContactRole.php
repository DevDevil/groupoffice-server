<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * @property int $contactId
 * @property int $roleId
 * @property int $permissionType
 */
class ContactRole extends AbstractRole{	
	

	protected static function defineResource() {
		return static::belongsTo('contact', Contact::className(), 'contactId');
	}
}