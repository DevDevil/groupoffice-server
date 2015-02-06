<?php
namespace GO\Modules\Projects\Model;

use GO\Core\Auth\Model\AbstractRole;

class ProjectRole extends AbstractRole{	
	public static function defineResource() {
		return self::belongsTo('project', Project::className(), 'projectId');
	}
	
}