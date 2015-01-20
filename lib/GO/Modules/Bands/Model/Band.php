<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
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
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->hasMany('albums', Album::className(), 'bandId'),
			$r->hasOne('customfields', BandCustomFields::className(), 'id')
			];
	}
}