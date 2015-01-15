<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\RelationFactory;
use GO\Modules\Contacts\Model\Contact;


/**
 * Contact address 
 * 
 * @property int $id
 * @property int $contactId
 * @property string $type
 * @property string $date
 * @property Contact $contact
 *
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ContactDate extends AbstractRecord {
	public static function defineRelations(RelationFactory $r){
		return [$r->belongsTo('contact', Contact::className(), 'contactId')];
	}
}