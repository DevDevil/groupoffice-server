<?php

namespace GO\Modules\Email;

use GO\Core\AbstractModule;
use GO\Modules\Email\Controller\AccountController;
use GO\Modules\Email\Controller\AttachmentController;
use GO\Modules\Email\Controller\ImapMessageController;
use GO\Modules\Email\Controller\SyncController;
use GO\Modules\Email\Controller\TestController;
use GO\Modules\Email\Controller\ThreadController;


class EmailModule extends AbstractModule {

	public function routes() {
		
		AccountController::routes()
			->get('email/accounts', 'store')
			->get('email/accounts/0','new')
			->get('email/accounts/:accountId','read')
			->put('email/accounts/:accountId', 'update')
			->post('email/accounts', 'create')
			->delete('email/accounts/:accountId','delete');
		
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