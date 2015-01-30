<?php

namespace GO\Modules\Contacts;

use GO\Core\AbstractModule;
use GO\Modules\Contacts\Controller\ContactController;
use GO\Modules\Contacts\Controller\FilesController;
use GO\Modules\Contacts\Controller\ThumbController;
use GO\Modules\Contacts\Controller\TimelineController;
use GO\Modules\CustomFields\CustomFieldsModule;
use GO\Modules\Files\FilesModule;
use GO\Modules\Tags\TagsModule;

class ContactsModule extends AbstractModule{
	public function routes(){
		ContactController::routes()
				->get('contacts', 'store')
				->get('contacts/0','new')
				->get('contacts/:contactId','read')
				->put('contacts/:contactId', 'update')
				->post('contacts', 'create')
				->delete('contacts/:contactId','delete');
		
		TimelineController::routes();
		ThumbController::routes();
		FilesController::routes();
	}
	
	
	public function depends() {
		return [
			FilesModule::className(),
			CustomFieldsModule::className(),
			TagsModule::className()
		];
	}
	
}