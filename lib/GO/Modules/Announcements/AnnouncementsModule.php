<?php

namespace GO\Modules\Announcements;

use GO\Core\AbstractModule;
use GO\Modules\Announcements\Controller\ThumbController;

class AnnouncementsModule extends AbstractModule {

	public function routes() {
		
		Controller\AnnouncementController::routes()
				->get('announcements', 'store')
				->get('announcements/0','new')
				->get('announcements/:announcementId','read')
				->put('announcements/:announcementId', 'update')
				->post('announcements', 'create')
				->delete('announcements/:announcementId','delete');
		
		ThumbController::routes()
				->get('announcements/:announcementId/thumb', 'download');
	}

}
