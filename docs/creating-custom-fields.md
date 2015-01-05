Creating custom fields
----------------------

1. Create model "ContactCustomFields" and add the trait "use Intermesh\Modules\CustomFields\Model\CustomFieldsTrait";
2. Define relation in Contact model:
	``````````````````````````````````````````````````````````````````
	$r->hasOne('customfields', ContactCustomFields::className(), 'id')
	```````````````````````````````````````````````````````````````````
3. Now you can start defining custom fields and request the "customfields" with the contact store.

