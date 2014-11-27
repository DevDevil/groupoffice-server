<?php
namespace Intermesh\Modules\Contacts\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;
use Intermesh\Modules\Contacts\Model\Contact;

/**
 * The contact model
 *
 * @property int $id
 * @property int $contactId
 * @property Contact $contact
 * @property string $type
 * @property string $email
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ContactEmailAddress extends AbstractRecord{	
	public static function defineRelations(RelationFactory $r){
		return [$r->belongsTo('contact', Contact::className(), 'contactId')];
	}
}