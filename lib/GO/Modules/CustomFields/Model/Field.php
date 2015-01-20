<?php

namespace GO\Modules\CustomFields\Model;

use Exception;
use GO\Core\App;
use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Columns;
use GO\Core\Db\RelationFactory;

/**
 * Field model
 * 
 * Defines a custom field
 *
 * @property int $id
 * @property int $fieldSetId
 * @property string $type One of the TYPE_* constants
 * @property string $name The name presented to the user.
 * @property string $databaseName The name of the database column
 * @property mixed $defaultValue 
 * @property bool $required
 * @property string $placeholder
 * @property array $data Extra options for the custom field eg. ["maxLength": 50, "selectOptions": ['option 1', 'option 2']]
 * 
 * @property FieldSet $fieldSet
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Field extends AbstractRecord {
	
	use \GO\Core\Db\SoftDeleteTrait;

	const TYPE_TEXT = "text";
	const TYPE_TEXTAREA = "textarea";
	const TYPE_CHECKBOX = "checkbox";
	const TYPE_SELECT = "select";
	const TYPE_DATE = "date";
	const TYPE_DATETIME = "datetime";
	
	const TYPE_NUMBER = "number";
	
	
	public $resort = false;
	

	protected static function defineRelations(RelationFactory $r) {
		return [
			$r->belongsTo('fieldSet', FieldSet::className(), 'fieldSetId'),
		];
	}
	
	protected static function defineDefaultAttributes() {
		return ['type' => self::TYPE_TEXT, '_data' => '{"maxLength":50}'];
	}

	public function deletePermanently() {

		if (parent::deletePermanently()) {

			//don't be strict in upgrade process
			App::dbConnection()->getPdo()->query("SET sql_mode=''");

			$sql = "ALTER TABLE `" . $this->fieldSet->customFieldsTableName() . "` DROP `" . $this->databaseName . "`";

			try {
				App::dbConnection()->getPdo()->query($sql);
			} catch (Exception $e) {
				trigger_error("Dropping custom field column failed with error: " . $e->getMessage());
			}
			
			//for cached database columns
			Columns::clearCache($this->fieldSet->modelName);
		}
	}

//	private function _createTable() {
//		$sql = "CREATE TABLE IF NOT EXISTS `" . $this->fieldSet->customFieldsTableName() . "` (
//			`id` int(11) NOT NULL,		
//			PRIMARY KEY (`id`)
//		  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
//
//		return App::dbConnection()->getPdo()->query($sql);
//	}
	
	private function _getTypeSql(){
		switch($this->type){
			
			case self::TYPE_DATE:
				$sql = "DATE NULL";
				if(!empty($this->defaultValue)){
					$sql .= " DEFAULT ".App::dbConnection()->getPDO()->quote($this->defaultValue);
				}
				return $sql;
				
			case self::TYPE_DATETIME:
				$sql = "DATE";
				if(!empty($this->defaultValue)){
					$sql .= " DEFAULT ".App::dbConnection()->getPDO()->quote($this->defaultValue);
				}
				return $sql;
			
			case self::TYPE_CHECKBOX:			
				return "BOOLEAN NOT NULL DEFAULT '".intval($this->defaultValue)."'";
			
			case self::TYPE_TEXTAREA:
				return "TEXT NULL";
				
			case self::TYPE_TEXT:	
				
				$data = $this->data;
				if(!isset($data['maxLength'])){
					$data['maxLength'] = 50;
					$this->data = $data;
				}				
				return "VARCHAR(".$this->data['maxLength'].") NOT NULL DEFAULT ".App::dbConnection()->getPDO()->quote($this->defaultValue);
				
			case self::TYPE_SELECT:
				
				return "VARCHAR(".$this->_findLargestSelectOption().") NOT NULL DEFAULT ".App::dbConnection()->getPDO()->quote($this->defaultValue);
			case self::TYPE_NUMBER:
				
				$sql = "DOUBLE NULL";
				
				if(!empty($this->defaultValue)){
					$sql .= " DEFAULT ".App::dbConnection()->getPDO()->quote($this->defaultValue);
				}
				
				return $sql;
			
			default:
				throw new Exception("Not implemented!?");				
		}
	}
	
	private function _findLargestSelectOption(){
		$max = 0;
		foreach($this->data['options'] as $option){
			$l = strlen($option['value']);
			if($l > $max){
				$max = $l;
			}
		}
		
		return $max;
	}
	
	private function _hasOption($value){
		foreach($this->data['options'] as $option){
			if($option['value']===$value){
				return true;
			}
		}
		
		return false;
	}
	
	public function validate() {
		
		switch($this->type) {
			case self::TYPE_SELECT:
		
				if(!isset($this->data['options'])){
					$this->setValidationError('data', 'noSelectOptions');
				}
				if(!empty($this->defaultValue) && !$this->_hasOption($this->defaultValue)){
					$this->setValidationError('defaultValue', 'defaultNotASelectOption');
				}
				break;
				
			case self::TYPE_TEXTAREA:
				
				if(!empty($this->defaultValue)){
					$this->setValidationError('defaultValue', 'textCantHaveDefaultValue');
				}
				
				break;
		}
		
		return parent::validate();
	}
	
	private function _resort(){		
		
		if($this->resort) {			
			
			App::debug("Resorting field ".$this->databaseName. " order: ".$this->sortOrder);

			$fields = $this->fieldSet->fields;
			

			$sortOrder = 0;
			foreach($fields as $field){
				
				$sortOrder++;

				if($sortOrder == $this->sortOrder){
					$sortOrder++;
				}

				//skip this model
				if($field->id == $this->id){					
					continue;
				}

				$field->sortOrder = $sortOrder;				
				
				
				App::debug("Resorting field ".$field->databaseName. " order: ".$field->sortOrder);
				
				$field->save();
			}
		}		
	}
	
	public function save() {
		
		
		$startTrans = $this->isModified(['databaseName','defaultValue', '_data','type']) && !App::dbConnection()->getPDO()->inTransaction();
		
		if($startTrans){
			App::dbConnection()->getPDO()->beginTransaction();
		}
		
		try {
			$this->_alterDatabase();
			
			$this->_resort();

			$success = parent::save();
		
		} catch (Exception $ex) {
			
			if($startTrans){
				App::dbConnection()->getPDO()->rollBack();
			}
			
			$this->setValidationError('databaseName', $ex->getMessage());
			
			App::debug($ex->getMessage());
			
			return false;
		}
		
		if($startTrans){
			App::dbConnection()->getPDO()->commit();
		}
		
		
		return $success;
	}
	
	public function getData(){
		return json_decode($this->_data, true);
	}
	
	public function setData(array $data){
		$this->_data = json_encode($data);	
	}
	
	public function getAttributes(array $returnAttributes = []) {
		$attributes = parent::getAttributes($returnAttributes);
		
		if(isset($attributes['_data'])){
			unset($attributes['_data']);
			
			$attributes['data']=$this->getData();
		}
		
		return $attributes;
	}

	private function _alterDatabase() {
		
		if($this->isModified(['databaseName','defaultValue', '_data','type'])){

			$table = $this->fieldSet->customFieldsTableName();

			if ($this->isNew) {
				$sql = "ALTER TABLE `" . $table . "` ADD `" . $this->databaseName . "` " . $this->_getTypeSql() . ";";
			} else {
				$tableName = $this->getOldAttributeValue('databaseName');
				if(!$tableName){
					$tableName = $this->databaseName;
				}
				$sql = "ALTER TABLE `" . $table . "` CHANGE `" . $tableName . "` `" . $this->databaseName . "` " . $this->_getTypeSql();
			}

//			echo $sql;

			//don't be strict in upgrade process
			App::dbConnection()->getPdo()->query("SET sql_mode=''");

			if (!App::dbConnection()->getPdo()->query($sql)) {
				throw new Exception("Could not create custom field");
			}

			//for cached database columns
			Columns::clearCache($this->fieldSet->modelName);
		}
	}

}
