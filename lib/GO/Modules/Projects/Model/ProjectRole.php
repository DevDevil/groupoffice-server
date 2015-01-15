<?php
namespace GO\Modules\Projects\Model;

use GO\Modules\Auth\Model\AbstractRole;

class ProjectRole extends AbstractRole{	
	public static function resourceKey() {
		return 'projectId';
	}
	
}