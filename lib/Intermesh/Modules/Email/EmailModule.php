<?php

namespace Intermesh\Modules\Email;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Email\Controller\AccountController;
use Intermesh\Modules\Email\Controller\AttachmentController;
use Intermesh\Modules\Email\Controller\MailboxController;
use Intermesh\Modules\Email\Controller\MessageController;


class EmailModule extends AbstractModule {

	public static function getRoutes() {
		return [
			'email' => [
				'children' => [
					'accounts' => [
						'routeParams' => ['accountId'],
						'controller' => AccountController::className(),
						'children' => [
							'mailbox' =>[
								'routeParams' => ['mailboxName'],
								'controller' => MailboxController::className(),
								
								'children' => [
									'messages' =>[
										'routeParams' => ['uid'],
										'controller' => MessageController::className(),
										'children' => [
											'attachments' =>[
												'routeParams' => ['partNumber'],
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