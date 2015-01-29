<?php
namespace GO\Modules\Tickets\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Auth\Model\User;

/**
 * The Agent model
 *
 * @property int $userId
 * @property string $createdAt
 * @property string $modifiedAt
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Agent extends AbstractRecord {
	
    protected static function defineRelations() {

		self::hasMany('tickets', Ticket::className(), 'agentUserId');
		self::hasOne('user', User::className(), 'userId');

    }
}