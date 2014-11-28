<?php

namespace Intermesh\Modules\Email;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Email\Controller\AccountController;
use Intermesh\Modules\Email\Controller\AttachmentController;
use Intermesh\Modules\Email\Controller\ImapMessageController;
use Intermesh\Modules\Email\Controller\SyncController;
use Intermesh\Modules\Email\Controller\ThreadController;


class EmailModule extends AbstractModule {

	public static function getRoutes() {
		return [
			'email' => [
				'children' => [
					'sync' => [
						'routeParams' => ['accountId'],
						'controller' => SyncController::className(),
					],
					
					'accounts' => [
						'routeParams' => ['accountId'],
						'controller' => AccountController::className(),
						'children' => [
							'mailboxes' =>[
								'routeParams' => ['mailboxName'],
//								'controller' => ImapMailboxController::className(),
								
								'children' => [
									'messages' =>[
										'routeParams' => ['uid'],
										'controller' => ImapMessageController::className(),
										'children' => [
											'attachments' =>[
												'routeParams' => ['partNumber'],
												'controller' => AttachmentController::className()
											]
										]
									],
								],
							],
							
							'folders' => [
									'routeParams' => ['folderId'],
									'controller' => Controller\FolderController::className(),
									'children' => [
											
										'threads' =>[
											'routeParams' => ['threadId'],
											'controller' => ThreadController::className(),
											'children' => [
												'attachments' =>[
													'routeParams' => ['attachmentId'],
													'controller' => AttachmentController::className()
												]
											]
										],


										'messages' =>[
											'routeParams' => ['messageId'],
											'controller' => Controller\MessageController::className(),
											'children' => [
												'attachments' =>[
													'routeParams' => ['attachmentId'],
													'controller' => AttachmentController::className()
												]
											]
										]
									]
						
							]
						]
					]
				]
				
			]
		];
	}
}