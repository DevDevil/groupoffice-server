<?php

namespace Intermesh\Modules\Email\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;


/**
 * The Attachment model
 *
 * @property int $id
 * @property int $messageId
 * @property string $email
 * @property string $personal
 * 
 * @property Message $message
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class ToAddress extends AbstractRecord {	
		
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('message', Message::className(), 'messageId'),
		];
	}
	
	public function __toString() {
		if (!empty($this->personal)) {
			return '"' . $this->personal . '" <' . $this->email . '>';
		} else {
			return $this->email;
		}
	}	
}