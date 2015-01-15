<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Db\AbstractRecord;
use GO\Modules\CustomFields\Model\CustomFieldsTrait;

class ContactCustomFields extends AbstractRecord{	
	use CustomFieldsTrait;
}