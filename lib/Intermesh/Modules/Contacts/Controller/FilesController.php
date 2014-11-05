<?php

namespace Intermesh\Modules\Contacts\Controller;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Modules\Contacts\Model\Contact;
use Intermesh\Modules\Files\Controller\AbstractFilesController;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license https://www.gnu.org/licenses/lgpl.html LGPLv3
 */
class FilesController extends AbstractFilesController{
	
	
	protected function getModel(){
		return Contact::findByPk($this->router->routeParams['contactId']);

	}

	protected function canRead(AbstractRecord $model) {
		return $model->checkPermission('readAccess');			
	}

	protected function canWrite(AbstractRecord $model) {
		return $model->checkPermission('editAccess');	
	}

	protected function canUpload(AbstractRecord $model) {
		return $model->checkPermission('uploadAccess');
	}

}