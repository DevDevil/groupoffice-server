<?php
namespace Intermesh\Modules\Timeline\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Core\Db\SoftDeleteTrait;
use Intermesh\Modules\Auth\Model\User;
use Intermesh\Modules\Contacts\Model\Contact;

/**
 * The Item model
 *
 * @property int $id
 * @property int $ownerUserId
 * @property User $owner
 * @property string $modifiedAt
 * @property string $createdAt
 * @property int $contactId
 * @property string $text
 * 
 * @property User $owner
 * @property Message $imapMessage
 * @property Contact $contact
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */

class Item extends AbstractRecord {
	use SoftDeleteTrait;
	
	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('owner', User::className(), 'ownerUserId'),
			$r->belongsTo('contact', Contact::className(), 'contactId')
			];
	}
	
	public function getAuthorThumbUrl(){
		return $this->owner->contact->getThumbUrl();
	}
}