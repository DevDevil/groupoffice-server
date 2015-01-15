<?php
namespace GO\Modules\Timeline\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Core\Db\SoftDeleteTrait;
use GO\Modules\Auth\Model\User;
use GO\Modules\Contacts\Model\Contact;

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
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
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