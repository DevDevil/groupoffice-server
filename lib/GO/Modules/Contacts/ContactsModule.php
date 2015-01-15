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
		return [
//			'addressbooks' => [
//				'routeParams' => ['addressbookId'],
//				'children' => [
					'contacts' => [
						'routeParams' => ['contactId'], 
						'controller' => ContactController::className(),						
						'children' => [
								'thumb' => [
									'controller' => ThumbController::className(),
								],
							
								'files' => [
									'routeParams' => ['fileId'],
									'controller' => FilesController::className()
								],
								'timeline' => [
									'routeParams' => ['itemId'],
									'controller' => TimelineController::className()
								],
							]
						]
//				]
//			]
		];
	}
	
	
	public function depends() {
		return [
			FilesModule::className(),
			CustomFieldsModule::className(),
			TagsModule::className()
		];
	}
	
}