Creating and saving a has many relation
=======================================

For this tutorial we look at the address book app. A contact has many phone numbers.

On the server side
------------------
Take these steps:

1. Create database table "addressbookContactPhone"
2. Create model \IPE\Apps\Addresbook\Model\ContactPhone
3. Define the "hasMany" relation in \IPE\Apps\Addresbook\Model\Contact:
	````````````````````````````````````````````````````````````````````````````````````
	public static function defineRelations(RelationFactory $r){
		return array(
			.....
			'phoneNumbers'=>$r->hasMany(ContactPhone::className(), 'contactId')
			......
		);
	}
	`````````````````````````````````````````````````````````````````````````````````````
4. Add the documentation magic relation property in the doc block on top of the contact class file:  
	@property ContactPhone[] $phoneNumbers
5. Nothing more! Relations are automatically saved and fetched by the server API.


On the client side (AngularJS)
------------------------------

1. Add the relation attribute to model in the controller (app/apps/addressbook/addressbook-controller.js) so it's fetched and saved:
	``````````````````````````````````````````````````````````````````
	$scope.contact = new Model('contact', 'IPE/addressbook/contact',{
		returnAttributes : [
			'*',
			'emailAddresses',
			'phoneNumbers'
		]
	});
	``````````````````````````````````````````````````````````````````

2. Add the view part in 'app/apps/addressbook/partials/contact-edit.html'.
	Notice the markDeleted attribute for deleting numbers.
	`````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````
	<label>{{"Phone"| t}}</label>
	<div ng-if="!phoneNumber.attributes.markDeleted" class="form-group input-group" ng-repeat="phoneNumber in contact.attributes.phoneNumbers">
			<div class="input-group-btn">
				<button type="button" class="btn btn-default" ng-model="phoneNumber.type" data-html="1" ng-options="type.value as type.label for type in phoneNumberOptions" bs-select>
					Type <span class="caret"></span>
				</button>
			</div>
			<input type="text" ng-model="phoneNumber.number" class="form-control" autofocus="!phoneNumber.id">
			<span class="input-group-btn">
				<button type="button" class="btn btn-danger" ng-click="phoneNumber.attributes.markDeleted=true;"><i class="fa fa-trash-o"></i></button>
			</span>				
	</div>
	<div class="form-group">
		<span class="btn btn-default" ng-click="addPhoneNumber()"><i class="fa fa-plus"></i> {{"Add phone number" | t}}</span>
	</div>
	`````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````````
3. In the edit controller (app/apps/addressbook/contact-edit-controller.js) we need a function to add new phone numbers:
	```````````````````````````````````````````````````````````````````````
	$scope.addPhoneNumber = function(){
		$scope.contact.attributes.phoneNumbers.push({type:"work"});
	};
	```````````````````````````````````````````````````````````````````````

4. That's it. Now everything should work.

