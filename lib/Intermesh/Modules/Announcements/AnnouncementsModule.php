<?php

namespace Intermesh\Modules\Announcements;

use Intermesh\Core\AbstractModule;
use Intermesh\Modules\Announcements\Controller\ThumbController;

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
