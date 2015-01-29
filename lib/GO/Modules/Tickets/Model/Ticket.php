<?php
/*
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace GO\Modules\Tickets\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Auth\Model\User;

/**
 * The Tickets model
 *
 * @property int $id
 * @property string $number
 * @property string $title
 * @property string $description
 * @property int $agentUserId
 * @property int $ownerUserId
 * @property User $owner
 * @property string $createdAt
 * @property string $modifiedAt
 */
class Ticket extends AbstractRecord{
	
	use \GO\Core\Db\SoftDeleteTrait;
	use \GO\Core\Auth\Model\RecordPermissionTrait;
	
    protected static function defineRelations() {

		self::belongsTo('agent', Agent::className(), 'agentUserId');
		self::hasMany('roles', TicketRole::className(), 'ticketId');
		//self::hasOne('customfields', Record::className(), 'id');

    }
}