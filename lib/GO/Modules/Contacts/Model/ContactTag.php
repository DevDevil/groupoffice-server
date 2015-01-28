<?php
namespace GO\Modules\Contacts\Model;

use GO\Core\Db\AbstractRecord;

/**
 * The contact model
 *
 * @property int $id
 * @property string $name
 * @property Contact $contact
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ContactTag extends AbstractRecord{	
	public static function primaryKeyColumn() {
		return array('contactId', 'tagId');
	}
	
	public static function defineRelations(){
		self::belongsTo('contact', Contact::className(), 'contactId');
	}
}