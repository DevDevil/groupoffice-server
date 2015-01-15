<?php

namespace GO\Modules\Notes\Controller;

use GO\Modules\Notes\Model\NoteImage;
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
		$noteImage = NoteImage::findByPk($this->router->routeParams['noteImageId']);		

		if ($noteImage) {		
			return $noteImage->getImageFile();
		}
		
		return false;
	}


	protected function thumbUseCache() {
		return true;
	}

}
