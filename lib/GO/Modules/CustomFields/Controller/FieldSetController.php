<?php

namespace GO\Modules\CustomFields\Controller;

use GO\Core\App;
use GO\Core\Controller\AbstractController;
use GO\Core\Data\Store;
use GO\Core\Db\Query;
use GO\Core\Exception\NotFound;
use GO\Modules\CustomFields\Model\FieldSet;

/**
 * The controller for fieldSets. Admin role is required.
 * 
 * Uses the {@see FieldSet} model.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FieldSetController extends AbstractController {

	/**
	 * Fetch fieldSets
	 *
	 * @param string $orderColumn Order by this column
	 * @param string $orderDirection Sort in this direction 'ASC' or 'DESC'
	 * @param int $limit Limit the returned records
	 * @param int $offset Start the select on this offset
	 * @param string $searchQuery Search on this query.
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	
	 * @return array JSON Model data
	 */
	protected function actionStore($modelName, $orderColumn = 'name', $orderDirection = 'ASC', $limit = 10, $offset = 0, $searchQuery = "", $returnAttributes = []) {

		$query = Query::newInstance()
				->orderBy([$orderColumn => $orderDirection])
				->limit($limit)
				->offset($offset);

		if (!empty($searchQuery)) {
			$query->search($searchQuery, ['t.name']);
		}
		
		$query->where(['modelName' => $modelName]);

//		if (!empty($where)) {
//
//			$where = json_decode($where, true);
//
//			if (count($where)) {
//				$query
//						->groupBy(['t.id'])
//						->whereSafe($where);
//			}
//		}

		$fieldSets = FieldSet::find($query);

		$store = new Store($fieldSets);
		$store->setReturnAttributes($returnAttributes);

		return $this->renderStore($store);
	}

	/**
	 * GET a list of fieldSets or fetch a single fieldSet
	 *
	 * 
	 * @param int $fieldSetId The ID of the role
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	protected function actionRead($fieldSetId = null, $returnAttributes = []) {

		$fieldSet = FieldSet::findByPk($fieldSetId);

		if (!$fieldSet) {
			throw new NotFound();
		}

		return $this->renderModel($fieldSet, $returnAttributes);
	}

	/**
	 * Get's the default data for a new fieldSet
	 * 
	 * @param array $returnAttributes
	 * @return array
	 */
	protected function actionNew($modelName, $returnAttributes = []) {

		$fieldSet = new FieldSet();
		$fieldSet->modelName = $modelName;

		return $this->renderModel($fieldSet, $returnAttributes);
	}

	/**
	 * Create a new fieldSet. Use GET to fetch the default attributes or POST to add a new fieldSet.
	 *
	 * The attributes of this fieldSet should be posted as JSON in a fieldSet object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldSetname":"test",...}}}
	 * </code>
	 * 
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 */
	public function actionCreate($modelName, $returnAttributes = []) {

		$fieldSet = new FieldSet();
		$fieldSet->modelName = $modelName;
		$fieldSet->setAttributes(App::request()->payload['data']);
		$fieldSet->save();

		return $this->renderModel($fieldSet, $returnAttributes);
	}

	/**
	 * Update a fieldSet. Use GET to fetch the default attributes or POST to add a new fieldSet.
	 *
	 * The attributes of this fieldSet should be posted as JSON in a fieldSet object
	 *
	 * <p>Example for POST and return data:</p>
	 * <code>
	 * {"data":{"attributes":{"fieldSetname":"test",...}}}
	 * </code>
	 * 
	 * @param int $fieldSetId The ID of the fieldSet
	 * @param array|JSON $returnAttributes The attributes to return to the client. eg. ['\*','emailAddresses.\*']. See {@see GO\Core\Db\ActiveRecord::getAttributes()} for more information.
	 * @return JSON Model data
	 * @throws NotFound
	 */
	public function actionUpdate($fieldSetId, $returnAttributes = []) {

		$fieldSet = FieldSet::findByPk($fieldSetId);

		if (!$fieldSet) {
			throw new NotFound();
		}

		$fieldSet->setAttributes(App::request()->payload['data']);

		$fieldSet->save();


		return $this->renderModel($fieldSet, $returnAttributes);
	}

	/**
	 * Delete a fieldSet
	 *
	 * @param int $fieldSetId
	 * @throws NotFound
	 */
	public function actionDelete($fieldSetId) {
		$fieldSet = FieldSet::findByPk($fieldSetId);

		if (!$fieldSet) {
			throw new NotFound();
		}

		$fieldSet->delete();

		return $this->renderModel($fieldSet);
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
