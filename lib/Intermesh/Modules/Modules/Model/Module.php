<?php
namespace Intermesh\Modules\Modules\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Auth\Model\RecordPermissionTrait;
use Intermesh\Core\Db\SoftDeleteTrait;	

/**
 * Module model
 * 
 * Each module that can be used in the application must have a database entry.
 *
 * @property int $id
 * @property string $name
 * 
 * @property ModuleRole $roles
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends AbstractRecord{
	
	use SoftDeleteTrait;	
	
	use RecordPermissionTrait;
	
	public $ownerUserId = 1;
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->hasMany('roles', ModuleRole::className(), 'moduleId')
			];
	}
}