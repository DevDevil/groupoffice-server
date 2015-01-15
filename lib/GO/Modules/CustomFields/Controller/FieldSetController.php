<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractRESTController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\CustomFields\Model\Field;
use GO\Modules\CustomFields\Model\FieldSet;

/**
 * The controller for address books
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FieldSetController extends AbstractRESTController {
	
	
	protected function httpGet($fieldSetId = null, $returnAttributes = []){
		if(!isset($fieldSetId)){
			return $this->callMethodWithParams('store');
		}  else {
			$fieldset = FieldSet::findByPk($fieldSetId);

			if (!$fieldset) {
				return $this->renderError(404);					
			}
			
			return $this->renderModel($fieldset, $returnAttributes);
		}
	}


	/**
	 * Fetch fieldsets
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return array JSON Model data
	 */
	protected function store($modelName, $orderColumn = 'sortOrder', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {


		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset)
				->search($searchQuery, ['name'])
				->where(['modelName' => $modelName]);
				
		
		$fieldsets = FieldSet::find($query);

		$store = new Store($fieldsets);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * Create a new fieldset. Use GET to fetch the default attributes or POST to add a new fieldset.
	 *
	 * The attributes of this fieldset should be posted as JSON in a fieldset object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"fieldset":{"attributes":{"fieldsetname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function httpPost($modelName='', $returnAttributes = []) {

		$fieldset = new FieldSet();
		$fieldset->modelName = $modelName;

		$fieldset->setAttributes(App::request()->payload['data']);
		$fieldset->save();
		

		return $this->renderModel($fieldset);
	}

	/**
	 * Update a fieldset. Use GET to fetch the default attributes or POST to add a new fieldset.
	 *
	 * The attributes of this fieldset should be posted as JSON in a fieldset object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"fieldset":{"attributes":{"fieldsetname":"test",...}}}
	 * </code>
	 * 
	 * @param int $fieldSetId The ID of the fieldset
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function httpPut($fieldSetId, $returnAttributes = []) {

		$fieldset = FieldSet::findByPk($fieldSetId);

		if (!$fieldset) {
			return $this->renderError(404);
		}

		$fieldset->setAttributes(App::request()->payload['data']);
		$fieldset->save();
		
		return $this->renderModel($fieldset);
	}

	/**
	 * Delete a fieldset
	 *
	 * @param int $id
	 * @throws NotFound
	 */
	public function httpDelete($id) {
		$fieldset = FieldSet::findByPk($id);

		if (!$fieldset) {
			return $this->renderError(404);
		}

		$fieldset->delete();

		return $this->renderModel($field);
	}
	
	
	public function actionSaveSort(){		
			
		$sortOrder = App::request()->post['sortOrder'];

		$index = count($sortOrder);

		foreach (App::request()->post['sortOrder'] as $key){
			$fieldSet = FieldSet::findByPk($key);

			if($fieldSet){
				$fieldSet->sortOrder = $index;
				$fieldSet->save();
			}
			$index++;
		}

		$response = array('success'=>true);

		echo $this->view->render('json', $response);		
	}
	
	
	public function actionTest(){
		
		$fieldSet = FieldSet::find(['name' => 'Tennis'])->single();
		if($fieldSet){
			$fieldSet->delete();
		}
			$fieldSet = new FieldSet();
			$fieldSet->modelName = "\GO\Modules\Contacts\Model\ContactCustomFields";
			$fieldSet->name = "Tennis";
			$success = $fieldSet->save();

			if(!$success){
				var_dump($fieldSet->getValidationErrors());
				exit();
			}
		
		
		$field = Field::find(['databaseName' =>  "Een tekstveld"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Een tekstveld";
		$field->type = Field::TYPE_TEXT;
		$field->databaseName = "Een tekstveld";
		$field->data = ['maxLength' => 100];
		$field->placeholder = "De placeholder...";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}
		
		$field = Field::find(['databaseName' =>  "Een textarea"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Een textarea";
		$field->type = Field::TYPE_TEXTAREA;
		$field->databaseName = "Een textarea";		
		$field->placeholder = "De placeholder...";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}
		
		$field = Field::find(['databaseName' =>  "zaterdagInvaller"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Ik wil invallen op zaterdag";
		$field->type = Field::TYPE_CHECKBOX;
		$field->databaseName = "zaterdagInvaller";
		$field->placeholder = "De placeholder...";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}
		
		$field = Field::find(['databaseName' =>  "Speelsterkte enkel"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Speelsterkte enkel";
		$field->type = Field::TYPE_SELECT;
		$field->databaseName = "Speelsterkte enkel";		
		$field->placeholder = "De placeholder...";
		$field->data = ['options' => ["9","8","7","6","5","4","3","2","1"]];
		$field->defaultValue = "9";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}
		
		$field = Field::find(['databaseName' =>  "Speelsterkte dubbel"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Speelsterkte dubbel";
		$field->type = Field::TYPE_SELECT;
		$field->databaseName = "Speelsterkte dubbel";
		$field->placeholder = "De placeholder...";
		$field->data = ['options' => ["9","8","7","6","5","4","3","2","1"]];
		$field->defaultValue = "9";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}
		
		
		$field = Field::find(['databaseName' =>  "Lid sinds"])->single();	
		if(!$field){
			$field = new Field();
		}
		$field->fieldSet = $fieldSet;
		$field->name = "Lid sinds";
		$field->type = Field::TYPE_DATE;
		$field->databaseName = "Lid sinds";		
		$field->placeholder = "De placeholder...";
		if(!$field->save()){
			var_dump($field->getValidationErrors());
			exit();
		}	

	}

}
