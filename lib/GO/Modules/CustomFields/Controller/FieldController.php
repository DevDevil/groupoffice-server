<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\CustomFields\Model\Field;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FieldController extends AbstractController {
	
	
	/**
	 * GET a list of fields or fetch a single field
	 *
	 * 
	 * @param int $fieldId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($fieldId = null, $returnAttributes = []) {

		$field = Field::findByPk($fieldId);

		if (!$field) {
			throw new NotFound();
		}

		return $this->renderModel($field, $returnAttributes);
	}

	/**
	 * Get's the default data for a new field
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($fieldSetId, $returnAttributes = []) {

		$field = new Field();
		$field->fieldSetId = $fieldSetId;

		return $this->renderModel($field, $returnAttributes);
	}

	/**
	 * Fetch fields
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function actionStore($fieldSetId, $orderColumn = 'sortOrder', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where=null) {


		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, ['name'])
				->where(['fieldSetId' => $fieldSetId]);
				
		if(isset($where)){
			$where = json_decode($where, true);
			$query->where($where);
		}

		$fields = Field::find($query);

		$store = new Store($fields);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}
	

	/**
	 * Create a new field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($fieldSetId, $returnAttributes = []) {

		$field = new Field();
		$field->fieldSetId = $fieldSetId;

		$field->setAttributes(App::request()->payload['data']);

		$field->save();
		

		return $this->renderModel($field, $returnAttributes);
	}

	/**
	 * Update a field. Use GET to fetch the default attributes or POST to add a new field.
	 *
	 * The attributes of this field should be posted as JSON in a field object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"field":{"attributes":{"fieldname":"test",...}}}
	 * </code>
	 * 
	 * @param int $fieldId The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($fieldId, $returnAttributes = []) {

		$field = Field::findByPk($fieldId);

		if (!$field) {
			return $this->renderError(404);
		}

		$field->setAttributes(App::request()->payload['data']);
		$field->save();
		
		return $this->renderModel($field, $returnAttributes);
	}

	/**
	 * Delete a field
	 *
	 * @param int $fieldId
	 * @throws NotFound
	 */
	public function actionDelete($fieldId) {
		$field = Field::findByPk($fieldId);

		if (!$field) {
			return $this->renderError(404);
		}

		$field->delete();

		return $this->renderModel($field);
	}
}
