<?php
namespace GO\Modules\Notes\Model;

use GO\Core\Auth\Model\AbstractRole;

class NoteRole extends AbstractRole {
	protected static function defineResource() {
		return self::belongsTo('note', Note::className(), 'noteId');
	}	
}