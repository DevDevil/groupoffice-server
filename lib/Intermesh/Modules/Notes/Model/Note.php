<?php
namespace Intermesh\Modules\Notes\Model;

use Intermesh\Modules\Auth\Model\User;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Core\Db\SoftDeleteTrait;

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
	
	public static function defineRelations(RelationFactory $r){
		return array(
			$r->belongsTo('owner', User::className(), 'ownerUserId'),
			$r->hasMany('roles', NoteRole::className(), 'noteId'),
			$r->hasMany('listItems', NoteListItem::className(), 'noteId'),
			$r->hasMany('images', NoteImage::className(), 'noteId')
		);
	}
	
	public function save() {
		
		$wasNew=$this->getIsNew();
		
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
}
