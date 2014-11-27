<?php
namespace Intermesh\Modules\Projects\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Core\Db\SoftDeleteTrait;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $statusId
 * @property boolean $deleted
 * @property boolean $sticky
 * @property int $createdAt
 * @property int $modifiedAt
 * @property string $color
 * @property int $ownerUserId
 */

class Project extends AbstractRecord{
	
	const STATUS_NEW = 1;
	const STATUS_OPEN = 2;
	const STATUS_ONGOING = 3;
	const STATUS_COMPLETE = 4;

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
			$r->hasMany('roles', ProjectRole::className(), 'projectId'),
			$r->hasMany('tasks', Task::className(), 'projectId')
		);
	}
	
	public function save() {
		
		$wasNew=$this->getIsNew();
		
		$success = parent::save();
		
		if($success && $wasNew){
			
			//Share this note with the owner by adding it's role
			$model = new ProjectRole();
			$model->projectId=$this->id;
			$model->roleId=$this->owner->role->id;
			$model->readAccess=1;
			$model->editAccess=1;
			$model->deleteAccess=1;
			$model->save();
		}
		
		return $success;
	}
}
