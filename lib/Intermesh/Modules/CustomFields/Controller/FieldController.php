<?php

namespace Intermesh\Modules\CustomFields\Controller;

use Intermesh\Core\App;
use Intermesh\Core\Controller\AbstractRESTController;
use Intermesh\Core\Data\Store;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Exception\NotFound;
use Intermesh\Modules\CustomFields\Model\Field;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FieldController extends AbstractRESTController {

	protected function httpGet($fieldId = null, $returnAttributes = []){
		if(!isset($fieldId)){
			return $this->callMethodWithParams('store');
		}  else {
			
			if($fieldId == 0){
				$field = new Field();
			}else
			{
				$field = Field::findByPk($fieldId);
			}

			if (!$field) {
				return $this->renderError(404);					
			}
			
			return $this->renderModel($field, $returnAttributes);
		}
	}
	/**
	 * Fetch fields
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function store($orderColumn = 'sortOrder', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = [], $where=null) {


		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, ['name']);
				
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
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function httpPost($fieldSetId, $returnAttributes = []) {

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
	 * @param int $id The ID of the field
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see Intermesh\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function httpPut($id, $returnAttributes = []) {

		$field = Field::findByPk($id);

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
	 * @param int $id
	 * @throws NotFound
	 */
	public function httpDelete($id) {
		$field = Field::findByPk($id);

		if (!$field) {
			return $this->renderError(404);
		}

		$field->delete();

		return $this->renderModel($field);
	}
}
