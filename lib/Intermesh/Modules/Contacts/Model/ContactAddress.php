<?php
namespace Intermesh\Modules\Contacts\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\RelationFactory;

/**
 * Contact address 
 * 
 * @property int $id
 * @property int $contactId
 * @property string $type
 * @property string $street
 * @property string $zipCode
 * @property string $city
 * @property string $country
 * @property Contact $contact
 *
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ContactAddress extends AbstractRecord {
	
	public static $defaultCountry = 'NL';
	
	public static function defineRelations(RelationFactory $r){
		return [$r->belongsTo('contact', Contact::className(), 'contactId')];
	}
	
	protected static function defineDefaultAttributes() {
		return ['country' => self::$defaultCountry];
	}
	
	public function getFormatted(){
		$formatted = $this->street."\n".
				$this->zipCode." ".$this->city."\n".
				$this->state."\n".
				$this->country;
		
		//remove double new lines
		return preg_replace("/[\n]+/","\n", $formatted);
	}
	
}