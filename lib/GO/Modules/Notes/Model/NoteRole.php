<?php
namespace GO\Modules\Notes\Model;

use GO\Core\Auth\Model\AbstractRole;

class NoteRole extends AbstractRole{	
	public static function resourceKey() {
		return 'noteId';
	}
	
}