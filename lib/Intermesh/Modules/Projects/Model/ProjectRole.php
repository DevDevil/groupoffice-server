<?php
namespace Intermesh\Modules\Projects\Model;

use Intermesh\Modules\Auth\Model\AbstractRole;

class ProjectRole extends AbstractRole{	
	public static function resourceKey() {
		return 'projectId';
	}
	
}