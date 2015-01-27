<?php

namespace GO\Modules\Email\Model;

use GO\Core\Db\AbstractRecord;



/**
 * The Attachment model
 *
 * @property int $id
 * @property int $messageId
 * @property string $email
 * @property string $personal
 * @property int $type See TYPE_* constants
 * 
 * @property Message $message
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Address extends AbstractRecord {	
	
	const TYPE_FROM = 0;
	const TYPE_TO = 1;
	const TYPE_CC = 2;
	const TYPE_BCC = 3;
	
	use \GO\Core\Db\TruncateAttributesTrait;
	
	protected static function defineRelations() {
		self::belongsTo('message', Message::className(), 'messageId');
	}
	
	
	public function __toString() {
		if (!empty($this->personal)) {
			return '"' . $this->personal . '" <' . $this->email . '>';
		} else {
			return $this->email;
		}
	}	
}