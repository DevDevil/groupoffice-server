<?php
namespace Intermesh\Modules\Projects\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Core\Db\SoftDeleteTrait;

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
	
	use RecordPermissionTrait;
	
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
