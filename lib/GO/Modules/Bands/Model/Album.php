<?php
namespace GO\Modules\Bands\Model;

use GO\Core\Db\AbstractRecord;

/**
 * The Album model
 *
 * @property int $id
 * @property int $bandId
 * @property string $name
 * @property int $ownerUserId
 * @property \GO\Modules\Auth\Model\User $owner
 * @property string $createdAt
 * @property string $modifiedAt
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Album extends AbstractRecord {
	protected static function defineRelations(\GO\Core\Db\RelationFactory $r) {
		return [$r->belongsTo('band', Band::className(), 'bandId')];
	}
}