<?php

namespace Intermesh\Modules\Email\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;

/**
 * The Folder model
 *
 * @property int $id
 * @property int $accountId
 * @property string $name

 * @property int $highestSyncedUid
 * @property int $highestModSeq
 * 
 * @property Account $account
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Thread extends AbstractRecord {	
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('folder', Folder::className(), 'accountId'),
			$r->hasMany('messages', Message::className(), 'threadId')
		];
	}
}