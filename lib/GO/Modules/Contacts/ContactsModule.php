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
	
	const PERMISSION_CREATE = 1;
	
	public function routes(){
		ContactController::routes()
				->get('contacts', 'store')
				->get('contacts/0','new')
				->get('contacts/:contactId','read')
				->put('contacts/:contactId', 'update')
				->post('contacts', 'create')
				->delete('contacts/:contactId','delete');
		
		TimelineController::routes();
		
		ThumbController::routes()
						->get('contacts/0/thumb', 'download')
						->get('contacts/:contactId/thumb', 'download');
		
		FilesController::routes()
				->get('contacts/:contactId/files', 'store')
				->get('contacts/:contactId/files/:fileId','read')
				->put('contacts/:contactId/files/:fileId', 'update')
				->post('contacts/:contactId/files', 'create')
				->delete('contacts/:contactId/files/:fileId','delete');
	}
	
	
	public function depends() {
		return [
			FilesModule::className(),
			CustomFieldsModule::className(),
			TagsModule::className()
		];
	}
	
}