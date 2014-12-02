<?php

namespace Intermesh\Modules\Files\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\Files\Model\File;

/**
 * The controller that handles file uploads and can thumbnail the temporary files.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class AbstractFilesController extends AbstractRESTController {
	
	/**
	 * 
	 * @param int $modelId
	 * @return AbstractRecord|boolean
	 */
	abstract protected function getModel();

	abstract protected function canRead(AbstractRecord $model);

	abstract protected function canWrite(AbstractRecord $model);

	abstract protected function canUpload(AbstractRecord $model);

	protected function httpGet($fileId = null, $download = false, $returnAttributes = []) {
		if (!isset($fileId)) {
			return $this->callMethodWithParams('store');
		} else {

			if ($fileId == 0) {
				$file = new File();
			} else {
				$file = File::findByPk($fileId);

				if (!$this->canRead($file->getModel())) {
					return $this->renderError(403);
				}
			}

			if (!$file) {
				return $this->renderError(404);
			}

			if($download){
				$file->output();
			}else
			{
				return $this->renderModel($file, $returnAttributes);
			}
		}
	}

	public function store($returnAttributes = []) {

		$model = $this->getModel();

		if (!$model) {
			return $this->renderError(404);
		}

		$folder = $model->getFolder();

		if (!$folder) {
			return $this->renderJson(['success' => true, 'results' => []]);
		} else {
			$store = new Store($folder->children);
			$store->setReturnAttributes($returnAttributes);
			$store->format('downloadUrl', function($model) {
				return $this->router->buildUrl($this->router->route.'/'.$model->id, ['download' => 1]);
			});
			return $this->renderStore($store);
		}
	}

//	/**
//	 * Use Flow.js to upload files. This controller returns the filenames relative
//	 *
//	 * to the App::session()->getTempFolder();
//	 */
//	public function actionUpload($modelId) {
//		$model = $this->getModel($modelId);
//
//		if (!$this->canUpload($model)) {
//			throw new Forbidden();
//		}
//
//		$chunksTempFolder = App::session()->getTempFolder(true)->createFolder('uploadChunks')->create();
//
//		$request = new Request();
//
//
//		$finalFile = File2::tempFile();
//
//
//		if (Basic::save($finalFile->getPath(), $chunksTempFolder->getPath())) {
//			// file saved successfully and can be accessed at './final_file_destination'
//
//
//			$folder = $model->getFolder(true);
//
//			$file = new File();
//			$file->name = $request->getFileName();
//			$file->parent = $folder;
//			$file->setModel($model);
//			$file->setFile($finalFile);
//
//			if (!$file->save()) {
//				throw new \Exception(var_export($file->getValidationErrors()));
//			}
//
//
//			echo $this->view->render(
//					'form', [
//				'file' => $file
//					]
//			);
//		} else {
//			// This is not a final chunk or request is invalid, continue to upload.
//			echo $this->view->render('json', array(
//				'success' => true
//			));
//		}
//	}



	/**
	 * Create a new file. Use GET to fetch the default attributes or POST to add a new file.
	 *
	 * The attributes of this file should be posted as JSON in a file object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"file":{"attributes":{"filename":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function httpPost($returnAttributes = []) {
		
		$model = $this->getModel();

		if (!$this->canUpload($model)) {
			return $this->renderError(403);
		}

		$file = new File();
		
		$folder = $model->getFolder(true);
		
		$file->parent = $folder;
		$file->setModel($model);
		
		$file->setAttributes(App::request()->payload['data']);

		$file->save();


		return $this->renderModel($file, $returnAttributes);
	}

	/**
	 * Update a file. Use GET to fetch the default attributes or POST to add a new file.
	 *
	 * The attributes of this file should be posted as JSON in a file object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"file":{"attributes":{"filename":"test",...}}}
	 * </code>
	 * 
	 * @param int $fileId The ID of the file
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function httpPut($fileId, $returnAttributes = []) {

		$file = File::findByPk($fileId);

		if (!$file) {
			return $this->renderError(404);
		}

		if (!$this->canWrite($file->getModel())) {
			return $this->renderError(403);
		}

		$file->setAttributes(App::request()->payload['data']);
		$file->save();

		return $this->renderModel($file, $returnAttributes);
	}

	/**
	 * Delete a file
	 *
	 * @param int $fileId
	 * @throws NotFound
	 */
	public function httpDelete($fileId) {
		$file = File::findByPk($fileId);

		if (!$file) {
			return $this->renderError(404);
		}

		if (!$this->canWrite($file->getModel())) {
			return $this->renderError(403);
		}

		$file->delete();

		return $this->renderModel($file);
	}

}
