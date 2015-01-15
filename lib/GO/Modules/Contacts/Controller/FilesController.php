<?php

namespace GO\Modules\Contacts\Controller;

use GO\Core\Db\AbstractRecord;
use GO\Modules\Contacts\Model\Contact;
use GO\Modules\Files\Controller\AbstractFilesController;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
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