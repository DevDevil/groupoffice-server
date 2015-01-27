<?php
namespace GO\Modules\Notes\Model;

use GO\Core\Db\AbstractRecord;


/**
 * @property int $id
 * @property int $noteId
 * @property string $text
 * @property boolean $checked
 * @property int $sortOrder
 */

class NoteListItem extends AbstractRecord{
	
	protected static function defineValidationRules() {
		return array(				

		);
	}
	
	public static function defineRelations(){
		self::belongsTo('note', Note::className(), 'noteId');
	}
	
}