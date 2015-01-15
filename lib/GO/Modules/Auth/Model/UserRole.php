<?php
namespace GO\Modules\Auth\Model;

use GO\Core\Db\AbstractRecord;

/**
 * Roles are used for permissions
 * 
 * @property int $userId
 * @property int $roleId
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class UserRole extends AbstractRecord{
	public static function primaryKeyColumn() {
		return array('userId', 'roleId');
	}
}