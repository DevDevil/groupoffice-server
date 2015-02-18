<?php

namespace GO\Modules\Email;

use GO\Core\AbstractModule;
use GO\Modules\Email\Controller\AccountController;
use GO\Modules\Email\Controller\AttachmentController;
use GO\Modules\Email\Controller\FolderController;
use GO\Modules\Email\Controller\ThreadController;

class EmailModule extends AbstractModule {

	public function routes() {

		AccountController::routes()
				->get('email/accounts', 'store')
				->get('email/accounts/0', 'new')
				->get('email/accounts/:accountId', 'read')
				->get('email/accounts/:accountId/sync', 'sync')
				->put('email/accounts/:accountId', 'update')
				->post('email/accounts', 'create')
				->delete('email/accounts/:accountId', 'delete');
		
		FolderController::routes()
				->get('email/accounts/:accountId/folders', 'store')
				->get('email/accounts/:accountId/folders/0', 'new')
				->get('email/accounts/:accountId/folders/:folderId', 'read')
				->put('email/accounts/:accountId/folders/:folderId', 'update')
				->post('email/accounts/:accountId/folders', 'create')
				->delete('email/accounts/:accountId/folders/:folderId', 'delete');

		ThreadController::routes()
				->get('email/accounts/:accountId/folders/:folderId/threads', 'store');
		
		AttachmentController::routes()
				->get('email/accounts/:accountId/folders/:folderId/threads/:threadId/attachment', 'read');

//		return [
//			'email' => [
//				'children' => [
//					
//					'test' =>[
//							'controller' => TestController::className(),
//					],
//					
//					'sync' => [
//						'routeParams' => ['accountId'],
//						'controller' => SyncController::className(),
//					],
//					
//					'accounts' => [
//						'routeParams' => ['accountId'],
//						'controller' => AccountController::className(),
//						'children' => [
//							'mailboxes' =>[
//								'routeParams' => ['mailboxName'],
////								'controller' => ImapMailboxController::className(),
//								
//								'children' => [
//									'messages' =>[
//										'routeParams' => ['uid'],
//										'controller' => ImapMessageController::className(),
//										'children' => [
//											'attachments' =>[
//												'routeParams' => ['partNumber'],
//												'controller' => AttachmentController::className()
//											]
//										]
//									],
//								],
//							],
//							
//							'folders' => [
//									'routeParams' => ['folderId'],
//									'controller' => Controller\FolderController::className(),
//									'children' => [
//											
//										'threads' =>[
//											'routeParams' => ['threadId'],
//											'controller' => ThreadController::className(),
//											'children' => [
//												'attachments' =>[
//													'routeParams' => ['attachmentId'],
//													'controller' => AttachmentController::className()
//												]
//											]
//										],
//
//
//										'messages' =>[
//											'routeParams' => ['messageId'],
//											'controller' => Controller\MessageController::className(),
//											'children' => [
//												'attachments' =>[
//													'routeParams' => ['attachmentId'],
//													'controller' => AttachmentController::className()
//												]
//											]
//										]
//									]
//						
//							]
//						]
//					]
//				]
//				
//			]
//		];
	}

}
