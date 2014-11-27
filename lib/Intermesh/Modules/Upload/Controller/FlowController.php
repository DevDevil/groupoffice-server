<?php

namespace Intermesh\Modules\Upload\Controller;

use Flow\Basic;
use Flow\Request;
use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;

/**
 * The controller that handles file uploads and can thumbnail the temporary files.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FlowController extends AbstractRESTController {

	/**
	 * Use Flow.js to upload files. This controller returns the filenames relative
	 *
	 * to the App::session()->getTempFolder();
	 */
	public function httpGet() {

		return $this->handleUpload();
	}
	
	public function httpPost(){
		return $this->handleUpload();
	}
	
	private function handleUpload(){
		$chunksTempFolder = App::session()->getTempFolder(true)->createFolder('uploadChunks')->create();

		$request = new Request();

		$finalFile = App::session()->getTempFolder()->createFile($request->getFileName());

		if (Basic::save($finalFile->getPath(), $chunksTempFolder->getPath())) {
			// file saved successfully and can be accessed at './final_file_destination'

			return $this->renderJson(array(
					'success' => true,
					'file' => $finalFile->getRelativePath(App::session()->getTempFolder())
			));
		} else {
			// This is not a final chunk or request is invalid, continue to upload.
			return $this->renderJson(array(
					'success' => true
			));
		}
	}
}