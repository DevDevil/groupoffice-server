<?php
namespace GO\Modules\Upload\Controller;

use GO\Core\App;
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
		return App::session()->getTempFolder()->createFile($this->router->routeParams['tempFile']);
	}
	
	protected function thumbUseCache() {
		return false;
	}

}

