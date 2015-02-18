<?php

namespace GO\Modules\Upload\Controller;

use Flow\Basic;
use Flow\Request;
use GO\Core\App;
use GO\Core\Controller\AbstractController;

/**
 * The controller that handles file uploads and can thumbnail the temporary files.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FlowController extends AbstractController {	
	protected function actionUpload(){
		$chunksTempFolder = App::accessToken()->getTempFolder()->createFolder('uploadChunks')->create();

		$request = new Request();

		$finalFile = App::accessToken()->getTempFolder()->createFile($request->getFileName());

		if (Basic::save($finalFile->getPath(), $chunksTempFolder->getPath())) {
			// file saved successfully and can be accessed at './final_file_destination'

			return $this->renderJson(array(
					'success' => true,
					'file' => $finalFile->getRelativePath(App::accessToken()->getTempFolder())
			));
		} else {
			// This is not a final chunk or request is invalid, continue to upload.
			return $this->renderJson(array(
					'success' => true
			));
		}
	}
}