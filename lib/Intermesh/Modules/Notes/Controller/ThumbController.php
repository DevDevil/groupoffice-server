<?php

namespace Intermesh\Modules\Notes\Controller;

use Intermesh\Modules\Notes\Model\NoteImage;
use Intermesh\Modules\Upload\Controller\AbstractThumbController;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
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
