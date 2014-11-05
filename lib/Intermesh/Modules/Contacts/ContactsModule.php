<?php

namespace Intermesh\Modules\Contacts;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Contacts\Controller\ContactController;
use Intermesh\Modules\Contacts\Controller\FilesController;
use Intermesh\Modules\Contacts\Controller\ThumbController;
use Intermesh\Modules\Contacts\Controller\TimelineController;

class ContactsModule extends AbstractModule{
	public static function getRoutes(){
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
	
}