<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Modules\Auth\Model\RecordPermissionTrait;
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
	
	public static function defineRelations(RelationFactory $r){
		return array(
//			$r->hasMany('roles', ProjectRole::className(), 'projectId'),
//			$r->hasMany('tasks', Task::className(), 'projectId')
		);
	}
	
}
