<?php
namespace GO\Modules\CustomFields\Model;

use GO\Core\Db\AbstractRecord;
use GO\Core\Db\Query;
use GO\Core\Db\Relation;


/**
 * FieldSet model
 * 
 *
 * @property int $id
 * @property int $sortOrder
 * @property string $modelName
 * @property string $name
 *
 * 
 * @property Field[] $fields
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class FieldSet extends AbstractRecord{
	
	use \GO\Core\Db\SoftDeleteTrait;
	
	protected static function defineRelations() {		
			self::hasMany('fields', Field::className(), 'fieldSetId')
				->setDeleteAction(Relation::DELETE_CASCADE)
				->setQuery(Query::newInstance()->orderBy(['sortOrder' => 'ASC']));		
	}
	
	/**
	 * Get's the table name to store custom fields in.
	 * 
	 * @return string
	 */
	public function customFieldsTableName(){		
		return call_user_func([$this->modelName, 'tableName']);		
	}
	
	public function validate() {
		
		if(!class_exists($this->modelName) || !is_subclass_of($this->modelName, AbstractCustomfieldsRecord::className())){
			$this->setValidationError('model', 'noCustomFieldsRecord', ['modelName' => $this->modelName]);
		}
		
		return parent::validate();
	}
	
	
	public $resort = false;
	
	private function _resort(){		
		
		if($this->resort) {			

			$fieldSets = FieldSet::find();
			

			$sortOrder = 0;
			foreach($fieldSets as $fieldSet){
				
				$sortOrder++;

				if($sortOrder == $this->sortOrder){
					$sortOrder++;
				}

				//skip this model
				if($fieldSet->id == $this->id){					
					continue;
				}

				$fieldSet->sortOrder = $sortOrder;				
				$fieldSet->save();
			}
		}		
	}
	
	public function save() {
		
		$this->_resort();
		
		return parent::save();
	}
}