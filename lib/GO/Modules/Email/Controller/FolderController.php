<?php
namespace GO\Modules\Email\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractCrudController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\Email\Model\Folder;



/**
 * The controller for folders. Admin role is required.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FolderController extends AbstractCrudController {

	/**
	 * Fetch folders
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($accountId, $orderColumn = 'sortOrder', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {

		$folders = Folder::find(Query::newInstance()
								->orderBy([$orderColumn => $orderDirection])
								->limit($limit)
								->offset($offset)
								->search($searchQuery, array('t.foldername'))
								->where(['accountId' => $accountId])
		);

		$store = new Store($folders);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * GET a list of folders or fetch a single folder
	 *
	 * 
	 * @param int $folderId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($folderId = null, $returnAttributes = []){
	
		$folder = Folder::findByPk($folderId);		

		if (!$folder) {
			throw new NotFound();			
		}

		return $this->renderModel($folder, $returnAttributes);
		
	}
	
	
	/**
	 * Get's the default data for a new folder
	 * 
	 * 
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($accountId, $returnAttributes = []){
		
		$folder = new Folder();
		$folder->accountId = $accountId;

		return $this->renderModel($folder, $returnAttributes);
	}

	
	/**
	 * Create a new field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($accountId, $returnAttributes = []) {

		$folder = new Folder();
		$folder->setAttributes(App::request()->payload['data']);
		$folder->accountId = $accountId;
		$folder->save();		

		return $this->renderModel($folder, $returnAttributes);
	}

	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $folderId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($folderId, $returnAttributes = []) {

		$folder = Folder::findByPk($folderId);		

		if (!$folder) {
			throw new NotFound();
		}

		$folder->setAttributes(App::request()->payload['data']);
		$folder->save();
		
		return $this->renderModel($folder, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $folderId
	 * @throws NotFound
	 */
	public function actionDelete($folderId) {
		$folder = Folder::findByPk($folderId);

		if (!$folder) {
			throw new NotFound();
		}

		$folder->delete();

		return $this->renderModel($folder);
	}
}