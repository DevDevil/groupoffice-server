<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Auth\Model\AbstractRole;

/**
 * @param int $contactId
 * @param int $userId
 * @param bool $readAccess
 * @param bool $editAccess
 * @param bool $deleteAccess
 */
class ContactRole extends AbstractRole{	
	public static function resourceKey() {
		return 'contactId';
	}	
}