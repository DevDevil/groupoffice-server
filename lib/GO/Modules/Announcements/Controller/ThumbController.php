<?php

namespace GO\Modules\Announcements\Controller;

use GO\Modules\Announcements\Model\Announcement;
use GO\Modules\Upload\Controller\AbstractThumbController;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class ThumbController extends AbstractThumbController {

	
	protected function thumbGetFile() {
		$announcement = Announcement::findByPk($this->router->routeParams['announcementId']);		

		if ($announcement) {		
			return $announcement->getImageFile();
		}
		
		return false;
	}


	protected function thumbUseCache() {
		return true;
	}

}
