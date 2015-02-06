<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Db\AbstractRecord;

use GO\Core\Auth\Model\User;

/**
 * The Band model
 *
 * @property int $id
 * @property string $name
 * @property int $ownerUserId
 * @property User $owner
 * @property string $createdAt
 * @property string $modifiedAt
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Band extends AbstractRecord{	
	
	use \GO\Core\Db\SoftDeleteTrait;	
	
	//Add this line for permissions
	use \GO\Core\Auth\Model\RecordPermissionTrait;
	
	
	/**
	 * Allow read access to the role
	 */
	const PERMISSION_READ = 0;
	
	/**
	 * Allow write access to the role
	 */
	const PERMISSION_WRITE = 1;	
	
	/**
	 * Allow delete access to the role
	 */
	const PERMISSION_DELETE = 2;
	
	
	
	protected static function defineRelations() {
		
			self::hasMany('albums', Album::className(), 'bandId');
			self::hasOne('customfields', BandCustomFields::className(), 'id');
			
			//add this role for permissions
			self::hasMany('roles', BandRole::className(), 'bandId');		
	}
}