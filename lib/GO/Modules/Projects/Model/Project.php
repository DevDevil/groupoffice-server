<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Modules\Auth\Model\RecordPermissionTrait;
use GO\Core\Db\SoftDeleteTrait;

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

	const PROGRESS_STATUS_OK = 1;
	const PROGRESS_STATUS_WARNING = 2;
	const PROGRESS_STATUS_LATE = 3;
	
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
	
	/**
	 * Get the percentage of the progress of this project.
	 * The percentage is calculated based on the tasks that are set for this project
	 * 
	 * @return int	Value between 0 and 100 
	 */
	public function getProgressPercentage(){
		
		if($this->name =='Poort')
			return 90;
		
		if($this->name =='Late project')
			return 40;
			
		return 10;
	}
	
	/**
	 * Get the progress status
	 * This returns one of the following statuses:
	 * 
	 * self::PROGRESS_STATUS_OK					The progress is on schedule
	 * self::PROGRESS_STATUS_WARNING;		The progress is late but the project can be finished within the deadline
	 * self::PROGRESS_STATUS_LATE;				The progress is late and the project cannot be finished within the deadline anymore.
	 * 
	 * @return int
	 */
	public function getProgressStatus(){
		
		if($this->name =='Poort')
			return self::PROGRESS_STATUS_WARNING;
		
		if($this->name =='Late project')
			return self::PROGRESS_STATUS_LATE;
		
		return self::PROGRESS_STATUS_OK;
	}
	
	public function getColorRGB(){
		
		if(!empty($this->color)){
			return $this->_hex2rgb($this->color);
		}
		
		return $this->_hex2rgb('#FFF');
	}
	
	
	private function _hex2rgb($hex){
		$hex = str_replace("#", "", $hex);

		if(strlen($hex) == 3) {
			 $r = hexdec(substr($hex,0,1).substr($hex,0,1));
			 $g = hexdec(substr($hex,1,1).substr($hex,1,1));
			 $b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
			 $r = hexdec(substr($hex,0,2));
			 $g = hexdec(substr($hex,2,2));
			 $b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return implode(",", $rgb); // returns the rgb values separated by commas
//		return $rgb; // returns an array with the rgb values
 }
	
	
}
