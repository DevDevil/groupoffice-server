<?php
namespace GO\Modules\CustomFields\Model;

/**
 * CustomFieldsTrait trait
 * 
 * Add this to models that can be used as a custom fields extension of a model. 
 * 
 * Creating custom fields
 * ----------------------
 * 
 * 1. Create model "ContactCustomFields" and add the trait "use GO\Modules\CustomFields\Model\CustomFieldsTrait";
 * 2. Define relation in Contact model:
 * 	``````````````````````````````````````````````````````````````````
 * 	$r->hasOne('customfields', ContactCustomFields::className(), 'id')
 * 	```````````````````````````````````````````````````````````````````
 * 3. Now you can start defining custom fields and request the "customfields" with the contact store.
 * 
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
trait CustomFieldsTrait{	
	
	/**
	 * Returns true if this is a custom fields record.
	 * 
	 * When a FieldSet is added it validates if the model it's for is actually 
	 * a custom fields record.
	 * 
	 * @return boolean
	 */
	public static function isCustomFieldsRecord(){
		return true;
	}	
}