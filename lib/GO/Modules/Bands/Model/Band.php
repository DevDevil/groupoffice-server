<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Auth\Model\AbstractCRUDRecord;
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
class Band extends AbstractCRUDRecord{	
	
	use \GO\Core\Db\SoftDeleteTrait;
	
	protected static function defineRelations() {
		
			self::hasMany('albums', Album::className(), 'bandId');
			self::hasOne('customfields', BandCustomFields::className(), 'id');
			
			//add this role for permissions
			self::hasMany('roles', BandRole::className(), 'bandId');		
	}
}