<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Db\AbstractRecord;

use GO\Core\Auth\Model\RecordPermissionTrait;
use GO\Core\Db\SoftDeleteTrait;

/**
 * @property int $id
 * @property int $projectId
 * @property string $name
 * @property string $description
 * @property boolean $deleted
 * @property int $parentId
 * @property int $previousId
 * @property int $startTime
 * @property boolean $sticky
 */

class Task extends AbstractRecord{
	
//	use RecordPermissionTrait;
	
	use SoftDeleteTrait {
		delete as softDelete;
	}
		
	protected static function defineValidationRules() {
		return array(				
		 //new ValidateUnique('title')				
		);
	}
	
	
}
