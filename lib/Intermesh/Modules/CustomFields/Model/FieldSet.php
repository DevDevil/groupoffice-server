<?php
namespace Intermesh\Modules\CustomFields\Model;

use Intermesh\Core\Db\AbstractRecord;
use Intermesh\Core\Db\Query;
use Intermesh\Core\Db\Relation;
use Intermesh\Core\Db\RelationFactory;

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
	
	use \Intermesh\Core\Db\SoftDeleteTrait;
	
	protected static function defineRelations(RelationFactory $r) {
		return [			
			$r->hasMany('fields', Field::className(), 'fieldSetId')
				->setDeleteAction(Relation::DELETE_CASCADE)
				->setQuery(Query::newInstance()->orderBy(['sortOrder' => 'ASC']))
			];
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
		
		if(!call_user_func([$this->modelName, 'isCustomFieldsRecord'])){
			$this->setValidationError('model', 'noCustomFieldsRecord');
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