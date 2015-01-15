<?php

namespace GO\Modules\Announcements;

use GO\Core\AbstractModule;
use GO\Modules\Announcements\Controller\ThumbController;

class AnnouncementsModule extends AbstractModule {

	public function routes() {
		return [
			'announcements' => [
				'controller' => Controller\AnnouncementController::className(),
				'routeParams' => ['announcementId'],
				'children' => [
					'thumb' => [
						'controller' => ThumbController::className(),
					],
				]
			]
		];
	}

}
