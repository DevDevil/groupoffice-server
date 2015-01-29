<?php
namespace GO\Modules\Notes\Model;

use GO\Core\Auth\Model\User;
use GO\Core\Db\AbstractRecord;

use GO\Core\Auth\Model\RecordPermissionTrait;
use GO\Core\Db\SoftDeleteTrait;

/**
 * @property int $id
 * @property int $ownerUserId
 * @property User $owner
 * @property string $name
 * @property int $sortOrder
 */

class Note extends AbstractRecord{
	
	use RecordPermissionTrait;
	
	use SoftDeleteTrait {
		delete as softDelete;
	}
	
	public static $availableColors = array('red','pink','blue','yellow');
	
	protected static function defineValidationRules() {
		return array(				
		 //new ValidateUnique('title')				
		);
	}
	
	public static function defineRelations(){
		
		self::belongsTo('owner', User::className(), 'ownerUserId');
		self::hasMany('roles', NoteRole::className(), 'noteId');
		self::hasMany('listItems', NoteListItem::className(), 'noteId');
		self::hasMany('images', NoteImage::className(), 'noteId');
		
	}
	
	public function save() {
		
		$wasNew=$this->isNew();
		
		$this->_resort();
		$success = parent::save();
		
		if($success && $wasNew){
			
			//Share this note with the owner by adding it's role
			$model = new NoteRole();
			$model->noteId=$this->id;
			$model->roleId=$this->owner->role->id;
			$model->readAccess=1;
			$model->editAccess=1;
			$model->deleteAccess=1;
			$model->save();
		}
		
		return $success;
	}
	
	public $resort = false;
	
	private function _resort(){		
		
		if($this->resort) {			

			$notes = Note::find();
			

			$sortOrder = 0;
			foreach($notes as $note){
				
				$sortOrder++;

				if($sortOrder == $this->sortOrder){
					$sortOrder++;
				}

				//skip this model
				if($note->id == $this->id){					
					continue;
				}

				$note->sortOrder = $sortOrder;				
				$note->save();
			}
		}		
	}
	
}
