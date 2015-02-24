<?php
namespace GO\Modules\Projects\Model;

//use GO\Core\Auth\Model\RecordPermissionTrait;


use GO\Core\Db\AbstractRecord;
use GO\Core\Db\SoftDeleteTrait;
use GO\Modules\Contacts\Model\Contact;

/**
 * @property int $id
 * @property string $name
 * @property int $contactId
 * @property string $contactName
 * @property string $contactEmail
 * @property int $companyId
 * @property string $companyName
 * @property int $createdAt
 * @property int $modifiedAt
 * @property int $createdBy
 * @property boolean $deleted
 * 
 * Relations
 * @property Contact $contact
 * @property Company $company 
 */

class Project extends AbstractRecord{

//	use RecordPermissionTrait;
	
	use SoftDeleteTrait {
		delete as softDelete;
	}
		
	protected static function defineValidationRules() {
		return array(				
		 //new ValidateUnique('title')				
		);
	}
	
	public static function defineRelations(){
//		self::hasMany('roles', ProjectRole::className(), 'projectId');
		self::hasOne('contact', Contact::className(), 'contactId');
//		self::hasOne('company', Company::className(), 'companyId');
	}
	
}
